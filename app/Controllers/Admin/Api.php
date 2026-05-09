<?php

namespace App\Controllers\Admin;

class Api extends BaseController
{
    public function index()
    {
        $data['title'] = 'API Reference';

        return view('admin/api', $data);
    }
}
