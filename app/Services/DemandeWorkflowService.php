<?php

namespace App\Services;

use App\Enums\StatutDemande;
use App\Enums\UserRole;
use App\Models\AffectationDev;
use App\Models\Commentaire;
use App\Models\Demande;
use App\Models\HistoriqueAction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DemandeWorkflowService
{
    public function __construct(
        private readonly NumerotationDdlService $numerotation,
        private readonly CahierDesChargesPdfService $pdfService,
        private readonly ParametreService $parametres,
    ) {}

    /**
     * Génère le PDF cahier des charges sans soumettre au secrétariat.
     * La demande reste en brouillon jusqu'à soumission explicite.
     */
    public function genererCahierDesCharges(Demande $demande, User $user): Demande
    {
        return DB::transaction(function () use ($demande, $user) {
            if (! $demande->numero) {
                $demande->update([
                    'numero' => $this->numerotation->attribuerNumero(),
                ]);
                $demande = $demande->fresh();
            }

            $this->pdfService->generer($demande);

            $this->journaliser(
                $demande,
                $user,
                $demande->statut,
                $demande->statut,
                'GENERATION_CDC',
                'Cahier des charges généré',
            );

            return $demande->fresh();
        });
    }

    /**
     * Soumet le cahier des charges au secrétariat (le PDF doit exister).
     */
    public function soumettre(Demande $demande, User $user): Demande
    {
        if (! $demande->aCahierDesCharges()) {
            throw ValidationException::withMessages([
                'action' => 'Vous devez d\'abord générer le cahier des charges avant de soumettre.',
            ]);
        }

        return DB::transaction(function () use ($demande, $user) {
            $ancien = $demande->statut;

            $demande->update([
                'statut' => StatutDemande::Soumise,
                'numero' => $demande->numero ?? $this->numerotation->attribuerNumero(),
                'date_soumission' => now(),
            ]);

            $demande = $demande->fresh();

            $this->journaliser($demande, $user, $ancien, StatutDemande::Soumise, 'SOUMISSION_CDC');
            $this->notifierSecretariat($demande);

            return $demande;
        });
    }

    public function recevoir(Demande $demande, User $user): Demande
    {
        if ($demande->statut !== StatutDemande::Soumise) {
            throw ValidationException::withMessages([
                'statut' => 'Seules les demandes soumises peuvent être accusées de réception.',
            ]);
        }

        return DB::transaction(function () use ($demande, $user) {
            $ancien = $demande->statut;

            $demande->update(['statut' => StatutDemande::RecueSecretariat]);
            $demande = $demande->fresh();

            $this->journaliser(
                $demande,
                $user,
                $ancien,
                StatutDemande::RecueSecretariat,
                'RECEPTION_CDC',
                'Cahier des charges reçu par le secrétariat',
            );

            return $demande;
        });
    }

    public function transfererDi(Demande $demande, User $user): Demande
    {
        if ($demande->statut !== StatutDemande::RecueSecretariat) {
            throw ValidationException::withMessages([
                'statut' => 'Seules les demandes reçues par le secrétariat peuvent être transférées à la DI.',
            ]);
        }

        return DB::transaction(function () use ($demande, $user) {
            $ancien = $demande->statut;

            $demande->update(['statut' => StatutDemande::TransfereeDi]);
            $demande = $demande->fresh();

            $this->journaliser(
                $demande,
                $user,
                $ancien,
                StatutDemande::TransfereeDi,
                'TRANSFERT_DI',
                'Cahier des charges transféré à la Direction Informatique',
            );

            $this->notifierDirectionInformatique($demande);

            return $demande;
        });
    }

    public function prendreEnCharge(Demande $demande, User $user): Demande
    {
        if ($demande->statut !== StatutDemande::TransfereeDi) {
            throw ValidationException::withMessages([
                'statut' => 'Seuls les cahiers transférés peuvent être pris en charge.',
            ]);
        }

        return $this->transition($demande, $user, StatutDemande::EnAnalyse, 'PRISE_EN_CHARGE', 'Prise en charge par la DI');
    }

    public function mettreEnAttente(Demande $demande, User $user, ?string $commentaire = null): Demande
    {
        if (! in_array($demande->statut, [StatutDemande::TransfereeDi, StatutDemande::EnAnalyse], true)) {
            throw ValidationException::withMessages([
                'statut' => 'Cette demande ne peut pas être mise en attente.',
            ]);
        }

        return $this->transition($demande, $user, StatutDemande::EnAttente, 'MISE_EN_ATTENTE', $commentaire);
    }

    public function reprendreAnalyse(Demande $demande, User $user): Demande
    {
        if ($demande->statut !== StatutDemande::EnAttente) {
            throw ValidationException::withMessages([
                'statut' => 'Seules les demandes en attente peuvent reprendre l\'analyse.',
            ]);
        }

        return $this->transition($demande, $user, StatutDemande::EnAnalyse, 'REPRISE_ANALYSE', 'Reprise de l\'analyse');
    }

    public function valider(Demande $demande, User $user, ?string $commentaire = null): Demande
    {
        if (! in_array($demande->statut, [StatutDemande::EnAnalyse, StatutDemande::EnAttente], true)) {
            throw ValidationException::withMessages([
                'statut' => 'Seules les demandes en analyse ou en attente peuvent être validées.',
            ]);
        }

        $demande = $this->transition($demande, $user, StatutDemande::Validee, 'VALIDATION_DI', $commentaire);
        $this->notifierAgent($demande, 'VALIDATION', 'Cahier des charges validé', sprintf(
            'Votre cahier des charges %s — %s a été validé par la Direction Informatique.',
            $demande->numero,
            $demande->titre,
        ));

        return $demande;
    }

    public function rejeter(Demande $demande, User $user, string $motif): Demande
    {
        if ($demande->statut !== StatutDemande::EnAnalyse) {
            throw ValidationException::withMessages([
                'statut' => 'Seules les demandes en analyse peuvent être rejetées.',
            ]);
        }

        return DB::transaction(function () use ($demande, $user, $motif) {
            $ancien = $demande->statut;

            $demande->update([
                'statut' => StatutDemande::Rejetee,
                'motif_rejet' => $motif,
            ]);

            $demande = $demande->fresh();

            $this->journaliser($demande, $user, $ancien, StatutDemande::Rejetee, 'REJET_DI', $motif);
            $this->notifierAgent($demande, 'REJET', 'Cahier des charges rejeté', sprintf(
                'Votre cahier des charges %s — %s a été rejeté. Motif : %s',
                $demande->numero,
                $demande->titre,
                $motif,
            ));

            return $demande;
        });
    }

    public function demanderCorrection(Demande $demande, User $user, string $commentaire): Demande
    {
        if ($demande->statut !== StatutDemande::EnAnalyse) {
            throw ValidationException::withMessages([
                'statut' => 'Seules les demandes en analyse peuvent être renvoyées pour correction.',
            ]);
        }

        $demande = $this->transition($demande, $user, StatutDemande::ACorriger, 'DEMANDE_CORRECTION', $commentaire);
        $this->notifierAgent($demande, 'A_CORRIGER', 'Informations complémentaires demandées', sprintf(
            'La DI demande des corrections pour %s — %s : %s',
            $demande->numero,
            $demande->titre,
            $commentaire,
        ));

        return $demande;
    }

    public function definirDelaiPrevisionnel(Demande $demande, User $user, string $date): Demande
    {
        $demande->update(['delai_previsionnel' => $date]);
        $demande = $demande->fresh();

        $this->journaliser(
            $demande,
            $user,
            $demande->statut,
            $demande->statut,
            'DELAI_PREVISIONNEL',
            'Délai prévisionnel défini au '.$date,
        );

        return $demande;
    }

    /**
     * @param  array<int, string>  $developpeurIds
     */
    public function affecterDeveloppeurs(Demande $demande, User $user, array $developpeurIds): Demande
    {
        if ($demande->statut !== StatutDemande::Validee) {
            throw ValidationException::withMessages([
                'statut' => 'Seules les demandes validées peuvent être affectées à un développeur.',
            ]);
        }

        $developpeurs = User::query()
            ->where('role', UserRole::Developpeur)
            ->where('actif', true)
            ->whereIn('id', $developpeurIds)
            ->get();

        if ($developpeurs->isEmpty()) {
            throw ValidationException::withMessages([
                'developpeur_ids' => 'Sélectionnez au moins un développeur actif.',
            ]);
        }

        return DB::transaction(function () use ($demande, $user, $developpeurs) {
            $ancien = $demande->statut;

            $demande->update(['statut' => StatutDemande::Affectee]);
            $demande = $demande->fresh();

            foreach ($developpeurs as $developpeur) {
                AffectationDev::updateOrCreate(
                    [
                        'demande_id' => $demande->id,
                        'developpeur_id' => $developpeur->id,
                    ],
                    [
                        'affecte_par_id' => $user->id,
                        'actif' => true,
                        'created_at' => now(),
                    ],
                );

                $this->creerNotification(
                    'AFFECTATION',
                    $developpeur->id,
                    $demande->id,
                    'Nouvelle demande affectée',
                    sprintf(
                        'La demande %s — %s vous a été affectée par la Direction Informatique.',
                        $demande->numero,
                        $demande->titre,
                    ),
                );
            }

            $noms = $developpeurs->map(fn (User $d) => $d->fullName())->join(', ');
            $this->journaliser($demande, $user, $ancien, StatutDemande::Affectee, 'AFFECTATION_DEV', 'Affecté à : '.$noms);

            return $demande;
        });
    }

    public function ajouterCommentaire(Demande $demande, User $user, string $contenu, bool $interne = false): Commentaire
    {
        $commentaire = Commentaire::create([
            'demande_id' => $demande->id,
            'auteur_id' => $user->id,
            'contenu' => $contenu,
            'interne' => $interne,
        ]);

        if (! $interne && $demande->auteur_id !== $user->id) {
            $this->notifierAgent($demande, 'COMMENTAIRE', 'Nouveau commentaire de la DI', sprintf(
                'Un commentaire a été ajouté sur votre demande %s — %s.',
                $demande->numero,
                $demande->titre,
            ));
        }

        return $commentaire;
    }

    private function transition(
        Demande $demande,
        User $user,
        StatutDemande $nouveau,
        string $action,
        ?string $commentaire = null,
    ): Demande {
        return DB::transaction(function () use ($demande, $user, $nouveau, $action, $commentaire) {
            $ancien = $demande->statut;
            $demande->update(['statut' => $nouveau]);
            $demande = $demande->fresh();
            $this->journaliser($demande, $user, $ancien, $nouveau, $action, $commentaire);

            return $demande;
        });
    }

    private function journaliser(
        Demande $demande,
        User $user,
        ?StatutDemande $ancien,
        StatutDemande $nouveau,
        string $action,
        ?string $commentaire = null,
    ): void {
        HistoriqueAction::create([
            'demande_id' => $demande->id,
            'utilisateur_id' => $user->id,
            'ancien_statut' => $ancien?->value,
            'nouveau_statut' => $nouveau->value,
            'action' => $action,
            'commentaire' => $commentaire,
            'created_at' => now(),
        ]);
    }

    private function notifierSecretariat(Demande $demande): void
    {
        $secretaires = User::query()
            ->where('role', UserRole::Secretariat)
            ->where('actif', true)
            ->get();

        foreach ($secretaires as $secretaire) {
            $this->creerNotification(
                'SOUMISSION',
                $secretaire->id,
                $demande->id,
                'Nouveau cahier des charges soumis',
                sprintf(
                    'Le cahier des charges %s — %s a été soumis par %s.',
                    $demande->numero,
                    $demande->titre,
                    $demande->nom_demandeur,
                ),
            );
        }
    }

    private function notifierDirectionInformatique(Demande $demande): void
    {
        $directeurs = User::query()
            ->where('role', UserRole::DirectionInformatique)
            ->where('actif', true)
            ->get();

        foreach ($directeurs as $directeur) {
            $this->creerNotification(
                'TRANSFERT_DI',
                $directeur->id,
                $demande->id,
                'Nouveau cahier des charges à examiner',
                sprintf(
                    'Le cahier des charges %s — %s vous a été transféré par le secrétariat.',
                    $demande->numero,
                    $demande->titre,
                ),
            );
        }
    }

    private function notifierAgent(Demande $demande, string $type, string $titre, string $message): void
    {
        $this->creerNotification($type, $demande->auteur_id, $demande->id, $titre, $message);
    }

    private function creerNotification(
        string $type,
        string $userId,
        ?string $demandeId,
        string $titre,
        string $message,
    ): void {
        $paramType = match ($type) {
            'COMMENTAIRE_DEV' => 'COMMENTAIRE',
            default => $type,
        };

        if (! $this->parametres->notificationActive($paramType)) {
            return;
        }

        Notification::create([
            'user_id' => $userId,
            'demande_id' => $demandeId,
            'type' => $type,
            'titre' => $titre,
            'message' => $message,
            'lue' => false,
            'created_at' => now(),
        ]);
    }

    public function demarrerDeveloppement(Demande $demande, User $user): Demande
    {
        $this->autoriserDeveloppeur($demande, $user);

        if ($demande->statut !== StatutDemande::Affectee) {
            throw ValidationException::withMessages([
                'statut' => 'Seules les demandes affectées peuvent être démarrées.',
            ]);
        }

        $demande = $this->transition($demande, $user, StatutDemande::EnDeveloppement, 'DEMARRAGE_DEV', 'Développement démarré');
        $this->notifierDirectionInformatiqueStatut($demande, 'STATUT_DEV', 'Développement démarré', sprintf(
            'Le développement de %s — %s a été démarré par %s.',
            $demande->numero,
            $demande->titre,
            $user->fullName(),
        ));

        return $demande;
    }

    public function passerEnTest(Demande $demande, User $user, ?string $commentaire = null): Demande
    {
        $this->autoriserDeveloppeur($demande, $user);

        if ($demande->statut !== StatutDemande::EnDeveloppement) {
            throw ValidationException::withMessages([
                'statut' => 'Seules les demandes en développement peuvent passer en test.',
            ]);
        }

        $demande = $this->transition($demande, $user, StatutDemande::EnTest, 'PASSAGE_TEST', $commentaire ?? 'Livraison en phase de test');
        $this->notifierDirectionInformatiqueStatut($demande, 'STATUT_DEV', 'Demande en test', sprintf(
            '%s — %s est passée en test. Développeur : %s.',
            $demande->numero,
            $demande->titre,
            $user->fullName(),
        ));

        return $demande;
    }

    public function ajouterCommentaireDeveloppeur(Demande $demande, User $user, string $contenu): Commentaire
    {
        $this->autoriserDeveloppeur($demande, $user);

        $commentaire = Commentaire::create([
            'demande_id' => $demande->id,
            'auteur_id' => $user->id,
            'contenu' => $contenu,
            'interne' => false,
        ]);

        $this->notifierDirectionInformatiqueStatut($demande, 'COMMENTAIRE_DEV', 'Commentaire développeur', sprintf(
            '%s a commenté la demande %s — %s.',
            $user->fullName(),
            $demande->numero,
            $demande->titre,
        ));

        return $commentaire;
    }

    private function autoriserDeveloppeur(Demande $demande, User $user): void
    {
        if (! $demande->estAffecteeA($user->id)) {
            throw ValidationException::withMessages([
                'demande' => 'Vous n\'êtes pas affecté à cette demande.',
            ]);
        }
    }

    private function notifierDirectionInformatiqueStatut(Demande $demande, string $type, string $titre, string $message): void
    {
        $directeurs = User::query()
            ->where('role', UserRole::DirectionInformatique)
            ->where('actif', true)
            ->get();

        foreach ($directeurs as $directeur) {
            $this->creerNotification($type, $directeur->id, $demande->id, $titre, $message);
        }
    }
}
