<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SimilarityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    private function book(string $titulo): Book
    {
        return Book::factory()->create(['titulo' => $titulo]);
    }

    public function test_similaridade_fuzzy_encontra_titulo_proximo(): void
    {
        $this->book('Clean Code');
        $this->book('Clean Coder');
        $this->book('Receitas de Bolo');

        $titulos = collect(
            $this->getJson('/api/books/similares?titulo=Clean Code')->assertOk()->json('data')
        )->pluck('titulo');

        $this->assertTrue($titulos->contains('Clean Coder'));
        $this->assertFalse($titulos->contains('Receitas de Bolo'));
    }

    public function test_ignora_acento_e_caixa(): void
    {
        $this->book('Introducao a Fisica');

        $titulos = collect(
            $this->getJson('/api/books/similares?titulo='.urlencode('INTRODUÇÃO À FÍSICA'))
                ->assertOk()->json('data')
        )->pluck('titulo');

        $this->assertTrue($titulos->contains('Introducao a Fisica'));
    }

    public function test_camada_semantica_trata_plural_e_radical(): void
    {
        $this->book('Codigos Limpos');
        $this->book('Jardinagem Moderna');

        $titulos = collect(
            $this->getJson('/api/books/similares?titulo='.urlencode('Codigo Limpo'))
                ->assertOk()->json('data')
        )->pluck('titulo');

        $this->assertTrue($titulos->contains('Codigos Limpos'));
        $this->assertFalse($titulos->contains('Jardinagem Moderna'));
    }

    public function test_similares_de_um_livro_exclui_ele_mesmo(): void
    {
        $base = $this->book('Design Patterns');
        $this->book('Design Patterns Explained');

        $data = $this->getJson("/api/books/{$base->id}/similares")->assertOk()->json('data');
        $ids = collect($data)->pluck('id');

        $this->assertFalse($ids->contains($base->id));
        $this->assertTrue(collect($data)->pluck('titulo')->contains('Design Patterns Explained'));
    }

    public function test_similares_por_texto_exige_titulo(): void
    {
        $this->getJson('/api/books/similares')->assertStatus(422);
    }

    public function test_ordena_por_score_de_similaridade(): void
    {
        // Match exato deve rankear acima de match fraco.
        $this->book('Clean Code');
        $this->book('Clean Coder');

        $data = $this->getJson('/api/books/similares?titulo='.urlencode('Clean Code'))
            ->assertOk()->json('data');

        $this->assertNotEmpty($data);
        $this->assertSame('Clean Code', $data[0]['titulo']);
    }

    public function test_sem_candidatos_retorna_lista_vazia(): void
    {
        $this->book('Clean Code');

        $this->getJson('/api/books/similares?titulo=xyztermosemmatchnenhum')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_similares_de_livro_inexistente_retorna_404(): void
    {
        $this->getJson('/api/books/999999/similares')
            ->assertStatus(404)
            ->assertJsonPath('error', 'Livro nao encontrado');
    }
}
