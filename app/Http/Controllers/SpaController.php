<?php

namespace App\Http\Controllers;

class SpaController extends Controller
{
    public function index()
    {
        $path = public_path('index.html');

        if (!file_exists($path)) {
            abort(404, 'SPA index.html not found');
        }

        return response(file_get_contents($path), 200)
            ->header('Content-Type', 'text/html');
    }

    public function login()
    {
        return redirect('/admin/login');
    }
}
