<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoriqueAction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoriqueController extends Controller
{
    public function index(Request $request): View
    {
        $historique = HistoriqueAction::query()
            ->with(['utilisateur', 'demande'])
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('action', 'ilike', '%'.$request->q.'%')
                    ->orWhere('commentaire', 'ilike', '%'.$request->q.'%')
                    ->orWhereHas('demande', fn ($q) => $q
                        ->where('numero', 'ilike', '%'.$request->q.'%')
                        ->orWhere('titre', 'ilike', '%'.$request->q.'%'));
            }))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($request->filled('date_debut'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_debut))
            ->when($request->filled('date_fin'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_fin))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $actions = HistoriqueAction::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.historique.index', compact('historique', 'actions'));
    }
}
