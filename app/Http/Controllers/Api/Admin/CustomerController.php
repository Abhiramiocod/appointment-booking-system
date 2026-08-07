<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Admin\Customers\GetCustomersAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexCustomerRequest;

class CustomerController extends Controller
{
    public function index(IndexCustomerRequest $request, GetCustomersAction $action)
    {
        return $action->execute($request->validated());
    }
}
