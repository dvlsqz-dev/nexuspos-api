<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTenantUserRequest;
use App\Http\Requests\UpdateTenantUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->tenant->users);
    }

    public function store(CreateTenantUserRequest $request)
    {
        $validated = $request->validated();
        $tenant = $request->user()->tenant;

        $user = $tenant->users()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
        ]);

        $user->assignRole($validated['role']);

        return response()->json($user->load('roles'), 201);
    }

    public function update(UpdateTenantUserRequest $request, User $user)
    {
        $this->ensureSameTenant($request, $user);

        $validated = $request->validated();

        if(isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
            unset($validated['role']);
        }

        $user->update($validated);

        return response()->json($user->fresh()->load('roles'));
    }

    public function destroy(Request $request, User $user)
    {
        $this->ensureSameTenant($request, $user);

        abort_if($user->id === $request->user()->id, 422, 'No puedes eliminar tu propio usuario.');

        $user->delete();

        return response()->json([
            'message' => 'Usuario desactivado correctamente.',
        ]);
    }

    private function ensureSameTenant(Request $request, User $user): void
    {
        abort_if($user->tenant_id !== $request->user()->tenant_id, 404);
    }
}