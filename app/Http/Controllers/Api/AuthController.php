<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $authService)
    {
        $result = $authService->login($request->only(['email', 'password']));

        if (!$result)
            return response()->json(['message' => 'Invalid credentials'], 401);

        return response()->json($result);
    }
}
