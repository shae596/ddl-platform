<?php

namespace App\Enums;

enum StatutDemande: string
{
    case Brouillon = 'BROUILLON';
    case Soumise = 'SOUMISE';
    case RecueSecretariat = 'RECUE_SECRETARIAT';
    case TransfereeDi = 'TRANSFEREE_DI';
    case EnAnalyse = 'EN_ANALYSE';
    case EnAttente = 'EN_ATTENTE';
    case Validee = 'VALIDEE';
    case Rejetee = 'REJETEE';
    case ACorriger = 'A_CORRIGER';
    case Affectee = 'AFFECTEE';
    case EnDeveloppement = 'EN_DEVELOPPEMENT';
    case EnTest = 'EN_TEST';
    case Terminee = 'TERMINEE';
    case Cloturee = 'CLOTUREE';

    public function label(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Soumise => 'Soumise',
            self::RecueSecretariat => 'Reçue secrétariat',
            self::TransfereeDi => 'Transférée DI',
            self::EnAnalyse => 'En analyse',
            self::EnAttente => 'En attente',
            self::Validee => 'Validée',
            self::Rejetee => 'Rejetée',
            self::ACorriger => 'À corriger',
            self::Affectee => 'Affectée',
            self::EnDeveloppement => 'En développement',
            self::EnTest => 'En test',
            self::Terminee => 'Terminée',
            self::Cloturee => 'Clôturée',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Brouillon => 'badge-gray',
            self::Soumise, self::RecueSecretariat, self::TransfereeDi, self::EnAnalyse,
            self::EnAttente, self::Affectee, self::EnDeveloppement, self::EnTest => 'badge-blue',
            self::Validee, self::Terminee => 'badge-green',
            self::Rejetee => 'badge-red',
            self::ACorriger => 'badge-amber',
            self::Cloturee => 'badge-gray',
        };
    }

    public function isEditableByAgent(): bool
    {
        return in_array($this, [self::Brouillon, self::ACorriger], true);
    }
}
