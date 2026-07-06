<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueAction extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'historique_actions';

    protected $fillable = [
        'demande_id',
        'utilisateur_id',
        'ancien_statut',
        'nouveau_statut',
        'action',
        'commentaire',
        'metadonnees',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadonnees' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
