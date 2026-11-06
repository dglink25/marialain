<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Filtres
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('model')) {
            $query->where('model', 'like', '%' . $request->model . '%');
        }
        if ($request->filled('device')) {
            $query->where('device', $request->device);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%$search%")
                  ->orWhere('user_name', 'like', "%$search%")
                  ->orWhere('ip_address', 'like', "%$search%")
                  ->orWhere('url', 'like', "%$search%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        // Pour les filtres de la vue
        $users   = User::orderBy('name')->get(['id', 'name']);
        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();

        // Stats rapides
        $stats = [
            'total'    => ActivityLog::count(),
            'today'    => ActivityLog::whereDate('created_at', today())->count(),
            'deletes'  => ActivityLog::where('action', 'delete')->count(),
            'blocked'  => ActivityLog::where('description', 'like', '%BLOQUÉE%')->count(),
        ];

        return view('admin.activity_logs.index', compact('logs', 'users', 'actions', 'stats'));
    }

    public function show(ActivityLog $activityLog)    {
        return view('admin.activity_logs.show', compact('activityLog'));
    }
}
