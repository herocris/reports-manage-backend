<?php

namespace App\Models;

use Database\Factories\ObservacionComentarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObservacionComentario extends Model
{
    /** @use HasFactory<ObservacionComentarioFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['observacion_id', 'usuario_id', 'contenido'];

    /** @return BelongsTo<Observacion, $this> */
    public function observacion(): BelongsTo
    {
        return $this->belongsTo(Observacion::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
