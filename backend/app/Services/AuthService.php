<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Regras de autenticacao (registro/login) e emissao de token Sanctum.
 * Controller apenas valida e delega; toda a regra vive aqui.
 */
class AuthService
{
    /**
     * Cria um novo usuario e retorna token + dados publicos.
     *
     * @return array{token: string, user: User}
     */
    public function register(string $name, string $email, string $password): array
    {
        if (User::where('email', $email)->exists()) {
            throw new ApiException(409, 'E-mail ja cadastrado');
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password, // cast 'hashed' faz o hash automaticamente
        ]);

        return [
            'token' => $user->createToken('api')->plainTextToken,
            'user' => $user,
        ];
    }

    /**
     * Autentica por e-mail/senha e retorna token + dados publicos.
     *
     * @return array{token: string, user: User}
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new ApiException(401, 'Credenciais invalidas');
        }

        return [
            'token' => $user->createToken('api')->plainTextToken,
            'user' => $user,
        ];
    }
}
