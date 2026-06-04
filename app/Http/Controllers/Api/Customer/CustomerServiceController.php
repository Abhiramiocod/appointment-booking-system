<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaffResource;
use App\Models\Service;

class CustomerServiceController extends Controller
{
    public function staff(Service $service)
    {
        return StaffResource::collection(
            $service->staff()->with('staffProfile')->get()
        );
    }
}
