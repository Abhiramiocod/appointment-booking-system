<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\EmploymentStatus;
use App\Enums\StaffApplicationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\StaffApplicationResource;
use App\Models\StaffApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffApplicationController extends Controller
{
    /**
     * Display all staff applications.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', StaffApplication::class);

        $applications = StaffApplication::query()
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim($request->search);

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%")
                            ->orWhere('phone', 'ILIKE', "%{$search}%");
                    });
                }
            )
            ->latest()
            ->paginate($request->input('per_page', 15));

        return StaffApplicationResource::collection($applications);
    }

    /**
     * Display a single application.
     */
    public function show(StaffApplication $staffApplication)
    {
        Gate::authorize('view', $staffApplication);

        return new StaffApplicationResource($staffApplication);
    }

    /**
     * Approve application.
     */
    public function approve(StaffApplication $staffApplication)
    {
        Gate::authorize('update', $staffApplication);

        if ($staffApplication->status !== StaffApplicationStatus::PENDING) {
            return response()->json([
                'message' => 'This application has already been processed.',
            ], 422);
        }

        DB::beginTransaction();

        try {

            $temporaryPassword = Str::password(10);

            $staff = User::create([
                'name' => $staffApplication->name,
                'email' => $staffApplication->email,
                'password' => Hash::make($temporaryPassword),
                'role' => UserRole::STAFF,
            ]);

            $staff->staffProfile()->create([
                'phone' => $staffApplication->phone,
                'employment_status' => EmploymentStatus::ACTIVE,
            ]);

            $staffApplication->update([
                'status' => StaffApplicationStatus::APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Application approved successfully.',
                'temporary_password' => $temporaryPassword,
                'staff' => $staff,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Reject application.
     */
    public function reject(Request $request, StaffApplication $staffApplication)
    {
        Gate::authorize('update', $staffApplication);

        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($staffApplication->status !== StaffApplicationStatus::PENDING) {
            return response()->json([
                'message' => 'This application has already been processed.',
            ], 422);
        }

        $staffApplication->update([
            'status' => StaffApplicationStatus::REJECTED,
            'admin_notes' => $request->admin_notes,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
        ]);

        return response()->json([
            'message' => 'Application rejected successfully.',
        ]);
    }

    /**
     * Delete an application.
     */
    public function destroy(StaffApplication $staffApplication)
    {
        Gate::authorize('delete', $staffApplication);

        $staffApplication->delete();

        return response()->json([
            'message' => 'Application deleted successfully.',
        ]);
    }
}
