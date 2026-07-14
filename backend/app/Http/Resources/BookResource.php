<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Serializa o livro no contrato do enunciado (chaves em portugues).
 * A arvore de indices deve estar em $this->indexTree (raizes com "childrenTree").
 *
 * @mixin \App\Models\Book
 */
class BookResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'usuario_publicador' => new UserResource($this->whenLoaded('user')),
            'numero_paginas' => $this->numero_paginas,
            'indices' => IndexResource::collection($this->indexTree ?? []),
        ];
    }
}
