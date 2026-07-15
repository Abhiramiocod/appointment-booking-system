<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaffResource;
use App\Http\Resources\ServiceResource;
use App\Models\Service;

class CustomerServiceController extends Controller
{
    public function index()
    {
        return ServiceResource::collection(
            Service::latest('created_at')->get()
        );
    }

    public function staff(Service $service)
    {
        return StaffResource::collection(
            $service->staff()->with('staffProfile')->get()
        );
    }
}
