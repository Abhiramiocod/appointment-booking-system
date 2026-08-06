<?php

namespace App\Http\Controllers\Api\Customer;

use App\Actions\Customer\Dashboard\GetDashboardStatsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, GetDashboardStatsAction $action)
    {

        return response()->json($action->execute($request->user()));
    }
}
