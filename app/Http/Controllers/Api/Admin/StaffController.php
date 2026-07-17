<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateEmploymentStatusRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Http\Resources\StaffSearchResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display a listing of staff (with profile, designation, services eager-loaded).
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $staff = User::query()
            ->where('role', UserRole::STAFF)
            ->with(['staffProfile.designation', 'services'])

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->search;

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%");
                    });
                }
            )

            ->orderBy(
                $request->input('sort_by', 'name'),
                $request->input('sort_dir', 'asc')
            )

            ->paginate(
                $request->input('per_page', 15)
            );

        return StaffResource::collection($staff);
    }

    /**
     * Store a newly created staff member (user + profile + services).
     */
    public function store(StoreStaffRequest $request)
    {
        Gate::authorize('create', User::class);

        $data = $request->validated();

        // 1. Create the user account
        $staff = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::STAFF,
            'image' => $data['image'] ?? null,
        ]);

        // 2. Create the staff profile
        $staff->staffProfile()->create([
            'phone' => $data['phone'] ?? null,
            'designation_id' => $data['designation_id'] ?? null,
            'experience_years' => $data['experience_years'] ?? null,
            'employment_status' => $data['employment_status'] ?? 'active',
        ]);

        // 3. Sync services
        if (! empty($data['service_ids'])) {
            $staff->services()->sync($data['service_ids']);
        }

        return new StaffResource($staff->load(['staffProfile.designation', 'services']));
    }

    /**
     * Display the specified staff member.
     */
    public function show(User $staff)
    {
        Gate::authorize('view', $staff);

        abort_if($staff->role !== UserRole::STAFF, 404);

        return new StaffResource($staff->load(['staffProfile.designation', 'services']));
    }

    /**
     * Update the specified staff member (user + profile fields).
     */
    public function update(UpdateStaffRequest $request, User $staff)
    {
        Gate::authorize('update', $staff);

        abort_if($staff->role !== UserRole::STAFF, 404);

        $data = $request->validated();

        // Update user fields
        $userFields = array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'image' => $data['image'] ?? null,
        ], fn ($v) => $v !== null);

        if (! empty($data['password'])) {
            $userFields['password'] = Hash::make($data['password']);
        }

        if (! empty($userFields)) {
            $staff->update($userFields);
        }

        // Update (or create) the staff profile
        $profileFields = array_filter([
            'phone' => $data['phone'] ?? null,
            'designation_id' => $data['designation_id'] ?? null,
            'experience_years' => $data['experience_years'] ?? null,
            'employment_status' => $data['employment_status'] ?? null,
        ], fn ($v) => $v !== null);

        if (! empty($profileFields)) {
            $staff->staffProfile()->updateOrCreate(
                ['user_id' => $staff->id],
                $profileFields
            );
        }

        // Sync services
        if (isset($data['service_ids'])) {
            $staff->services()->sync($data['service_ids']);
        }

        return new StaffResource($staff->fresh()->load(['staffProfile.designation', 'services']));
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(User $staff)
    {
        Gate::authorize('delete', $staff);

        abort_if($staff->role !== UserRole::STAFF, 404);

        $staff->delete();

        return response()->json([
            'message' => 'Staff deleted successfully.',
        ]);
    }

    public function search(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $staff = User::query()
            ->where('role', UserRole::STAFF)
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim($request->search);

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%");
                    });
                }
            )
            ->orderBy('name')
            ->limit(5)
            ->get([
                'id',
                'name',
            ]);

        return StaffSearchResource::collection($staff);
    }

    public function updateEmploymentStatus(
        UpdateEmploymentStatusRequest $request,
        User $staff
    ) {
        Gate::authorize('update', $staff);

        abort_if($staff->role !== UserRole::STAFF, 404);

        $staffProfile = $staff->staffProfile;

        if (! $staffProfile) {
            // Auto-create a profile if missing
            $staffProfile = $staff->staffProfile()->create([
                'employment_status' => $request->employment_status,
            ]);
        } else {
            $staffProfile->update([
                'employment_status' => $request->employment_status,
            ]);
        }

        return response()->json([
            'message' => 'Employment status updated successfully.',
            'data' => new StaffResource($staff->fresh()->load(['staffProfile.designation', 'services'])),
        ]);
    }

    /**
     * Display the specified staff schedule.
     */
    public function schedule(User $staff)
    {
        Gate::authorize('view', $staff);

        abort_if($staff->role !== UserRole::STAFF, 404);

        $hours = $staff->workingHours()->orderBy('day_of_week')->get();

        return response()->json([
            'data' => $hours,
        ]);
    }
}
