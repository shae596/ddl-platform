<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ParametreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParametreController extends Controller
{
    public function __construct(
        private readonly ParametreService $parametres,
    ) {}

    public function index(): View
    {
        return view('admin.parametres.index', [
            'notifications' => $this->parametres->notificationsConfig(),
        ]);
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $active = $request->input('notifications', []);

        foreach (ParametreService::NOTIFICATIONS as $type => $cle) {
            $this->parametres->definir($cle, in_array($cle, $active, true) ? '1' : '0');
        }

        return back()->with('success', 'Paramètres de notification enregistrés.');
    }
}
