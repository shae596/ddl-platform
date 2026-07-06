<?php

namespace App\Models;

use App\Enums\Priorite;
use App\Enums\StatutDemande;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Demande extends Model
{
    use HasUuids;

    protected $fillable = [
        'numero',
        'statut',
        'priorite',
        'titre',
        'service_demandeur',
        'nom_demandeur',
        'email_demandeur',
        'telephone_demandeur',
        'date_souhaitee_livraison',
        'contexte',
        'problematique',
        'objectif_general',
        'objectifs_specifiques',
        'description_fonctionnelle',
        'utilisateurs_cibles',
        'hors_perimetre',
        'contraintes_techniques',
        'contraintes_reglementaires',
        'dependances',
        'motif_rejet',
        'date_soumission',
        'delai_previsionnel',
        'auteur_id',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutDemande::class,
            'priorite' => Priorite::class,
            'objectifs_specifiques' => 'array',
            'date_souhaitee_livraison' => 'date',
            'date_soumission' => 'datetime',
            'delai_previsionnel' => 'date',
        ];
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function piecesJointes(): HasMany
    {
        return $this->hasMany(PieceJointe::class);
    }

    public function historiqueActions(): HasMany
    {
        return $this->hasMany(HistoriqueAction::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function commentaires(): HasMany
    {
        return $this->hasMany(Commentaire::class);
    }

    public function affectationsDev(): HasMany
    {
        return $this->hasMany(AffectationDev::class);
    }

    public function aCahierDesCharges(): bool
    {
        return $this->piecesJointes()
            ->where('type', \App\Enums\TypePieceJointe::CahierDesCharges->value)
            ->exists();
    }

    public function scopeForAgent($query, string $userId)
    {
        return $query->where('auteur_id', $userId);
    }

    public function scopeARecevoirSecretariat($query)
    {
        return $query->where('statut', StatutDemande::Soumise);
    }

    public function scopeATransfererSecretariat($query)
    {
        return $query->where('statut', StatutDemande::RecueSecretariat);
    }

    public function scopeTransfereesParSecretariat($query)
    {
        return $query->whereNotIn('statut', [
            StatutDemande::Brouillon->value,
            StatutDemande::Soumise->value,
            StatutDemande::RecueSecretariat->value,
        ]);
    }

    public function scopeVisibleParSecretariat($query)
    {
        return $query->whereNot('statut', StatutDemande::Brouillon);
    }

    public function scopeVisibleParDi($query)
    {
        return $query->whereNotIn('statut', [
            StatutDemande::Brouillon->value,
            StatutDemande::Soumise->value,
            StatutDemande::RecueSecretariat->value,
        ]);
    }

    public function scopeAExaminerDi($query)
    {
        return $query->where('statut', StatutDemande::TransfereeDi);
    }

    public function scopeEnCoursDi($query)
    {
        return $query->whereIn('statut', [
            StatutDemande::EnAnalyse->value,
            StatutDemande::EnAttente->value,
            StatutDemande::Validee->value,
        ]);
    }

    public function scopeSuiviesDi($query)
    {
        return $query->whereIn('statut', [
            StatutDemande::Affectee->value,
            StatutDemande::EnDeveloppement->value,
            StatutDemande::EnTest->value,
            StatutDemande::Terminee->value,
            StatutDemande::Cloturee->value,
            StatutDemande::Rejetee->value,
            StatutDemande::ACorriger->value,
        ]);
    }

    public function scopeForDeveloppeur($query, string $userId)
    {
        return $query->whereHas('affectationsDev', fn ($q) => $q
            ->where('developpeur_id', $userId)
            ->where('actif', true));
    }

    public function estAffecteeA(string $userId): bool
    {
        return $this->affectationsDev()
            ->where('developpeur_id', $userId)
            ->where('actif', true)
            ->exists();
    }
}
