<?php

namespace App\Http\Controllers\Agent;

use App\Enums\Priorite;
use App\Enums\StatutDemande;
use App\Enums\TypePieceJointe;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\DownloadsCahierDesCharges;
use App\Http\Controllers\Concerns\ShowsDemandeHistorique;
use App\Http\Requests\Agent\DemandeRequest;
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
    use DownloadsCahierDesCharges, ShowsDemandeHistorique;

    public function __construct(
        private readonly DemandeWorkflowService $workflow,
    ) {}

    public function index(Request $request): View
    {
        $demandes = Demande::forAgent($request->user()->id)
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('titre', 'ilike', '%'.$request->q.'%')
                    ->orWhere('numero', 'ilike', '%'.$request->q.'%');
            }))
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('agent.demandes.index', [
            'demandes' => $demandes,
            'statuts' => StatutDemande::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('agent.demandes.form', [
            'demande' => new Demande([
                'priorite' => Priorite::Moyenne,
                'service_demandeur' => $user->service ?? '',
                'nom_demandeur' => $user->fullName(),
                'email_demandeur' => $user->email,
                'telephone_demandeur' => $user->telephone,
                'objectifs_specifiques' => [''],
            ]),
            'priorites' => Priorite::cases(),
            'aCahier' => false,
        ]);
    }

    public function store(DemandeRequest $request): RedirectResponse
    {
        $demande = Demande::create([
            ...$request->donneesDemande(),
            'statut' => StatutDemande::Brouillon,
            'auteur_id' => $request->user()->id,
        ]);

        return $this->traiterAction($request, $demande->fresh());
    }

    public function show(Request $request, Demande $demande): View
    {
        $this->autoriserDemande($request, $demande);

        $demande->load(['piecesJointes']);
        $demande->loadCount('historiqueActions');

        $cahierPdf = $demande->piecesJointes
            ->firstWhere('type', TypePieceJointe::CahierDesCharges->value);

        $this->marquerNotificationsLues($request, $demande);

        return view('agent.demandes.show', compact('demande', 'cahierPdf'));
    }

    public function downloadCahier(Request $request, Demande $demande): StreamedResponse
    {
        $this->autoriserDemande($request, $demande);

        return $this->telechargerCahier($demande);
    }

    public function edit(Request $request, Demande $demande): View|RedirectResponse
    {
        $this->autoriserDemande($request, $demande);

        if (! $demande->statut->isEditableByAgent()) {
            return redirect()
                ->route('agent.demandes.show', $demande)
                ->with('error', 'Cette demande n\'est plus modifiable.');
        }

        $demande->load('piecesJointes');

        $objectifs = $demande->objectifs_specifiques ?: [''];
        $demande->objectifs_specifiques = $objectifs;

        return view('agent.demandes.form', [
            'demande' => $demande,
            'priorites' => Priorite::cases(),
            'aCahier' => $demande->aCahierDesCharges(),
        ]);
    }

    public function update(DemandeRequest $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($request, $demande);

        if (! $demande->statut->isEditableByAgent()) {
            return redirect()
                ->route('agent.demandes.show', $demande)
                ->with('error', 'Cette demande n\'est plus modifiable.');
        }

        $demande->update($request->donneesDemande());

        return $this->traiterAction($request, $demande->fresh());
    }

    public function destroy(Request $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($request, $demande);

        if ($demande->statut !== StatutDemande::Brouillon) {
            return back()->with('error', 'Seuls les brouillons peuvent être supprimés.');
        }

        foreach ($demande->piecesJointes as $piece) {
            Storage::disk('local')->delete($piece->chemin_stockage);
        }

        $demande->delete();

        return redirect()->route('agent.demandes.index')->with('success', 'Brouillon supprimé.');
    }

    private function traiterAction(DemandeRequest $request, Demande $demande): RedirectResponse
    {
        $action = $request->input('action');
        $user = $request->user();

        if ($action === 'generer_cdc') {
            $demande = $this->workflow->genererCahierDesCharges($demande, $user);

            return redirect()
                ->route('agent.demandes.edit', $demande)
                ->with('success', 'Cahier des charges généré. Numéro : '.$demande->numero.' — vous pouvez le télécharger puis le soumettre.');
        }

        if ($action === 'soumettre') {
            $demande = $this->workflow->soumettre($demande, $user);

            return redirect()
                ->route('agent.demandes.show', $demande)
                ->with('success', 'Cahier des charges soumis au secrétariat. Numéro : '.$demande->numero);
        }

        return redirect()
            ->route('agent.demandes.edit', $demande)
            ->with('success', 'Brouillon enregistré.');
    }

    private function autoriserDemande(Request $request, Demande $demande): void
    {
        abort_unless($demande->auteur_id === $request->user()->id, 403);
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
        $this->autoriserDemande($request, $demande);
    }

    protected function historiqueLayoutView(): string
    {
        return 'layouts.agent';
    }

    protected function historiqueBackUrl(Demande $demande): string
    {
        return route('agent.demandes.show', $demande);
    }
}
