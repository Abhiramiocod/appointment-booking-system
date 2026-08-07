<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Admin\Staff\CreateStaffAction;
use App\Actions\Admin\Staff\GetStaffAction;
use App\Actions\Admin\Staff\SearchStaffAction;
use App\Actions\Admin\Staff\UpdateEmploymentStatusAction;
use App\Actions\Admin\Staff\UpdateStaffAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexStaffRequest;
use App\Http\Requests\Admin\SearchStaffRequest;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateEmploymentStatusRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Http\Resources\StaffSearchResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class StaffController extends Controller
{
    /**
     * Display a listing of staff (with profile, designation, services eager-loaded).
     */
    public function index(IndexStaffRequest $request, GetStaffAction $action)
    {

        Gate::authorize('viewAny', User::class);

        $staff = $action->execute(
            $request->validated()
        );

        return StaffResource::collection($staff);
    }

    /**
     * Store a newly created staff member (user + profile + services).
     */
    public function store(StoreStaffRequest $request, CreateStaffAction $action)
    {

        Gate::authorize('create', User::class);

        $staff = $action->execute($request->validated());

        return new StaffResource(
            $staff->load([
                'staffProfile.designation',
                'services',
            ])
        );
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
    public function update(UpdateStaffRequest $request, User $staff, UpdateStaffAction $action)
    {

        Gate::authorize('update', $staff);

        abort_if($staff->role !== UserRole::STAFF, 404);

        $staff = $action->execute(
            $staff,
            $request->validated()
        );

        return new StaffResource(
            $staff->load([
                'staffProfile.designation',
                'services',
            ])
        );
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

    public function search(SearchStaffRequest $request, SearchStaffAction $action)
    {

        Gate::authorize('viewAny', User::class);

        $staff = $action->execute($request->validated());

        return StaffSearchResource::collection($staff);
    }

    public function updateEmploymentStatus(UpdateEmploymentStatusRequest $request, User $staff, UpdateEmploymentStatusAction $action)
    {
        Gate::authorize('update', $staff);

        abort_if($staff->role !== UserRole::STAFF, 404);

        $staff = $action->execute($staff, $request->validated());

        return response()->json([
            'message' => 'Employment status updated successfully.',
            'data' => new StaffResource(
                $staff->load(['staffProfile.designation', 'services'])
            ),
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
