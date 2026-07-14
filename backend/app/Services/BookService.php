<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Book;
use App\Models\BookIndex;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

class BookService
{
    public function __construct(private readonly IndexTreeService $tree) {}

    /**
     * Cria um livro e sua arvore de indices em uma unica transacao.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(int $userId, array $data): Book
    {
        return DB::transaction(function () use ($userId, $data) {
            $book = Book::create([
                'user_id' => $userId,
                'titulo' => $data['titulo'],
                'numero_paginas' => $data['numero_paginas'],
            ]);

            $this->tree->persistTree($book, $data['indices'] ?? []);

            return $this->loadTree($book);
        });
    }

    /**
     * Substitui titulo, numero_paginas e a arvore inteira (estrategia replace-all).
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Book $book, array $data): Book
    {
        return DB::transaction(function () use ($book, $data) {
            $book->update([
                'titulo' => $data['titulo'],
                'numero_paginas' => $data['numero_paginas'],
            ]);

            // Apaga a arvore atual (cascade remove os filhos) e recria do zero.
            $book->indices()->whereNull('parent_id')->get()
                ->each(fn (BookIndex $root) => $root->delete());
            // Seguranca extra: remove qualquer indice remanescente do livro.
            $book->indices()->delete();

            $this->tree->persistTree($book, $data['indices'] ?? []);

            $book->refresh();

            return $this->loadTree($book);
        });
    }

    public function delete(Book $book): void
    {
        // Indices caem por cascade da FK.
        $book->delete();
    }

    public function getById(int $id): Book
    {
        $book = Book::with('user')->find($id);

        if (! $book) {
            throw new ApiException(404, 'Livro nao encontrado');
        }

        return $this->loadTree($book);
    }

    /**
     * Lista livros com filtros opcionais.
     *
     * @param  array<string, mixed>  $filters  titulo, titulo_do_indice
     * @return EloquentCollection<int, Book>
     */
    public function list(array $filters = []): EloquentCollection
    {
        $query = Book::with('user')->orderByDesc('id');

        if (! empty($filters['titulo'])) {
            $q = $this->normalize($filters['titulo']);
            $query->whereRaw('f_unaccent(lower(titulo)) like f_unaccent(lower(?))', ["%{$q}%"]);
        }

        // Mapa livro -> ids de indices a manter (match + ascendentes) quando filtrado por indice.
        $prune = null;

        if (! empty($filters['titulo_do_indice'])) {
            [$bookIds, $prune] = $this->indicesMatching($filters['titulo_do_indice']);
            $query->whereIn('id', $bookIds ?: [0]);
        }

        $books = $query->get();

        foreach ($books as $book) {
            $indices = $book->indices()->get();
            $allowed = $prune[$book->id] ?? null;
            $book->indexTree = $this->tree->buildTree($indices, $allowed);
        }

        return $books;
    }

    /**
     * Carrega a arvore completa de indices do livro para serializacao.
     */
    private function loadTree(Book $book): Book
    {
        $book->loadMissing('user');
        $book->indexTree = $this->tree->buildTree($book->indices()->get());

        return $book;
    }

    /**
     * Localiza indices cujo titulo casa com a busca e resolve seus ascendentes.
     *
     * @return array{0: array<int,int>, 1: array<int, array<int,int>>}
     *   [ids de livros, mapa bookId => ids de indices a manter (match + ascendentes)]
     */
    private function indicesMatching(string $titulo): array
    {
        $q = $this->normalize($titulo);

        /** @var EloquentCollection<int, BookIndex> $matches */
        $matches = BookIndex::whereRaw('f_unaccent(lower(titulo)) like ?', ["%{$q}%"])->get();

        $keepByBook = [];

        foreach ($matches as $match) {
            $ids = [$match->id];
            // ancestors() (recursive CTE do pacote) traz toda a cadeia ate a raiz.
            foreach ($match->ancestors as $ancestor) {
                $ids[] = $ancestor->id;
            }

            foreach ($ids as $id) {
                $keepByBook[$match->book_id][$id] = $id;
            }
        }

        $keep = [];
        foreach ($keepByBook as $bookId => $ids) {
            $keep[$bookId] = array_values($ids);
        }

        return [array_keys($keep), $keep];
    }

    /**
     * Normaliza para busca: minusculo, sem acento e escapando curingas do LIKE.
     */
    private function normalize(string $value): string
    {
        $lowered = mb_strtolower(trim($value));
        // f_unaccent no SQL cuida da acentuacao; aqui escapamos curingas.
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $lowered);
    }
}
