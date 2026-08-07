<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Admin\StaffApplications\ApproveStaffApplicationAction;
use App\Actions\Admin\StaffApplications\GetStaffApplicationsAction;
use App\Actions\Admin\StaffApplications\RejectStaffApplicationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexStaffApplicationRequest;
use App\Http\Requests\Admin\RejectStaffApplicationRequest;
use App\Http\Resources\StaffApplicationResource;
use App\Models\StaffApplication;
use Illuminate\Support\Facades\Gate;

class StaffApplicationController extends Controller
{
    /**
     * Display all staff applications.
     */
    public function index(IndexStaffApplicationRequest $request, GetStaffApplicationsAction $action)
    {
        Gate::authorize('viewAny', StaffApplication::class);

        $applications = $action->execute($request->validated());

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
    public function approve(StaffApplication $staffApplication, ApproveStaffApplicationAction $action)
    {
        Gate::authorize('update', $staffApplication);

        $result = $action->execute($staffApplication);

        return response()->json($result);
    }

    /**
     * Reject application.
     */
    public function reject(RejectStaffApplicationRequest $request, StaffApplication $staffApplication, RejectStaffApplicationAction $action)
    {

        Gate::authorize('update', $staffApplication);

        $result = $action->execute($staffApplication, $request->validated());

        return response()->json($result);
    }
}
