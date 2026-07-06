<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PieceJointe extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'pieces_jointes';

    protected $fillable = [
        'demande_id',
        'nom_fichier',
        'nom_original',
        'mime_type',
        'taille_octets',
        'chemin_stockage',
        'type',
        'uploade_par_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }
}
