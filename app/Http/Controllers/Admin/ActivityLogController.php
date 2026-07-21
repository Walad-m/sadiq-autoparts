<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class ActivityLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage-users'),
        ];
    }

    public function index(Request $request)
    {
        $query = Activity::with('causer', 'subject')
            ->latest();

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->input('user_id'))
                ->where('causer_type', \App\Models\User::class);
        }

        if ($request->filled('event')) {
            $query->where('description', $request->input('event'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs = $query->paginate(30)
            ->withQueryString()
            ->through(fn (Activity $log) => [
                'id' => $log->id,
                'description' => $log->description,
                'causer' => $log->causer ? [
                    'id' => $log->causer->id,
                    'name' => $log->causer->name,
                ] : null,
                'subject_type' => $log->subject_type
                    ? class_basename($log->subject_type)
                    : null,
                'subject_id' => $log->subject_id,
                'properties' => $log->properties,
                'created_at' => $log->created_at,
            ]);

        $users = User::orderBy('name')->get(['id', 'name']);

        $eventTypes = Activity::distinct()
            ->orderBy('description')
            ->pluck('description');

        return Inertia::render('admin/activity-log/index', [
            'logs' => $logs,
            'users' => $users,
            'eventTypes' => $eventTypes,
            'filters' => $request->only('user_id', 'event', 'from', 'to'),
        ]);
    }
}
