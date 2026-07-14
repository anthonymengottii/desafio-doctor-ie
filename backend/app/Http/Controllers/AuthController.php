<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->auth->register($data['name'], $data['email'], $data['password']);

        return $this->tokenResponse($result, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->auth->login($data['email'], $data['password']);

        return $this->tokenResponse($result, 200);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => new UserResource($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Revoga apenas o token usado nesta requisicao.
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    /**
     * @param array{token: string, user: \App\Models\User} $result
     */
    private function tokenResponse(array $result, int $status): JsonResponse
    {
        return response()->json([
            'token' => $result['token'],
            'user' => new UserResource($result['user']),
        ], $status);
    }
}
