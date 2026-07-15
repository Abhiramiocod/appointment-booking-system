<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\DesignationResource;
use App\Models\Designation;

class DesignationController extends Controller
{
    /**
     * Display a listing of active designations.
     */
    public function index()
    {
        $designations = Designation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return DesignationResource::collection($designations);
    }
}
