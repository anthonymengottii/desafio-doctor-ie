<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use App\Services\IndexTreeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Popula um usuario demo, varios livros com arvores de indices e
     * milhares de livros para validar a performance da similaridade.
     */
    public function run(): void
    {
        $demo = User::factory()->create([
            'name' => 'Doctor IE',
            'email' => 'doctor-ie@example.com',
            'password' => 'segredo123',
        ]);

        $this->seedCuratedBooks($demo->id);

        // Massa de dados para teste de performance (~5000 livros).
        $this->seedManyBooks($demo->id, 5000);
    }

    /** Livros com arvore de indices recursiva (para explorar detalhes/filtros). */
    private function seedCuratedBooks(int $userId): void
    {
        $tree = app(IndexTreeService::class);

        foreach ($this->curatedBooks() as $data) {
            $book = Book::create([
                'user_id' => $userId,
                'titulo' => $data['titulo'],
                'numero_paginas' => $data['numero_paginas'],
            ]);

            $tree->persistTree($book, $data['indices']);
        }
    }

    /**
     * @return array<int, array{titulo: string, numero_paginas: int, indices: array<int, mixed>}>
     */
    private function curatedBooks(): array
    {
        return [
            [
                'titulo' => 'Clean Code',
                'numero_paginas' => 450,
                'indices' => [
                    ['titulo' => 'Capitulo 1', 'pagina' => 1, 'subindices' => [
                        ['titulo' => 'Introducao', 'pagina' => 2, 'subindices' => []],
                        ['titulo' => 'O que e codigo limpo', 'pagina' => 6, 'subindices' => []],
                    ]],
                    ['titulo' => 'Capitulo 2 - Nomes Significativos', 'pagina' => 17, 'subindices' => [
                        ['titulo' => 'Use nomes que revelam intencao', 'pagina' => 18, 'subindices' => []],
                        ['titulo' => 'Evite desinformacao', 'pagina' => 19, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'Clean Architecture',
                'numero_paginas' => 432,
                'indices' => [
                    ['titulo' => 'Parte I - Introducao', 'pagina' => 1, 'subindices' => [
                        ['titulo' => 'O que e design e arquitetura', 'pagina' => 3, 'subindices' => []],
                    ]],
                    ['titulo' => 'Parte III - Principios de Design', 'pagina' => 57, 'subindices' => [
                        ['titulo' => 'SRP - Principio da Responsabilidade Unica', 'pagina' => 61, 'subindices' => []],
                        ['titulo' => 'OCP - Principio Aberto/Fechado', 'pagina' => 70, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'Refatoracao',
                'numero_paginas' => 418,
                'indices' => [
                    ['titulo' => 'Principios da Refatoracao', 'pagina' => 45, 'subindices' => [
                        ['titulo' => 'Definindo refatoracao', 'pagina' => 46, 'subindices' => []],
                        ['titulo' => 'Por que refatorar', 'pagina' => 48, 'subindices' => []],
                    ]],
                    ['titulo' => 'Maus Cheiros no Codigo', 'pagina' => 71, 'subindices' => []],
                ],
            ],
            [
                'titulo' => 'Padroes de Projeto',
                'numero_paginas' => 395,
                'indices' => [
                    ['titulo' => 'Padroes de Criacao', 'pagina' => 87, 'subindices' => [
                        ['titulo' => 'Factory Method', 'pagina' => 112, 'subindices' => []],
                        ['titulo' => 'Singleton', 'pagina' => 130, 'subindices' => []],
                    ]],
                    ['titulo' => 'Padroes Estruturais', 'pagina' => 151, 'subindices' => [
                        ['titulo' => 'Adapter', 'pagina' => 157, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'O Programador Pragmatico',
                'numero_paginas' => 352,
                'indices' => [
                    ['titulo' => 'Uma Filosofia Pragmatica', 'pagina' => 1, 'subindices' => [
                        ['titulo' => 'O gato comeu meu codigo-fonte', 'pagina' => 2, 'subindices' => []],
                    ]],
                    ['titulo' => 'Uma Abordagem Pragmatica', 'pagina' => 27, 'subindices' => []],
                ],
            ],
            [
                'titulo' => 'Estruturas de Dados e Algoritmos',
                'numero_paginas' => 620,
                'indices' => [
                    ['titulo' => 'Analise de Complexidade', 'pagina' => 10, 'subindices' => [
                        ['titulo' => 'Notacao Big-O', 'pagina' => 12, 'subindices' => []],
                    ]],
                    ['titulo' => 'Arvores', 'pagina' => 200, 'subindices' => [
                        ['titulo' => 'Arvores Binarias de Busca', 'pagina' => 210, 'subindices' => []],
                        ['titulo' => 'Arvores AVL', 'pagina' => 230, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'Banco de Dados Modernos',
                'numero_paginas' => 480,
                'indices' => [
                    ['titulo' => 'Modelagem Relacional', 'pagina' => 30, 'subindices' => [
                        ['titulo' => 'Normalizacao', 'pagina' => 45, 'subindices' => []],
                    ]],
                    ['titulo' => 'Indices e Performance', 'pagina' => 120, 'subindices' => [
                        ['titulo' => 'Indices GIN e GiST', 'pagina' => 140, 'subindices' => []],
                        ['titulo' => 'Busca Textual', 'pagina' => 155, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'Codigos Limpos e Praticos',
                'numero_paginas' => 300,
                'indices' => [
                    ['titulo' => 'Fundamentos', 'pagina' => 1, 'subindices' => []],
                    ['titulo' => 'Boas Praticas', 'pagina' => 50, 'subindices' => []],
                ],
            ],
        ];
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
