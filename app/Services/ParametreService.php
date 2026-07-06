<?php

namespace App\Services;

use App\Models\Parametre;

class ParametreService
{
    /** @var array<string, string> */
    public const NOTIFICATIONS = [
        'SOUMISSION' => 'notif_soumission',
        'TRANSFERT_DI' => 'notif_transfert_di',
        'VALIDATION' => 'notif_validation',
        'REJET' => 'notif_rejet',
        'A_CORRIGER' => 'notif_correction',
        'AFFECTATION' => 'notif_affectation',
        'STATUT_DEV' => 'notif_statut_dev',
        'COMMENTAIRE' => 'notif_commentaire',
    ];

    public function get(string $cle, ?string $default = null): ?string
    {
        $param = Parametre::query()->find($cle);

        return $param?->valeur ?? $default;
    }

    public function notificationActive(string $typeNotification): bool
    {
        $cle = self::NOTIFICATIONS[$typeNotification] ?? null;

        if (! $cle) {
            return true;
        }

        return $this->get($cle, '1') === '1';
    }

    public function definir(string $cle, string $valeur, ?string $description = null): void
    {
        Parametre::query()->updateOrCreate(
            ['cle' => $cle],
            array_filter([
                'valeur' => $valeur,
                'description' => $description,
            ], fn ($v) => $v !== null),
        );
    }

    /** @return \Illuminate\Support\Collection<int, Parametre> */
    public function notificationsConfig(): \Illuminate\Support\Collection
    {
        $labels = [
            'notif_soumission' => 'Soumission d\'un cahier des charges (→ secrétariat)',
            'notif_transfert_di' => 'Transfert à la Direction Informatique',
            'notif_validation' => 'Validation par la DI (→ agent)',
            'notif_rejet' => 'Rejet par la DI (→ agent)',
            'notif_correction' => 'Demande de correction (→ agent)',
            'notif_affectation' => 'Affectation à un développeur',
            'notif_statut_dev' => 'Changement de statut développeur (→ DI)',
            'notif_commentaire' => 'Commentaires DI / développeur',
        ];

        return collect($labels)->map(function (string $label, string $cle) {
            $param = Parametre::query()->find($cle);

            return (object) [
                'cle' => $cle,
                'label' => $label,
                'valeur' => $param?->valeur ?? '1',
                'actif' => ($param?->valeur ?? '1') === '1',
            ];
        });
    }
}
