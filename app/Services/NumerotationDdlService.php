<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NumerotationDdlService
{
    public function attribuerNumero(): string
    {
        $annee = (int) date('Y');

        return DB::transaction(function () use ($annee) {
            $row = DB::table('numerotation_ddl')
                ->where('annee', $annee)
                ->lockForUpdate()
                ->first();

            $maxExistant = $this->dernierNumeroUtilise($annee);
            $suivant = $row
                ? max((int) $row->dernier_numero + 1, $maxExistant + 1)
                : max(1, $maxExistant + 1);

            DB::table('numerotation_ddl')->updateOrInsert(
                ['annee' => $annee],
                ['dernier_numero' => $suivant],
            );

            return sprintf('DDL-%d-%03d', $annee, $suivant);
        });
    }

    /**
     * Plus haut numéro séquentiel déjà présent dans demandes pour l'année.
     * Protège contre une table numerotation_ddl désynchronisée (ex. après db:seed).
     */
    public function dernierNumeroUtilise(int $annee): int
    {
        $numeros = DB::table('demandes')
            ->whereNotNull('numero')
            ->where('numero', 'like', "DDL-{$annee}-%")
            ->pluck('numero');

        $max = 0;

        foreach ($numeros as $numero) {
            if (preg_match('/^DDL-'.preg_quote((string) $annee, '/').'-(\d+)$/', $numero, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max;
    }

    public function synchroniserCompteur(?int $annee = null): void
    {
        $annee ??= (int) date('Y');
        $max = $this->dernierNumeroUtilise($annee);

        DB::table('numerotation_ddl')->updateOrInsert(
            ['annee' => $annee],
            ['dernier_numero' => $max],
        );
    }
}
