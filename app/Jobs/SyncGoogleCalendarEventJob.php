<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\GoogleCalendar\CreateCalendarEventAction;
use App\Actions\GoogleCalendar\DeleteCalendarEventAction;
use App\Actions\GoogleCalendar\UpdateCalendarEventAction;
use App\Models\Appointment;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGoogleCalendarEventJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  string  $action  'create', 'update', or 'delete'
     */
    public function __construct(
        public readonly int $appointmentId,
        public readonly string $action = 'create'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        CreateCalendarEventAction $createAction,
        UpdateCalendarEventAction $updateAction,
        DeleteCalendarEventAction $deleteAction
    ): void {
        try {
            $appointment = Appointment::with(['customer.googleCalendarAccount', 'staff.googleCalendarAccount', 'service'])->find($this->appointmentId);

            if (! $appointment) {
                return;
            }

            // Sync calendar for customer if connected
            $customerAccount = $appointment->customer?->googleCalendarAccount;
            if ($customerAccount) {
                $this->syncForAccount($customerAccount, $appointment, $createAction, $updateAction, $deleteAction);
            }

            // Sync calendar for staff if connected
            $staffAccount = $appointment->staff?->googleCalendarAccount;
            if ($staffAccount && $staffAccount->id !== $customerAccount?->id) {
                $this->syncForAccount($staffAccount, $appointment, $createAction, $updateAction, $deleteAction);
            }
        } catch (Exception $e) {
            Log::error("SyncGoogleCalendarEventJob exception for appointment {$this->appointmentId}: {$e->getMessage()}", [
                'appointment_id' => $this->appointmentId,
                'action' => $this->action,
                'exception' => $e,
            ]);
        }
    }

    private function syncForAccount(
        $account,
        Appointment $appointment,
        CreateCalendarEventAction $createAction,
        UpdateCalendarEventAction $updateAction,
        DeleteCalendarEventAction $deleteAction
    ): void {
        match ($this->action) {
            'create' => $createAction->execute($account, $appointment),
            'update' => $appointment->google_calendar_event_id
                ? $updateAction->execute($account, $appointment)
                : $createAction->execute($account, $appointment),
            'delete' => $deleteAction->execute($account, $appointment),
            default => null,
        };
    }
}
