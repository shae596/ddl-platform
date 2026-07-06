<?php

namespace App\Services;

use App\Enums\TypePieceJointe;
use App\Models\Demande;
use App\Models\PieceJointe;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CahierDesChargesPdfService
{
    public function generer(Demande $demande): PieceJointe
    {
        $demande->load('piecesJointes');

        $this->supprimerAncienCahier($demande);

        $logoPath = public_path(config('ddl.logo'));
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('pdf.cahier-des-charges', [
            'demande' => $demande,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4');

        $nomFichier = Str::slug($demande->numero ?? 'ddl-brouillon').'-cahier-des-charges.pdf';
        $chemin = "demandes/{$demande->id}/{$nomFichier}";

        Storage::disk('local')->put($chemin, $pdf->output());

        return PieceJointe::create([
            'demande_id' => $demande->id,
            'nom_fichier' => $nomFichier,
            'nom_original' => $nomFichier,
            'mime_type' => 'application/pdf',
            'taille_octets' => Storage::disk('local')->size($chemin),
            'chemin_stockage' => $chemin,
            'type' => TypePieceJointe::CahierDesCharges->value,
            'uploade_par_id' => null,
            'created_at' => now(),
        ]);
    }

    private function supprimerAncienCahier(Demande $demande): void
    {
        $demande->piecesJointes()
            ->where('type', TypePieceJointe::CahierDesCharges->value)
            ->get()
            ->each(function (PieceJointe $piece) {
                Storage::disk('local')->delete($piece->chemin_stockage);
                $piece->delete();
            });
    }
}
