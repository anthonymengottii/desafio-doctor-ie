<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Garante as extensoes mesmo fora do Docker (ex.: banco de teste).
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');

        // unaccent() nao e IMMUTABLE por padrao, o que impede seu uso em indice
        // funcional. Este wrapper fixa o dicionario e marca a funcao como IMMUTABLE.
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION f_unaccent(text)
            RETURNS text
            LANGUAGE sql IMMUTABLE PARALLEL SAFE STRICT
            AS $$ SELECT public.unaccent('public.unaccent', $1) $$
        SQL);

        // Similaridade fuzzy (trigram) sobre titulo normalizado (sem acento, minusculo).
        DB::statement(
            'CREATE INDEX books_titulo_trgm_idx ON books '.
            'USING gin (f_unaccent(lower(titulo)) gin_trgm_ops)'
        );
        DB::statement(
            'CREATE INDEX book_indices_titulo_trgm_idx ON book_indices '.
            'USING gin (f_unaccent(lower(titulo)) gin_trgm_ops)'
        );

        // Coluna tsvector (dicionario portugues) para a camada semantica (radical/plural/stopwords).
        DB::statement(
            "ALTER TABLE books ADD COLUMN titulo_tsv tsvector ".
            "GENERATED ALWAYS AS (to_tsvector('portuguese', coalesce(titulo, ''))) STORED"
        );
        DB::statement('CREATE INDEX books_titulo_tsv_idx ON books USING gin (titulo_tsv)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS books_titulo_tsv_idx');
        DB::statement('ALTER TABLE books DROP COLUMN IF EXISTS titulo_tsv');
        DB::statement('DROP INDEX IF EXISTS book_indices_titulo_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS books_titulo_trgm_idx');
        DB::statement('DROP FUNCTION IF EXISTS f_unaccent(text)');
    }
};
