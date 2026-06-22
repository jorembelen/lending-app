<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $routes = [
            'admin'     => 'admin.dashboard',
            'collector' => 'collector.route',
            'borrower'  => 'borrower.home',
        ];

        foreach ($routes as $role => $route) {
            if (auth()->user()->hasRole($role)) {
                return redirect()->route($route);
            }
        }

        return view('home');
    }
}
