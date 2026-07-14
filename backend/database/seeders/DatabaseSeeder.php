<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookIndex;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Popula um usuario demo, um livro de exemplo com arvore de indices e
     * milhares de livros para validar a performance da similaridade.
     */
    public function run(): void
    {
        $demo = User::factory()->create([
            'name' => 'Bill',
            'email' => 'bill@example.com',
            'password' => 'segredo123',
        ]);

        // Livro de exemplo do enunciado, com arvore de indices.
        $book = Book::create([
            'user_id' => $demo->id,
            'titulo' => 'Clean Code',
            'numero_paginas' => 450,
        ]);
        $cap = BookIndex::create([
            'book_id' => $book->id,
            'parent_id' => null,
            'titulo' => 'Capitulo 1',
            'pagina' => 1,
            'position' => 0,
        ]);
        BookIndex::create([
            'book_id' => $book->id,
            'parent_id' => $cap->id,
            'titulo' => 'Introducao',
            'pagina' => 2,
            'position' => 0,
        ]);

        // Massa de dados para teste de performance (~5000 livros).
        $this->seedManyBooks($demo->id, 5000);
    }

    private function seedManyBooks(int $userId, int $total): void
    {
        $adjetivos = ['Limpo', 'Moderno', 'Pratico', 'Avancado', 'Essencial', 'Completo'];
        $temas = ['Codigo', 'Arquitetura', 'Banco de Dados', 'Redes', 'Algoritmos', 'Design', 'Testes', 'Seguranca'];
        $now = now();
        $rows = [];

        for ($i = 0; $i < $total; $i++) {
            $titulo = $temas[array_rand($temas)].' '.$adjetivos[array_rand($adjetivos)].' '.($i + 1);
            $rows[] = [
                'user_id' => $userId,
                'titulo' => $titulo,
                'numero_paginas' => random_int(50, 900),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) === 500) {
                DB::table('books')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('books')->insert($rows);
        }
    }
}
