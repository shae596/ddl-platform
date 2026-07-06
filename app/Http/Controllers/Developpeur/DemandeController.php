<?php

namespace App\Http\Controllers\Developpeur;

use App\Enums\StatutDemande;
use App\Enums\TypePieceJointe;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ShowsDemandeHistorique;
use App\Http\Requests\Developpeur\CommentaireRequest;
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
        $userId = $request->user()->id;
        $onglet = $request->input('onglet', 'a_demarrer');

        $query = Demande::forDeveloppeur($userId)
            ->with('auteur')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('titre', 'ilike', '%'.$request->q.'%')
                    ->orWhere('numero', 'ilike', '%'.$request->q.'%');
            }))
            ->when($request->filled('priorite'), fn ($q) => $q->where('priorite', $request->priorite));

        $demandes = match ($onglet) {
            'en_developpement' => $query->where('statut', StatutDemande::EnDeveloppement),
            'en_test' => $query->where('statut', StatutDemande::EnTest),
            'terminees' => $query->whereIn('statut', [StatutDemande::Terminee, StatutDemande::Cloturee]),
            default => $query->where('statut', StatutDemande::Affectee),
        };

        $base = Demande::forDeveloppeur($userId);

        $demandes = $demandes
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('developpeur.demandes.index', [
            'demandes' => $demandes,
            'onglet' => $onglet,
            'stats' => [
                'a_demarrer' => (clone $base)->where('statut', StatutDemande::Affectee)->count(),
                'en_developpement' => (clone $base)->where('statut', StatutDemande::EnDeveloppement)->count(),
                'en_test' => (clone $base)->where('statut', StatutDemande::EnTest)->count(),
                'terminees' => (clone $base)->whereIn('statut', [StatutDemande::Terminee, StatutDemande::Cloturee])->count(),
            ],
        ]);
    }

    public function show(Request $request, Demande $demande): View
    {
        $this->autoriserDemande($request, $demande);

        $demande->load(['piecesJointes', 'auteur', 'commentaires.auteur']);
        $demande->loadCount('historiqueActions');

        $cahierPdf = $demande->piecesJointes
            ->firstWhere('type', TypePieceJointe::CahierDesCharges->value);

        $this->marquerNotificationsLues($request, $demande);

        return view('developpeur.demandes.show', compact('demande', 'cahierPdf'));
    }

    public function downloadCahier(Request $request, Demande $demande): StreamedResponse
    {
        $this->autoriserDemande($request, $demande);

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

    public function demarrer(Request $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($request, $demande);
        $this->workflow->demarrerDeveloppement($demande, $request->user());

        return back()->with('success', 'Développement démarré. La Direction Informatique a été notifiée.');
    }

    public function passerEnTest(Request $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($request, $demande);
        $this->workflow->passerEnTest($demande, $request->user(), $request->input('commentaire'));

        return back()->with('success', 'Demande passée en test. La Direction Informatique a été notifiée.');
    }

    public function commenter(CommentaireRequest $request, Demande $demande): RedirectResponse
    {
        $this->autoriserDemande($request, $demande);
        $this->workflow->ajouterCommentaireDeveloppeur($demande, $request->user(), $request->validated('contenu'));

        return back()->with('success', 'Commentaire publié. La DI a été notifiée.');
    }

    private function autoriserDemande(Request $request, Demande $demande): void
    {
        abort_unless($demande->estAffecteeA($request->user()->id), 403);
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
        return 'layouts.developpeur';
    }

    protected function historiqueBackUrl(Demande $demande): string
    {
        return route('developpeur.demandes.show', $demande);
    }
}
