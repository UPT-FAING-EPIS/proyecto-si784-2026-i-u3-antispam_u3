<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Channel;
use App\Http\Controllers\Controller;
use App\Models\AnalysisLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $channel = $request->get('channel');
        $spamFilter = $request->get('spam');

        $query = AnalysisLog::latest();

        if ($channel) {
            $query->channel($channel);
        }

        if ($spamFilter === 'spam') {
            $query->spam();
        } elseif ($spamFilter === 'clean') {
            $query->clean();
        }

        $logs = $query->paginate(25)->appends($request->only('channel', 'spam'));
        $channels = Channel::cases();

        return view('admin.audit-log.index', compact('logs', 'channels', 'channel', 'spamFilter'));
    }

    public function metrics(): View
    {
        $byChannel = AnalysisLog::query()
            ->select('channel')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_spam = 1 THEN 1 ELSE 0 END) as spam_count')
            ->groupBy('channel')
            ->get();

        return view('admin.metrics', compact('byChannel'));
    }
}
