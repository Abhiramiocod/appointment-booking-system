<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\StoreServiceRequest;
use App\Http\Requests\Services\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $validated = $request->validated();
        $validated['slug'] = Service::generateSlug($validated['name']);

        $service = Service::create($validated);

        return new JsonResponse(new ServiceResource($service), 201);
    }

    public function show(Service $service)
    {
        return new ServiceResource($service);
    }

    public function update(UpdateServiceRequest $request,Service $service): JsonResponse {
        $validated = $request->validated();

        if (isset($validated['name'])) {
            $validated['slug'] = Service::generateSlug(
                $validated['name']
            );
        }

        $service->update($validated);

        return response()->json([
            'message' => 'Service updated successfully',
            'data' => new ServiceResource($service->fresh()),
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
