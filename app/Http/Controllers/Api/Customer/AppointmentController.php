<?php

namespace App\Http\Controllers\Api\Customer;

use App\Actions\Customer\Appointment\AcceptRescheduleAction;
use App\Actions\Customer\Appointment\CancelAppointmentAction;
use App\Actions\Customer\Appointment\DeclineRescheduleAction;
use App\Actions\Customer\Appointment\StoreAppointmentAction;
use App\Actions\Customer\Appointment\StoreAppointmentReviewAction;
use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Reviews\StoreStaffReviewRequest;
use App\Http\Requests\Customer\StoreAppointmentRequest;
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
        Gate::authorize('viewAnyCustomer', Appointment::class);

        $appointments = Appointment::query()
            ->where('customer_id', $request->user()->id)
            ->with(['service', 'staff', 'review'])
            ->latest()
            ->paginate();

        return AppointmentResource::collection($appointments);
    }

    public function show(Appointment $appointment)
    {
        Gate::authorize('viewCustomer', $appointment);

        return new AppointmentResource(
            $appointment->load(['service', 'staff', 'review'])
        );
    }

    public function store(
        StoreAppointmentRequest $request,
        StoreAppointmentAction $action
    ): AppointmentResource|JsonResponse {
        try {
            $appointment = $action->execute(
                customer: $request->user(),
                serviceId: $request->service_id,
                staffId: $request->staff_id,
                appointmentDate: $request->appointment_date,
                startTime: $request->start_time,
                notes: $request->notes,
            );

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
        Request $request,
        Appointment $appointment,
        CancelAppointmentAction $action
    ): AppointmentResource|JsonResponse {
        Gate::authorize('cancelCustomer', $appointment);

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

    public function acceptReschedule(
        Request $request,
        Appointment $appointment,
        AcceptRescheduleAction $action
    ): AppointmentResource|JsonResponse {
        abort_if($appointment->customer_id !== $request->user()->id, 403, 'Unauthorized.');
        abort_if($appointment->status !== AppointmentStatus::RESCHEDULE_REQUESTED, 400, 'No reschedule request found.');

        $appointment = $action->execute($appointment);

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service'])
        );
    }

    public function declineReschedule(
        Request $request,
        Appointment $appointment,
        DeclineRescheduleAction $action
    ): AppointmentResource|JsonResponse {
        abort_if($appointment->customer_id !== $request->user()->id, 403, 'Unauthorized.');
        abort_if($appointment->status !== AppointmentStatus::RESCHEDULE_REQUESTED, 400, 'No reschedule request found.');

        $appointment = $action->execute($appointment);

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service'])
        );
    }

    public function review(
        StoreStaffReviewRequest $request,
        Appointment $appointment,
        StoreAppointmentReviewAction $action
    ): JsonResponse|AppointmentResource {
        Gate::authorize('reviewCustomer', $appointment);

        $appointment = $action->execute(
            $appointment,
            $request->user(),
            $request->validated()
        );

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service', 'review'])
        );
    }
}
