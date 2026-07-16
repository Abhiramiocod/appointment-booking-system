<?php

namespace App\Http\Controllers\Api\Customer;

use App\Actions\Customer\Appointment\CancelAppointmentAction;
use App\Actions\Customer\Appointment\StoreAppointmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Http\Requests\Customer\Reviews\StoreStaffReviewRequest;

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
                $appointment->load([
                    'customer',
                    'staff',
                    'service',
                ])
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

    public function acceptReschedule(Request $request, Appointment $appointment): AppointmentResource|JsonResponse
    {
        abort_if($appointment->customer_id !== $request->user()->id, 403, 'Unauthorized.');
        abort_if($appointment->status !== \App\Enums\AppointmentStatus::RESCHEDULE_REQUESTED, 400, 'No reschedule request found.');

        $appointment->update([
            'appointment_date' => $appointment->proposed_date,
            'start_time' => $appointment->proposed_time,
            'status' => \App\Enums\AppointmentStatus::CONFIRMED,
            'proposed_date' => null,
            'proposed_time' => null,
            'proposed_note' => null,
        ]);

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service'])
        );
    }

    public function declineReschedule(Request $request, Appointment $appointment): AppointmentResource|JsonResponse
    {
        abort_if($appointment->customer_id !== $request->user()->id, 403, 'Unauthorized.');
        abort_if($appointment->status !== \App\Enums\AppointmentStatus::RESCHEDULE_REQUESTED, 400, 'No reschedule request found.');

        $appointment->update([
            'status' => \App\Enums\AppointmentStatus::REJECTED,
            'proposed_date' => null,
            'proposed_time' => null,
            'proposed_note' => null,
        ]);

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service'])
        );
    }

    public function review(
        StoreStaffReviewRequest $request,
        Appointment $appointment
    ): JsonResponse|AppointmentResource {
        Gate::authorize('reviewCustomer', $appointment);

        $appointment->review()->create([
            'staff_id' => $appointment->staff_id,
            'customer_id' => $request->user()->id,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return new AppointmentResource(
            $appointment->load(['customer', 'staff', 'service', 'review'])
        );
    }
}
