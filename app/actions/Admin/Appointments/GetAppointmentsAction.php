<?php

namespace App\Actions\Admin\Appointments;

use App\Http\Requests\Admin\IndexAppointmentRequest;
use App\Models\Appointment;

class GetAppointmentsAction
{
    public function execute(IndexAppointmentRequest $request)
    {
        return Appointment::query()
            ->with(['customer', 'staff', 'service'])

            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )

            ->when(
                $request->filled('customer_id'),
                fn ($query) => $query->where('customer_id', $request->customer_id)
            )

            ->when(
                $request->filled('staff_id'),
                fn ($query) => $query->where('staff_id', $request->staff_id)
            )

            ->when(
                $request->filled('service_id'),
                fn ($query) => $query->where('service_id', $request->service_id)
            )

            ->when(
                $request->filled('appointment_date'),
                fn ($query) => $query->whereDate('appointment_date', $request->appointment_date)
            )

            ->when(
                $request->filled('date_from'),
                fn ($query) => $query->whereDate('appointment_date', '>=', $request->date_from)
            )

            ->when(
                $request->filled('date_to'),
                fn ($query) => $query->whereDate('appointment_date', '<=', $request->date_to)
            )

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->search;

                    $query->where(function ($q) use ($search) {
                        $q->whereHas('customer', function ($customer) use ($search) {
                            $customer->where('name', 'ILIKE', "%{$search}%");
                        })
                            ->orWhereHas('staff', function ($staff) use ($search) {
                                $staff->where('name', 'ILIKE', "%{$search}%");
                            })
                            ->orWhereHas('service', function ($service) use ($search) {
                                $service->where('name', 'ILIKE', "%{$search}%");
                            });
                    });
                }
            )

            ->orderBy(
                $request->input('sort_by', 'created_at'),
                $request->input('sort_dir', 'desc')
            )

            ->paginate(
                $request->input('per_page', 15)
            );
    }
}
