<?php

namespace App\Http\Controllers\Tenant;

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        $teamId = app(PermissionRegistrar::class)->getPermissionsTeamId();

        return response()->json(
            Role::where('team_id', $teamId)->get(['id', 'name'])
        );
    }
}
