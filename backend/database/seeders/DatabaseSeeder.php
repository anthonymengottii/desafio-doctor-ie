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
                    ['titulo' => 'Capitulo 1 - Codigo Limpo', 'pagina' => 1, 'subindices' => [
                        ['titulo' => 'Introducao', 'pagina' => 2, 'subindices' => []],
                        ['titulo' => 'O que e codigo limpo', 'pagina' => 6, 'subindices' => [
                            ['titulo' => 'Escolas de pensamento', 'pagina' => 10, 'subindices' => []],
                            ['titulo' => 'Somos autores', 'pagina' => 13, 'subindices' => []],
                        ]],
                        ['titulo' => 'A regra do escoteiro', 'pagina' => 15, 'subindices' => []],
                    ]],
                    ['titulo' => 'Capitulo 2 - Nomes Significativos', 'pagina' => 17, 'subindices' => [
                        ['titulo' => 'Use nomes que revelam intencao', 'pagina' => 18, 'subindices' => []],
                        ['titulo' => 'Evite desinformacao', 'pagina' => 19, 'subindices' => []],
                        ['titulo' => 'Faca distincoes significativas', 'pagina' => 20, 'subindices' => []],
                        ['titulo' => 'Use nomes pronunciaveis', 'pagina' => 22, 'subindices' => []],
                    ]],
                    ['titulo' => 'Capitulo 3 - Funcoes', 'pagina' => 31, 'subindices' => [
                        ['titulo' => 'Pequenas', 'pagina' => 34, 'subindices' => []],
                        ['titulo' => 'Faca apenas uma coisa', 'pagina' => 35, 'subindices' => []],
                        ['titulo' => 'Argumentos de funcao', 'pagina' => 40, 'subindices' => [
                            ['titulo' => 'Formas monadicas', 'pagina' => 41, 'subindices' => []],
                            ['titulo' => 'Argumentos de flag', 'pagina' => 42, 'subindices' => []],
                        ]],
                    ]],
                    ['titulo' => 'Capitulo 4 - Comentarios', 'pagina' => 53, 'subindices' => [
                        ['titulo' => 'Comentarios bons', 'pagina' => 55, 'subindices' => []],
                        ['titulo' => 'Comentarios ruins', 'pagina' => 59, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'Clean Architecture',
                'numero_paginas' => 432,
                'indices' => [
                    ['titulo' => 'Parte I - Introducao', 'pagina' => 1, 'subindices' => [
                        ['titulo' => 'O que e design e arquitetura', 'pagina' => 3, 'subindices' => []],
                        ['titulo' => 'Um conto de dois valores', 'pagina' => 11, 'subindices' => []],
                    ]],
                    ['titulo' => 'Parte II - Paradigmas de Programacao', 'pagina' => 19, 'subindices' => [
                        ['titulo' => 'Programacao Estruturada', 'pagina' => 21, 'subindices' => []],
                        ['titulo' => 'Programacao Orientada a Objetos', 'pagina' => 31, 'subindices' => []],
                        ['titulo' => 'Programacao Funcional', 'pagina' => 41, 'subindices' => []],
                    ]],
                    ['titulo' => 'Parte III - Principios de Design', 'pagina' => 57, 'subindices' => [
                        ['titulo' => 'SRP - Responsabilidade Unica', 'pagina' => 61, 'subindices' => []],
                        ['titulo' => 'OCP - Aberto/Fechado', 'pagina' => 70, 'subindices' => []],
                        ['titulo' => 'LSP - Substituicao de Liskov', 'pagina' => 78, 'subindices' => []],
                        ['titulo' => 'ISP - Segregacao de Interface', 'pagina' => 84, 'subindices' => []],
                        ['titulo' => 'DIP - Inversao de Dependencia', 'pagina' => 87, 'subindices' => []],
                    ]],
                    ['titulo' => 'Parte IV - Componentes', 'pagina' => 93, 'subindices' => [
                        ['titulo' => 'Coesao de Componentes', 'pagina' => 103, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'Refatoracao',
                'numero_paginas' => 418,
                'indices' => [
                    ['titulo' => 'Primeiro Exemplo', 'pagina' => 1, 'subindices' => [
                        ['titulo' => 'O programa inicial', 'pagina' => 2, 'subindices' => []],
                        ['titulo' => 'Decompondo a funcao', 'pagina' => 9, 'subindices' => []],
                    ]],
                    ['titulo' => 'Principios da Refatoracao', 'pagina' => 45, 'subindices' => [
                        ['titulo' => 'Definindo refatoracao', 'pagina' => 46, 'subindices' => []],
                        ['titulo' => 'Por que refatorar', 'pagina' => 48, 'subindices' => []],
                        ['titulo' => 'Quando refatorar', 'pagina' => 52, 'subindices' => []],
                    ]],
                    ['titulo' => 'Maus Cheiros no Codigo', 'pagina' => 71, 'subindices' => [
                        ['titulo' => 'Codigo Duplicado', 'pagina' => 72, 'subindices' => []],
                        ['titulo' => 'Funcao Longa', 'pagina' => 73, 'subindices' => []],
                        ['titulo' => 'Inveja de Recurso', 'pagina' => 77, 'subindices' => []],
                    ]],
                    ['titulo' => 'Compondo Metodos', 'pagina' => 106, 'subindices' => []],
                ],
            ],
            [
                'titulo' => 'Padroes de Projeto',
                'numero_paginas' => 395,
                'indices' => [
                    ['titulo' => 'Padroes de Criacao', 'pagina' => 87, 'subindices' => [
                        ['titulo' => 'Abstract Factory', 'pagina' => 95, 'subindices' => []],
                        ['titulo' => 'Factory Method', 'pagina' => 112, 'subindices' => []],
                        ['titulo' => 'Builder', 'pagina' => 104, 'subindices' => []],
                        ['titulo' => 'Singleton', 'pagina' => 130, 'subindices' => []],
                    ]],
                    ['titulo' => 'Padroes Estruturais', 'pagina' => 151, 'subindices' => [
                        ['titulo' => 'Adapter', 'pagina' => 157, 'subindices' => []],
                        ['titulo' => 'Composite', 'pagina' => 170, 'subindices' => []],
                        ['titulo' => 'Decorator', 'pagina' => 181, 'subindices' => []],
                        ['titulo' => 'Facade', 'pagina' => 192, 'subindices' => []],
                    ]],
                    ['titulo' => 'Padroes Comportamentais', 'pagina' => 221, 'subindices' => [
                        ['titulo' => 'Observer', 'pagina' => 274, 'subindices' => []],
                        ['titulo' => 'Strategy', 'pagina' => 292, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'O Programador Pragmatico',
                'numero_paginas' => 352,
                'indices' => [
                    ['titulo' => 'Uma Filosofia Pragmatica', 'pagina' => 1, 'subindices' => [
                        ['titulo' => 'O gato comeu meu codigo-fonte', 'pagina' => 2, 'subindices' => []],
                        ['titulo' => 'Entropia de software', 'pagina' => 4, 'subindices' => []],
                        ['titulo' => 'Sopa de pedra e sapos cozidos', 'pagina' => 7, 'subindices' => []],
                    ]],
                    ['titulo' => 'Uma Abordagem Pragmatica', 'pagina' => 27, 'subindices' => [
                        ['titulo' => 'Os males da duplicacao', 'pagina' => 30, 'subindices' => []],
                        ['titulo' => 'Ortogonalidade', 'pagina' => 34, 'subindices' => []],
                    ]],
                    ['titulo' => 'Ferramentas Basicas', 'pagina' => 71, 'subindices' => [
                        ['titulo' => 'O poder do texto simples', 'pagina' => 73, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'Estruturas de Dados e Algoritmos',
                'numero_paginas' => 620,
                'indices' => [
                    ['titulo' => 'Analise de Complexidade', 'pagina' => 10, 'subindices' => [
                        ['titulo' => 'Notacao Big-O', 'pagina' => 12, 'subindices' => []],
                        ['titulo' => 'Analise amortizada', 'pagina' => 25, 'subindices' => []],
                    ]],
                    ['titulo' => 'Listas e Filas', 'pagina' => 60, 'subindices' => [
                        ['titulo' => 'Listas Encadeadas', 'pagina' => 62, 'subindices' => []],
                        ['titulo' => 'Pilhas e Filas', 'pagina' => 80, 'subindices' => []],
                    ]],
                    ['titulo' => 'Arvores', 'pagina' => 200, 'subindices' => [
                        ['titulo' => 'Arvores Binarias de Busca', 'pagina' => 210, 'subindices' => [
                            ['titulo' => 'Insercao e remocao', 'pagina' => 214, 'subindices' => []],
                            ['titulo' => 'Percursos', 'pagina' => 220, 'subindices' => []],
                        ]],
                        ['titulo' => 'Arvores AVL', 'pagina' => 230, 'subindices' => []],
                        ['titulo' => 'Arvores B', 'pagina' => 250, 'subindices' => []],
                    ]],
                    ['titulo' => 'Grafos', 'pagina' => 300, 'subindices' => [
                        ['titulo' => 'Busca em Largura (BFS)', 'pagina' => 310, 'subindices' => []],
                        ['titulo' => 'Busca em Profundidade (DFS)', 'pagina' => 318, 'subindices' => []],
                        ['titulo' => 'Menor Caminho (Dijkstra)', 'pagina' => 330, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'Banco de Dados Modernos',
                'numero_paginas' => 480,
                'indices' => [
                    ['titulo' => 'Modelagem Relacional', 'pagina' => 30, 'subindices' => [
                        ['titulo' => 'Entidades e Relacionamentos', 'pagina' => 33, 'subindices' => []],
                        ['titulo' => 'Normalizacao', 'pagina' => 45, 'subindices' => [
                            ['titulo' => 'Primeira Forma Normal', 'pagina' => 46, 'subindices' => []],
                            ['titulo' => 'Segunda Forma Normal', 'pagina' => 49, 'subindices' => []],
                            ['titulo' => 'Terceira Forma Normal', 'pagina' => 52, 'subindices' => []],
                        ]],
                    ]],
                    ['titulo' => 'Indices e Performance', 'pagina' => 120, 'subindices' => [
                        ['titulo' => 'Indices B-Tree', 'pagina' => 125, 'subindices' => []],
                        ['titulo' => 'Indices GIN e GiST', 'pagina' => 140, 'subindices' => []],
                        ['titulo' => 'Busca Textual', 'pagina' => 155, 'subindices' => [
                            ['titulo' => 'Full-Text Search', 'pagina' => 158, 'subindices' => []],
                            ['titulo' => 'Similaridade com pg_trgm', 'pagina' => 164, 'subindices' => []],
                        ]],
                    ]],
                    ['titulo' => 'Transacoes e Concorrencia', 'pagina' => 200, 'subindices' => [
                        ['titulo' => 'Niveis de Isolamento', 'pagina' => 205, 'subindices' => []],
                    ]],
                ],
            ],
            [
                'titulo' => 'Codigos Limpos e Praticos',
                'numero_paginas' => 300,
                'indices' => [
                    ['titulo' => 'Fundamentos', 'pagina' => 1, 'subindices' => [
                        ['titulo' => 'Legibilidade', 'pagina' => 3, 'subindices' => []],
                        ['titulo' => 'Simplicidade', 'pagina' => 8, 'subindices' => []],
                    ]],
                    ['titulo' => 'Boas Praticas', 'pagina' => 50, 'subindices' => [
                        ['titulo' => 'Testes Automatizados', 'pagina' => 55, 'subindices' => []],
                        ['titulo' => 'Revisao de Codigo', 'pagina' => 70, 'subindices' => []],
                    ]],
                    ['titulo' => 'Antipadroes', 'pagina' => 120, 'subindices' => []],
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
