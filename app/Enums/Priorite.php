<?php

namespace App\Enums;

enum Priorite: string
{
    case Basse = 'BASSE';
    case Moyenne = 'MOYENNE';
    case Haute = 'HAUTE';
    case Critique = 'CRITIQUE';

    public function label(): string
    {
        return match ($this) {
            self::Basse => 'Basse',
            self::Moyenne => 'Moyenne',
            self::Haute => 'Haute',
            self::Critique => 'Critique',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Basse => 'badge-gray',
            self::Moyenne => 'badge-blue',
            self::Haute => 'badge-amber',
            self::Critique => 'badge-red',
        };
    }
}
