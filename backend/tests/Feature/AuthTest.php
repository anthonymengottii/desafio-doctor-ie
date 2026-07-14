<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_cria_usuario_e_retorna_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Bill',
            'email' => 'bill@example.com',
            'password' => 'segredo123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user' => ['id', 'nome', 'email']])
            ->assertJsonPath('user.nome', 'Bill');

        $this->assertDatabaseHas('users', ['email' => 'bill@example.com']);
    }

    public function test_registro_rejeita_email_duplicado(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $this->postJson('/api/auth/register', [
            'name' => 'Outro',
            'email' => 'dup@example.com',
            'password' => 'segredo123',
        ])->assertStatus(409)->assertJsonPath('error', 'E-mail ja cadastrado');
    }

    public function test_login_com_credenciais_validas_retorna_token(): void
    {
        User::factory()->create([
            'email' => 'ana@example.com',
            'password' => 'segredo123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'ana@example.com',
            'password' => 'segredo123',
        ])->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_login_com_senha_errada_falha(): void
    {
        User::factory()->create([
            'email' => 'ana@example.com',
            'password' => 'segredo123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'ana@example.com',
            'password' => 'errada',
        ])->assertStatus(401)->assertJsonPath('error', 'Credenciais invalidas');
    }

    public function test_rota_protegida_exige_token(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_rota_protegida_aceita_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_logout_revoga_o_token_usado(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $tokenId = explode('|', $token)[0];

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $tokenId]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertNoContent();

        // Token usado foi removido -> nao autentica mais requisicoes futuras.
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    public function test_registro_rejeita_senha_curta(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Bill',
            'email' => 'bill@example.com',
            'password' => 'curta',
        ])->assertStatus(422)
            ->assertJsonPath('error', 'Dados invalidos')
            ->assertJsonStructure(['errors' => ['password']]);
    }

    public function test_registro_rejeita_email_invalido(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Bill',
            'email' => 'nao-e-email',
            'password' => 'segredo123',
        ])->assertStatus(422)
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_registro_rejeita_nome_ausente(): void
    {
        $this->postJson('/api/auth/register', [
            'email' => 'bill@example.com',
            'password' => 'segredo123',
        ])->assertStatus(422)
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_login_com_email_inexistente_falha(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'ninguem@example.com',
            'password' => 'segredo123',
        ])->assertStatus(401)->assertJsonPath('error', 'Credenciais invalidas');
    }

    public function test_login_rejeita_email_ausente(): void
    {
        $this->postJson('/api/auth/login', [
            'password' => 'segredo123',
        ])->assertStatus(422)
            ->assertJsonStructure(['errors' => ['email']]);
    }
}
