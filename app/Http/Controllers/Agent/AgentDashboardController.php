<?php

namespace App\Http\Controllers\Agent;

use App\Enums\StatutDemande;
use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $stats = [
            'brouillons' => Demande::forAgent($userId)->where('statut', StatutDemande::Brouillon)->count(),
            'en_cours' => Demande::forAgent($userId)
                ->whereNotIn('statut', [
                    StatutDemande::Brouillon->value,
                    StatutDemande::Terminee->value,
                    StatutDemande::Cloturee->value,
                    StatutDemande::Rejetee->value,
                ])
                ->count(),
            'terminees' => Demande::forAgent($userId)
                ->whereIn('statut', [StatutDemande::Terminee->value, StatutDemande::Cloturee->value])
                ->count(),
        ];

        $demandes = Demande::forAgent($userId)
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $notifications = Notification::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit(5)
            ->get();

        $notificationsNonLues = Notification::query()
            ->where('user_id', $userId)
            ->where('lue', false)
            ->count();

        return view('agent.dashboard', compact('stats', 'demandes', 'notifications', 'notificationsNonLues'));
    }
}
