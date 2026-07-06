<?php

namespace App\Http\Controllers\Di;

use App\Enums\StatutDemande;
use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $stats = [
            'a_examiner' => Demande::aExaminerDi()->count(),
            'en_cours' => Demande::enCoursDi()->count(),
            'en_developpement' => Demande::query()
                ->whereIn('statut', [
                    StatutDemande::Affectee->value,
                    StatutDemande::EnDeveloppement->value,
                    StatutDemande::EnTest->value,
                ])
                ->count(),
            'rejetees' => Demande::query()->where('statut', StatutDemande::Rejetee)->count(),
        ];

        $demandes = Demande::visibleParDi()
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $notifications = Notification::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('di.dashboard', compact('stats', 'demandes', 'notifications'));
    }
}
