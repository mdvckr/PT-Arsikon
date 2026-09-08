<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view audit logs');

        $query = AuditLog::with('user')->latest();

        if ($request->action) {
            $query->where('event', $request->action);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('auditable_type', 'like', "%{$request->search}%")
                  ->orWhere('ip_address', 'like', "%{$request->search}%");
            });
        }

        $logs    = $query->paginate(30)->withQueryString();
        $actions = AuditLog::distinct()->pluck('event')->sort()->values();

        return view('audit-logs.index', compact('logs', 'actions'));
    }
}
