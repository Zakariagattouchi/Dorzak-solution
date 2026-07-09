<?php

namespace App\Http\Controllers\Api\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /platform/audit-logs — the accountability trail: who did what, to whom,
 * when. Read-only; the log itself is append-only.
 */
class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = PlatformAuditLog::query()
            ->with('admin:id,name,email')
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')))
            ->latest('id')
            ->paginate(50);

        return response()->json([
            'data' => $logs->getCollection()->map(fn (PlatformAuditLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'admin' => $log->admin ? ['id' => $log->admin->id, 'name' => $log->admin->name, 'email' => $log->admin->email] : null,
                'target_type' => $log->target_type,
                'target_id' => $log->target_id,
                'target_label' => $log->target_label,
                'meta' => $log->meta,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toISOString(),
            ]),
            'meta' => ['total' => $logs->total(), 'per_page' => $logs->perPage(), 'current_page' => $logs->currentPage()],
        ]);
    }
}
