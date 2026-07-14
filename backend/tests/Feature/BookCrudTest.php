<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        $user = User::factory()->create(['name' => 'Bill']);
        Sanctum::actingAs($user);

        return $user;
    }

    private function payload(): array
    {
        return [
            'titulo' => 'Clean Code',
            'numero_paginas' => 450,
            'indices' => [
                [
                    'titulo' => 'Capitulo 1',
                    'pagina' => 1,
                    'subindices' => [
                        ['titulo' => 'Introducao', 'pagina' => 2, 'subindices' => []],
                    ],
                ],
            ],
        ];
    }

    public function test_cria_livro_com_arvore_de_indices(): void
    {
        $this->actingUser();

        $response = $this->postJson('/api/books', $this->payload());

        $response->assertStatus(201)
            ->assertJsonPath('data.titulo', 'Clean Code')
            ->assertJsonPath('data.numero_paginas', 450)
            ->assertJsonPath('data.usuario_publicador.nome', 'Bill')
            ->assertJsonPath('data.indices.0.titulo', 'Capitulo 1')
            ->assertJsonPath('data.indices.0.subindices.0.titulo', 'Introducao');

        $this->assertDatabaseCount('book_indices', 2);
    }

    public function test_lista_livros(): void
    {
        $this->actingUser();
        $this->postJson('/api/books', $this->payload())->assertCreated();

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonPath('data.0.titulo', 'Clean Code');
    }

    public function test_filtra_por_titulo_ignorando_acento_e_caixa(): void
    {
        $this->actingUser();
        $this->postJson('/api/books', $this->payload())->assertCreated();

        // "clean" em caixa alta ainda casa.
        $this->getJson('/api/books?titulo=CLEAN')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/books?titulo=inexistente')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_filtra_por_titulo_do_indice_retorna_match_e_ascendentes(): void
    {
        $this->actingUser();
        $this->postJson('/api/books', $this->payload())->assertCreated();

        $response = $this->getJson('/api/books?titulo_do_indice=Introducao')->assertOk();

        // Livro retornado, com o ascendente (Capitulo 1) e o match (Introducao).
        $response->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.indices.0.titulo', 'Capitulo 1')
            ->assertJsonPath('data.0.indices.0.subindices.0.titulo', 'Introducao');
    }

    public function test_mostra_livro_por_id(): void
    {
        $this->actingUser();
        $id = $this->postJson('/api/books', $this->payload())->json('data.id');

        $this->getJson("/api/books/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id);
    }

    public function test_edita_livro_substituindo_arvore(): void
    {
        $this->actingUser();
        $id = $this->postJson('/api/books', $this->payload())->json('data.id');

        $novo = [
            'titulo' => 'Clean Architecture',
            'numero_paginas' => 500,
            'indices' => [
                ['titulo' => 'Parte I', 'pagina' => 10, 'subindices' => []],
            ],
        ];

        $this->putJson("/api/books/{$id}", $novo)
            ->assertOk()
            ->assertJsonPath('data.titulo', 'Clean Architecture')
            ->assertJsonPath('data.indices.0.titulo', 'Parte I')
            ->assertJsonCount(1, 'data.indices');

        // Arvore antiga (2 nos) foi substituida por 1 no.
        $this->assertDatabaseCount('book_indices', 1);
    }

    public function test_deleta_livro_remove_indices_em_cascata(): void
    {
        $this->actingUser();
        $id = $this->postJson('/api/books', $this->payload())->json('data.id');

        $this->deleteJson("/api/books/{$id}")->assertNoContent();

        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('book_indices', 0);
    }

    public function test_nao_dono_nao_pode_editar(): void
    {
        $dono = User::factory()->create();
        $book = Book::factory()->for($dono)->create();

        $this->actingUser(); // outro usuario
        $this->putJson("/api/books/{$book->id}", [
            'titulo' => 'Hack',
            'numero_paginas' => 1,
            'indices' => [],
        ])->assertStatus(403);
    }

    public function test_validacao_rejeita_indice_sem_titulo(): void
    {
        $this->actingUser();

        $this->postJson('/api/books', [
            'titulo' => 'Livro',
            'numero_paginas' => 10,
            'indices' => [
                ['pagina' => 1, 'subindices' => []],
            ],
        ])->assertStatus(422)
            ->assertJsonPath('error', 'Dados invalidos');
    }

    public function test_rotas_de_livros_exigem_autenticacao(): void
    {
        $this->getJson('/api/books')->assertStatus(401);
    }
}
