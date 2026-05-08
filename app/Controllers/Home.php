<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $data['title'] = 'Chat';
        $data['js']    = ['chat'];
        $data['css']   = ['chat'];
        $data['uuid']  = null;
        return view('chat', $data);
    }

    public function chatSession(string $uuid): string
    {
        $data['title'] = 'Chat';
        $data['js']    = ['chat'];
        $data['css']   = ['chat'];
        $data['uuid']  = $uuid;
        return view('chat', $data);
    }
}
