<?php

namespace App\Enums;

enum UserRole: string
{
    case Agent = 'AGENT';
    case Secretariat = 'SECRETARIAT';
    case DirectionInformatique = 'DIRECTION_INFORMATIQUE';
    case Developpeur = 'DEVELOPPEUR';
    case Admin = 'ADMIN';

    public function label(): string
    {
        return match ($this) {
            self::Agent => 'Agent',
            self::Secretariat => 'Secrétariat',
            self::DirectionInformatique => 'Direction Informatique',
            self::Developpeur => 'Développeur',
            self::Admin => 'Administrateur',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Agent => 'agent.dashboard',
            self::Secretariat => 'secretariat.dashboard',
            self::DirectionInformatique => 'di.dashboard',
            self::Developpeur => 'developpeur.dashboard',
            self::Admin => 'admin.dashboard',
        };
    }
}
