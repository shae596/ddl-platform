<?php

namespace App\Http\Controllers\Secretariat;

use App\Enums\StatutDemande;
use App\Enums\TypePieceJointe;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ShowsDemandeHistorique;
use App\Models\Demande;
use App\Models\Notification;
use App\Services\DemandeWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DemandeController extends Controller
{
    use ShowsDemandeHistorique;

    public function __construct(
        private readonly DemandeWorkflowService $workflow,
    ) {}

    public function index(Request $request): View
    {
        $onglet = $request->input('onglet', 'a_recevoir');

        $query = Demande::query()
            ->with('auteur')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('titre', 'ilike', '%'.$request->q.'%')
                    ->orWhere('numero', 'ilike', '%'.$request->q.'%')
                    ->orWhere('service_demandeur', 'ilike', '%'.$request->q.'%');
            }))
            ->when($request->filled('priorite'), fn ($q) => $q->where('priorite', $request->priorite));

        $demandes = match ($onglet) {
            'a_transferer' => $query->aTransfererSecretariat(),
            'transferees' => $query->transfereesParSecretariat(),
            default => $query->aRecevoirSecretariat(),
        };

        $demandes = $demandes
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('secretariat.demandes.index', [
            'demandes' => $demandes,
            'onglet' => $onglet,
            'stats' => [
                'a_recevoir' => Demande::aRecevoirSecretariat()->count(),
                'a_transferer' => Demande::aTransfererSecretariat()->count(),
                'transferees' => Demande::transfereesParSecretariat()->count(),
            ],
        ]);
    }

    public function show(Request $request, Demande $demande): View
    {
        $this->autoriserDemande($demande);

        $demande->load(['piecesJointes', 'auteur']);
        $demande->loadCount('historiqueActions');

        $cahierPdf = $demande->piecesJointes
            ->firstWhere('type', TypePieceJointe::CahierDesCharges->value);

        $this->marquerNotificationsLues($request, $demande);

        return view('secretariat.demandes.show', compact('demande', 'cahierPdf'));
    }

    public function downloadCahier(Request $request, Demande $demande): StreamedResponse
    {
        $this->autoriserDemande($demande);

        $cahier = $demande->piecesJointes()
            ->where('type', TypePieceJointe::CahierDesCharges->value)
            ->latest('created_at')
            ->firstOrFail();

        abort_unless(Storage::disk('local')->exists($cahier->chemin_stockage), 404);

        return Storage::disk('local')->download(
            $cahier->chemin_stockage,
            $cahier->nom_original,
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function recevoir(Request $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);

        $this->workflow->recevoir($demande, $request->user());

        return redirect()
            ->route('secretariat.demandes.show', $demande)
            ->with('success', 'Réception accusée. Le cahier des charges est prêt à être transféré à la Direction Informatique.');
    }

    public function transfererDi(Request $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);

        $this->workflow->transfererDi($demande, $request->user());

        return redirect()
            ->route('secretariat.demandes.show', $demande)
            ->with('success', 'Cahier des charges transféré à la Direction Informatique.');
    }

    private function autoriserDemande(Demande $demande): void
    {
        abort_if($demande->statut === StatutDemande::Brouillon, 404);
    }

    private function marquerNotificationsLues(Request $request, Demande $demande): void
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('demande_id', $demande->id)
            ->where('lue', false)
            ->update(['lue' => true]);
    }

    protected function authorizeDemandeHistorique(Request $request, Demande $demande): void
    {
        $this->autoriserDemande($demande);
    }

    protected function historiqueLayoutView(): string
    {
        return 'layouts.secretariat';
    }

    protected function historiqueBackUrl(Demande $demande): string
    {
        return route('secretariat.demandes.show', $demande);
    }
}
