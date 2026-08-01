<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateTenantConfigRequest;

class ConfigController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user()->tenant);
    }

    public function update(UpdateTenantConfigRequest $request)
    {
        $tenant = $request->user()->tenant;
        $tenant->update($request->validated());

        return response()->json($tenant->fresh());
    }
}
