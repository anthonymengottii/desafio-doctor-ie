<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Serializa um indice e seus subindices recursivamente.
 * Espera a arvore montada via IndexTreeService (relacao "childrenTree").
 *
 * @mixin \App\Models\BookIndex
 */
class IndexResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $children = $this->whenLoaded('childrenTree', fn () => $this->childrenTree, []);

        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'pagina' => $this->pagina,
            'subindices' => IndexResource::collection($children),
        ];
    }
}
