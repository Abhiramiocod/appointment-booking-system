<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Admin\Analytics\GetAnalyticsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function index(GetAnalyticsAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }
}
