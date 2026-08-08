<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDocument;
use App\Services\TenantActivationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class TenantApprovalController extends Controller
{
    public function __construct( private readonly TenantActivationService $activationService ) 
    {

    }

    public function index(Request $request)
    {
        $query = Tenant::query();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('nit', 'like', "%{$search}%");
            });
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function pending()
    {
        $tenants = Tenant::where('status', 'pendiente')
            ->with(['users', 'documents'])
            ->get();

        return response()->json($tenants);
    }

    public function showDocument(Tenant $tenant, TenantDocument $document)
    {
        abort_if($document->tenant_id !== $tenant->id, 404);

        return Storage::disk('tenant_documents')->response($document->file_path);
    }

    public function approve(Tenant $tenant)
    {
        $this->activationService->activate($tenant);

        return response()->json([
            'message' => 'Tenant aprobado.',
            'tenant' => $tenant->fresh('branches'),
        ]);
    }

    public function reject(Tenant $tenant)
    {
        $tenant->update(['status' => 'rechazado']);

        return response()->json([
            'message' => 'Tenant rechazado.',
            'tenant' => $tenant,
        ]);
    }  

    public function show(Tenant $tenant)
    {
        return response()->json($tenant->load(['users', 'documents', 'branches']));
    }

    public function suspend(Tenant $tenant)
    {
        abort_if($tenant->status !== 'activo', 422, 'Solo se pueden suspender tenants activos.');

        $tenant->update(['status' => 'suspendido']);

        return response()->json(['message' => 'Tenant suspendido.', 'tenant' => $tenant]);
    }

    public function reactivate(Tenant $tenant)
    {
        abort_if($tenant->status !== 'suspendido', 422, 'Solo se pueden reactivar tenants suspendidos.');

        $tenant->update(['status' => 'activo']);

        return response()->json(['message' => 'Tenant reactivado.', 'tenant' => $tenant]);
    }
}
