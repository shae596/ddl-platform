<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Demande;
use Illuminate\Http\Request;
use Illuminate\View\View;

trait ShowsDemandeHistorique
{
    public function historique(Request $request, Demande $demande): View
    {
        $this->authorizeDemandeHistorique($request, $demande);

        $demande->load(['historiqueActions.utilisateur']);

        return view('demandes.historique', [
            'demande' => $demande,
            'layout' => $this->historiqueLayoutView(),
            'backUrl' => $this->historiqueBackUrl($demande),
        ]);
    }

    abstract protected function authorizeDemandeHistorique(Request $request, Demande $demande): void;

    abstract protected function historiqueLayoutView(): string;

    abstract protected function historiqueBackUrl(Demande $demande): string;
}
