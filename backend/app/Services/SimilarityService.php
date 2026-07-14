<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Identifica livros com titulos semanticamente equivalentes.
 *
 * Abordagem hibrida, escalavel para milhares de registros via indices GIN:
 *  - pg_trgm: similaridade fuzzy (acento/caixa/erros de digitacao), operador `%`.
 *  - Full-Text Search (dicionario portugues): aproxima significado tratando
 *    radical, plural e stopwords (ex.: "codigos limpos" ~ "codigo limpo").
 * O score final combina os dois sinais.
 */
class SimilarityService
{
    /** Peso relativo entre similaridade fuzzy (trgm) e semantica (fts). */
    private const W_TRGM = 0.6;

    private const W_FTS = 0.4;

    /**
     * Livros semelhantes a um livro existente (exclui ele mesmo).
     *
     * @return EloquentCollection<int, Book>
     */
    public function similarTo(Book $book, int $limit = 50): EloquentCollection
    {
        return $this->query($book->titulo, $limit, $book->id);
    }

    /**
     * Livros semelhantes a um titulo livre.
     *
     * @return EloquentCollection<int, Book>
     */
    public function similarToText(string $titulo, int $limit = 50): EloquentCollection
    {
        return $this->query($titulo, $limit);
    }

    /**
     * @return EloquentCollection<int, Book>
     */
    private function query(string $titulo, int $limit, ?int $excludeId = null): EloquentCollection
    {
        $q = trim($titulo);

        $trgm = 'similarity(f_unaccent(lower(titulo)), f_unaccent(lower(?)))';
        $fts = "ts_rank(titulo_tsv, websearch_to_tsquery('portuguese', ?))";
        $score = "({$trgm} * ".self::W_TRGM." + {$fts} * ".self::W_FTS.')';

        $query = Book::query()
            ->with('user')
            ->select('books.*')
            ->selectRaw("{$trgm} as sim_trgm", [$q])
            ->selectRaw("{$fts} as sim_fts", [$q])
            ->selectRaw("{$score} as sim_score", [$q, $q])
            // Candidato entra se casar por trigram (operador % usa o indice GIN)
            // OU se casar na busca textual (semantica).
            ->where(function ($w) use ($q) {
                $w->whereRaw('f_unaccent(lower(titulo)) % f_unaccent(lower(?))', [$q])
                    ->orWhereRaw("titulo_tsv @@ websearch_to_tsquery('portuguese', ?)", [$q]);
            })
            ->orderByRaw("{$score} desc", [$q, $q])
            ->limit($limit);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }
}
