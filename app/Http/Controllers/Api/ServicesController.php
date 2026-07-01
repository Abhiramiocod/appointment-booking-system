<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\StoreServiceRequest;
use App\Http\Requests\Services\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServicesController extends Controller
{
    public function index()
    {
        return ServiceResource::collection(
            Service::latest('created_at')->paginate(15)
        );
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = Service::create($request->validated());

        return response()->json([
            'message' => 'Service created successfully',
            'data' => new ServiceResource($service),
        ], 201);
    }

    public function show(Service $service): ServiceResource
    {
        return new ServiceResource($service);
    }

    public function update(UpdateServiceRequest $request,Service $service): JsonResponse {
        $service->update($request->validated());

        return response()->json([
            'message' => 'Service updated successfully',
            'data' => new ServiceResource($service),
        ]);
    }

   public function destroy(Service $service): JsonResponse
    {
        
        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully',
        ]);
    }
}
