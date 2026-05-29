<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login do usuário
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // Validação dos dados de entrada
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
                'device_name' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar usuário pelo email
            $user = User::where('email', $request->email)->first();

            // Verificar se o usuário existe e a senha está correta
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email ou senha incorretos'
                ], 401);
            }

            // Verificar se o usuário está ativo (se houver campo status)
            if (isset($user->status) && $user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Conta desativada. Entre em contato com o administrador.'
                ], 403);
            }

            // Gerar token de acesso
            $deviceName = $request->device_name ?? $request->userAgent() ?? 'Mobile App';
            $token = $user->createToken($deviceName)->plainTextToken;

            // Carregar relacionamentos se necessário
            // $user->load(['role', 'permissions']); // Ajuste conforme seus relacionamentos

            // Atualizar último login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role ?? 'user',
                        'avatar' => $user->avatar ?? null,
                        'created_at' => $user->created_at,
                        'last_login_at' => $user->last_login_at,
                        // Adicione outros campos necessários
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_at' => null // Sanctum tokens não expiram por padrão
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Logout do usuário (invalidar token atual)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Invalidar apenas o token atual
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar logout',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Logout de todos os dispositivos
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAll(Request $request)
    {
        try {
            // Invalidar todos os tokens do usuário
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado em todos os dispositivos com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar logout',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Obter dados do usuário autenticado
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            
            // Carregar relacionamentos se necessário
            $user->load(['roles', 'permissions']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role ?? 'user',
                        'avatar' => $user->avatar ?? null,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                        'last_login_at' => $user->last_login_at,
                        'email_verified_at' => $user->email_verified_at,
                        // Adicione outros campos necessários
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados do usuário',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Atualizar dados do perfil
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'current_password' => 'sometimes|required_with:password|string',
                'password' => 'sometimes|required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = [];

            if ($request->has('name')) {
                $data['name'] = $request->name;
            }

            if ($request->has('email')) {
                $data['email'] = $request->email;
            }

            // Atualizar senha se fornecida
            if ($request->has('password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Senha atual incorreta'
                    ], 422);
                }

                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role ?? 'user',
                        'updated_at' => $user->updated_at,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Verificar se o token é válido
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Token válido',
            'data' => [
                'user_id' => $request->user()->id,
                'token_name' => $request->user()->currentAccessToken()->name,
                'expires_at' => null
            ]
        ], 200);
    }
}
