<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookIndex;
use Illuminate\Support\Collection;

/**
 * Persiste e monta a arvore de indices (estrutura recursiva ilimitada).
 * Isola a logica recursiva usada por criacao/edicao/listagem de livros.
 */
class IndexTreeService
{
    /**
     * Grava recursivamente a arvore de indices de um livro.
     * Cada no do payload tem: titulo, pagina e subindices[].
     *
     * @param  array<int, array<string, mixed>>  $nodes
     */
    public function persistTree(Book $book, array $nodes, ?int $parentId = null): void
    {
        foreach (array_values($nodes) as $position => $node) {
            $index = BookIndex::create([
                'book_id' => $book->id,
                'parent_id' => $parentId,
                'titulo' => $node['titulo'],
                'pagina' => $node['pagina'],
                'position' => $position,
            ]);

            if (! empty($node['subindices'])) {
                $this->persistTree($book, $node['subindices'], $index->id);
            }
        }
    }

    /**
     * Monta a arvore aninhada (com chave "subindices") a partir de uma
     * lista plana de indices, em memoria e sem consultas extras.
     *
     * @param  Collection<int, BookIndex>  $indices
     * @param  array<int, int>|null  $allowedIds  se informado, mantem apenas estes nos (poda)
     * @return array<int, BookIndex>  indices raiz, com children populados em "childrenTree"
     */
    public function buildTree(Collection $indices, ?array $allowedIds = null): array
    {
        if ($allowedIds !== null) {
            $allowed = array_flip($allowedIds);
            $indices = $indices->filter(fn (BookIndex $i) => isset($allowed[$i->id]))->values();
        }

        // Agrupa por pai preservando a ordem por "position".
        $byParent = $indices
            ->sortBy('position')
            ->groupBy(fn (BookIndex $i) => $i->parent_id ?? 0);

        $attach = function (int $parentKey) use (&$attach, $byParent): array {
            return $byParent->get($parentKey, collect())
                ->map(function (BookIndex $node) use (&$attach) {
                    $node->setRelation('childrenTree', $attach($node->id));

                    return $node;
                })
                ->all();
        };

        return $attach(0);
    }
}
