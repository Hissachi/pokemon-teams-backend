<?php

namespace App\Http\Controllers;

use App\Models\{Pokemon, Team, User};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Hash, Http};

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:200',
            'password' => 'required|string|min:5',
        ]);

        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $token = $user->createToken($user->name.'_Token')->plainTextToken;
        
        return response()->json([
            'message' => 'Login realizado com sucesso',
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:200|unique:users',
            'password' => 'required|string|min:5|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken($user->name.'_Token')->plainTextToken;

        return response()->json([
            'message' => 'Usuário registrado com sucesso',
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    public function createTeam(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50']);
        
        $team = auth()->user()->teams()->create($request->only('name'));
        
        return response()->json([
            'message' => 'Time criado com sucesso',
            'team' => $team
        ], 201);
    }

    public function addPokemon(Request $request, Team $team): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:20',
            'type' => 'required|string|max:15',
            'ability' => 'required|string|max:15',
            'image' => 'required|string|max:120',
        ]);

        if ($team->pokemons()->count() >= 5) {
            return response()->json([
                'message' => 'Limite de 5 pokémons por time atingido'
            ], 422);
        }

        $pokemon = $team->pokemons()->create($request->all());

        return response()->json([
            'message' => 'Pokémon adicionado com sucesso',
            'pokemon' => $pokemon,
            'remaining_slots' => 5 - $team->pokemons()->count()
        ], 201);
    }

    public function listTeams(): JsonResponse
    {
        return response()->json([
            'teams' => auth()->user()->teams()->with('pokemons')->get()
        ]);
    }

    public function deleteTeam(Team $team): JsonResponse
    {
        $team->delete();
        return response()->json(['message' => 'Time removido com sucesso']);
    }

    public function deletePokemon(Team $team, Pokemon $pokemon): JsonResponse
    {
        $pokemon->delete();
        return response()->json(['message' => 'Pokémon removido com sucesso']);
    }

    public function getApi(): JsonResponse
    {
        $response = Http::withoutVerifying()->get('https://pokeapi.co/api/v2/');
        
        return $response->successful()
            ? response()->json($response->json())
            : response()->json(['error' => 'Falha ao acessar a PokeAPI'], 500);
    }
}