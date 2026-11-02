<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** BA3: layar Log Aktivitas (hanya Super Admin). */
class LogController extends Controller
{
    protected const PER_PAGE = 25;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $group = $request->query('group');

        $logs = ActivityLog::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.addcslashes($q, '%_\\').'%';
                $query->where(fn ($w) => $w->where('description', 'like', $like)->orWhere('user_name', 'like', $like));
            })
            ->when(array_key_exists((string) $group, ActivityLog::GROUPS), fn ($query) => $query->where('action', 'like', $group.'.%'))
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('pages.admin.logs.index', [
            'logs' => $logs,
            'q' => $q,
            'group' => $group,
            'filtered' => $q !== '' || $group,
        ]);
    }
}