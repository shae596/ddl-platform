<?php

namespace App\Http\Controllers\Di;

use App\Enums\StatutDemande;
use App\Enums\TypePieceJointe;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\DownloadsCahierDesCharges;
use App\Http\Controllers\Concerns\ShowsDemandeHistorique;
use App\Http\Requests\Di\AffecterDeveloppeursRequest;
use App\Http\Requests\Di\CommentaireRequest;
use App\Http\Requests\Di\DelaiPrevisionnelRequest;
use App\Http\Requests\Di\DemanderCorrectionRequest;
use App\Http\Requests\Di\RejeterDemandeRequest;
use App\Models\Demande;
use App\Models\Notification;
use App\Models\User;
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
        $onglet = $request->input('onglet', 'a_examiner');

        $query = Demande::query()
            ->with('auteur')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('titre', 'ilike', '%'.$request->q.'%')
                    ->orWhere('numero', 'ilike', '%'.$request->q.'%')
                    ->orWhere('service_demandeur', 'ilike', '%'.$request->q.'%');
            }))
            ->when($request->filled('priorite'), fn ($q) => $q->where('priorite', $request->priorite))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('service'), fn ($q) => $q->where('service_demandeur', 'ilike', '%'.$request->service.'%'))
            ->when($request->filled('date_debut'), fn ($q) => $q->whereDate('date_soumission', '>=', $request->date_debut))
            ->when($request->filled('date_fin'), fn ($q) => $q->whereDate('date_soumission', '<=', $request->date_fin));

        $demandes = match ($onglet) {
            'en_cours' => $query->enCoursDi(),
            'suivies' => $query->suiviesDi(),
            default => $query->aExaminerDi(),
        };

        $demandes = $demandes
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('di.demandes.index', [
            'demandes' => $demandes,
            'onglet' => $onglet,
            'statuts' => StatutDemande::cases(),
            'stats' => [
                'a_examiner' => Demande::aExaminerDi()->count(),
                'en_cours' => Demande::enCoursDi()->count(),
                'suivies' => Demande::suiviesDi()->count(),
            ],
        ]);
    }

    public function show(Request $request, Demande $demande): View
    {
        $this->autoriserDemande($demande);

        $demande->load([
            'piecesJointes',
            'auteur',
            'commentaires.auteur',
            'affectationsDev.developpeur',
        ]);
        $demande->loadCount('historiqueActions');

        $cahierPdf = $demande->piecesJointes
            ->firstWhere('type', TypePieceJointe::CahierDesCharges->value);

        $developpeurs = User::query()
            ->where('role', UserRole::Developpeur)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        $this->marquerNotificationsLues($request, $demande);

        return view('di.demandes.show', compact('demande', 'cahierPdf', 'developpeurs'));
    }

    public function downloadCahier(Request $request, Demande $demande): StreamedResponse
    {
        $this->autoriserDemande($demande);

        return $this->telechargerCahier($demande);
    }

    public function prendreEnCharge(Request $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);
        $this->workflow->prendreEnCharge($demande, $request->user());

        return back()->with('success', 'Demande prise en charge.');
    }

    public function mettreEnAttente(Request $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);
        $this->workflow->mettreEnAttente($demande, $request->user(), $request->input('commentaire'));

        return back()->with('success', 'Demande mise en attente.');
    }

    public function reprendreAnalyse(Request $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);
        $this->workflow->reprendreAnalyse($demande, $request->user());

        return back()->with('success', 'Analyse reprise.');
    }

    public function valider(Request $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);
        $this->workflow->valider($demande, $request->user(), $request->input('commentaire'));

        return back()->with('success', 'Cahier des charges validé. Vous pouvez affecter un développeur.');
    }

    public function rejeter(RejeterDemandeRequest $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);
        $this->workflow->rejeter($demande, $request->user(), $request->validated('motif_rejet'));

        return back()->with('success', 'Cahier des charges rejeté. L\'agent a été notifié.');
    }

    public function demanderCorrection(DemanderCorrectionRequest $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);
        $this->workflow->demanderCorrection($demande, $request->user(), $request->validated('commentaire'));

        return back()->with('success', 'Demande renvoyée à l\'agent pour correction.');
    }

    public function definirDelai(DelaiPrevisionnelRequest $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);
        $this->workflow->definirDelaiPrevisionnel(
            $demande,
            $request->user(),
            $request->validated('delai_previsionnel'),
        );

        return back()->with('success', 'Délai prévisionnel enregistré.');
    }

    public function affecter(AffecterDeveloppeursRequest $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);
        $this->workflow->affecterDeveloppeurs($demande, $request->user(), $request->validated('developpeur_ids'));

        return back()->with('success', 'Développeur(s) affecté(s).');
    }

    public function commenter(CommentaireRequest $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($demande);
        $this->workflow->ajouterCommentaire(
            $demande,
            $request->user(),
            $request->validated('contenu'),
            $request->boolean('interne'),
        );

        return back()->with('success', 'Commentaire ajouté.');
    }

    private function autoriserDemande(Demande $demande): void
    {
        abort_if(in_array($demande->statut, [StatutDemande::Brouillon, StatutDemande::Soumise, StatutDemande::RecueSecretariat], true), 404);
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
        return 'layouts.di';
    }

    protected function historiqueBackUrl(Demande $demande): string
    {
        return route('di.demandes.show', $demande);
    }
}
