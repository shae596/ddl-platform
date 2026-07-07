<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Demande;
use App\Services\CahierDesChargesPdfService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait DownloadsCahierDesCharges
{
    protected function telechargerCahier(Demande $demande): StreamedResponse
    {
        $cahier = app(CahierDesChargesPdfService::class)->pieceJointeTelechargeable($demande);

        return Storage::disk('local')->download(
            $cahier->chemin_stockage,
            $cahier->nom_original,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
