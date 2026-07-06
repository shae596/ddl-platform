<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffectationDev extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'affectations_dev';

    protected $fillable = [
        'demande_id',
        'developpeur_id',
        'affecte_par_id',
        'actif',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function developpeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'developpeur_id');
    }

    public function affectePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affecte_par_id');
    }
}
