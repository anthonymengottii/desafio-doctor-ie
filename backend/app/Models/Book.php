<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titulo',
        'numero_paginas',
    ];

    protected $casts = [
        'numero_paginas' => 'integer',
    ];

    /**
     * Usuario publicador do livro.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Todos os indices do livro (qualquer nivel da arvore).
     *
     * @return HasMany<BookIndex, $this>
     */
    public function indices(): HasMany
    {
        return $this->hasMany(BookIndex::class);
    }

    /**
     * Apenas os indices raiz (sem pai), ordenados por posicao.
     *
     * @return HasMany<BookIndex, $this>
     */
    public function rootIndices(): HasMany
    {
        return $this->hasMany(BookIndex::class)
            ->whereNull('parent_id')
            ->orderBy('position');
    }
}
