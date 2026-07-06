<?php

namespace App\Http\Controllers\Secretariat;

use App\Enums\StatutDemande;
use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecretariatDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $stats = [
            'a_recevoir' => Demande::aRecevoirSecretariat()->count(),
            'a_transferer' => Demande::aTransfererSecretariat()->count(),
            'transferees' => Demande::transfereesParSecretariat()->count(),
        ];

        $demandes = Demande::visibleParSecretariat()
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $notifications = Notification::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('secretariat.dashboard', compact('stats', 'demandes', 'notifications'));
    }
}
