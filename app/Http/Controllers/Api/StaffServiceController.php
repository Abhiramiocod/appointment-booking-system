<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffServices\UpdateStaffServicesRequest;
use App\Http\Resources\ServiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class StaffServiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return ServiceResource::collection($request->user()->services()->get());
    }

    public function update(UpdateStaffServicesRequest $request): JsonResponse {
        $request->user()->services()->sync($request->validated('service_ids'));

        return response()->json([
            'message' => 'Services updated successfully',
        ]);
    }
}
