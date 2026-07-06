<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function agent(): View
    {
        return view('dashboard.index', ['title' => 'Tableau de bord Agent']);
    }

    public function secretariat(): View
    {
        return view('dashboard.index', ['title' => 'Tableau de bord Secrétariat']);
    }

    public function di(): View
    {
        return view('dashboard.index', ['title' => 'Tableau de bord Direction Informatique']);
    }

    public function developpeur(): View
    {
        return view('dashboard.index', ['title' => 'Tableau de bord Développeur']);
    }

    public function admin(): View
    {
        return view('dashboard.index', ['title' => 'Administration']);
    }
}
