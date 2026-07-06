<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatutDemande;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\HistoriqueAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'utilisateurs' => User::query()->count(),
            'utilisateurs_actifs' => User::query()->where('actif', true)->count(),
            'demandes' => Demande::query()->count(),
            'demandes_en_cours' => Demande::query()
                ->whereNotIn('statut', [
                    StatutDemande::Brouillon->value,
                    StatutDemande::Terminee->value,
                    StatutDemande::Cloturee->value,
                    StatutDemande::Rejetee->value,
                ])
                ->count(),
        ];

        $parRole = collect(UserRole::cases())->mapWithKeys(fn (UserRole $role) => [
            $role->label() => User::query()->where('role', $role)->where('actif', true)->count(),
        ]);

        $historiqueRecent = HistoriqueAction::query()
            ->with(['utilisateur', 'demande'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'parRole', 'historiqueRecent'));
    }
}
