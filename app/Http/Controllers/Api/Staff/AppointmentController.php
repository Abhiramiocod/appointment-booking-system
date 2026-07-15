<?php

namespace App\Http\Controllers\Api\Staff;

use App\Actions\Staff\Appointment\CancelAppointmentAction;
use App\Actions\Staff\Appointment\CompleteAppointmentAction;
use App\Actions\Staff\Appointment\ConfirmAppointmentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAnyStaff', Appointment::class);

        $appointments = Appointment::query()
            ->where('staff_id', $request->user()->id)
            ->with(['customer', 'service'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate();

        return AppointmentResource::collection($appointments);
    }

    public function confirm(
        Appointment $appointment,
        ConfirmAppointmentAction $action
    ): AppointmentResource|JsonResponse {
        Gate::authorize('confirmStaff', $appointment);

        try {
            $appointment = $action->execute($appointment);

            return new AppointmentResource(
                $appointment->load(['customer', 'staff', 'service'])
            );
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function complete(
        Appointment $appointment,
        CompleteAppointmentAction $action
    ): AppointmentResource|JsonResponse {
        Gate::authorize('completeStaff', $appointment);

        try {
            $appointment = $action->execute($appointment);

            return new AppointmentResource(
                $appointment->load(['customer', 'staff', 'service'])
            );
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancel(
        Appointment $appointment,
        CancelAppointmentAction $action
    ): AppointmentResource|JsonResponse {
        Gate::authorize('cancelStaff', $appointment);

        $appointment = $action->execute($appointment);

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service'])
        );
    }

    public function reject(Request $request, Appointment $appointment): AppointmentResource|JsonResponse
    {
        abort_if($appointment->staff_id !== $request->user()->id, 403, 'Unauthorized.');

        $appointment->update([
            'status' => \App\Enums\AppointmentStatus::REJECTED,
            'rejection_reason' => $request->rejection_reason,
        ]);

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service'])
        );
    }

    public function proposeTime(Request $request, Appointment $appointment): AppointmentResource|JsonResponse
    {
        abort_if($appointment->staff_id !== $request->user()->id, 403, 'Unauthorized.');

        $request->validate([
            'proposed_date' => 'required|date|after_or_equal:today',
            'proposed_time' => 'required',
        ]);

        $appointment->update([
            'status' => \App\Enums\AppointmentStatus::RESCHEDULE_REQUESTED,
            'proposed_date' => $request->proposed_date,
            'proposed_time' => $request->proposed_time,
            'proposed_note' => $request->proposed_note,
        ]);

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service'])
        );
    }
}
