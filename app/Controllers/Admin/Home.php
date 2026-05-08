<?php

namespace App\Controllers\Admin;

use App\Models\ChatSessionModel;
use App\Models\ChatMessageModel;

class Home extends BaseController
{
    public function index()
    {
        $sessions = new ChatSessionModel();
        $messages = new ChatMessageModel();

        $sessionCount = $sessions->countAll();
        $messageCount = $messages->countAll();
        $pinnedCount  = $sessions->where('pinned', 1)->countAllResults();

        $data['stats'] = [
            'sessions' => $sessionCount,
            'messages' => $messageCount,
            'pinned'   => $pinnedCount,
            'avg'      => $sessionCount > 0 ? round($messageCount / $sessionCount, 1) : 0,
        ];

        $data['recent_sessions'] = $sessions
            ->orderBy('created_at', 'DESC')
            ->limit(8)
            ->find();

        $data['js']    = ['admin/home'];
        $data['css']   = ['admin/home'];
        $data['title'] = 'Admin Dashboard';

        return view('admin/home', $data);
    }
}
