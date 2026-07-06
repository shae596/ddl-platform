<?php

namespace App\Http\Controllers\Developpeur;

use App\Enums\StatutDemande;
use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeveloppeurDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $base = Demande::forDeveloppeur($userId);

        $stats = [
            'a_demarrer' => (clone $base)->where('statut', StatutDemande::Affectee)->count(),
            'en_developpement' => (clone $base)->where('statut', StatutDemande::EnDeveloppement)->count(),
            'en_test' => (clone $base)->where('statut', StatutDemande::EnTest)->count(),
            'terminees' => (clone $base)->whereIn('statut', [StatutDemande::Terminee, StatutDemande::Cloturee])->count(),
        ];

        $demandes = Demande::forDeveloppeur($userId)
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $notifications = Notification::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('developpeur.dashboard', compact('stats', 'demandes', 'notifications'));
    }
}
