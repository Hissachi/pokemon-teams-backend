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

    public function addPokemon(Request $request, $teamId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:20', // Apenas o nome é obrigatório
        ]);
    
        $team = Team::where('user_id', auth()->id())->findOrFail($teamId);
    
        if ($team->pokemons()->count() >= 5) {
            return response()->json([
                'message' => 'Limite de 5 pokémons por time atingido'
            ], 422);
        }
    
        $pokemonData = $this->fetchPokemonData($request->name);
        
        if (!$pokemonData) {
            return response()->json(['message' => 'Pokémon não encontrado na PokeAPI'], 404);
        }
    
        $pokemon = $team->pokemons()->create([
            'name' => $pokemonData['name'],
            'type' => $pokemonData['type'],
            'ability' => $pokemonData['ability'],
            'image' => $pokemonData['image']
        ]);
    
        return response()->json([
            'message' => 'Pokémon adicionado com sucesso',
            'pokemon' => $pokemon,
            'remaining_slots' => 5 - $team->pokemons()->count()
        ], 201);
    }
    
    private function fetchPokemonData($name)
    {
        $response = Http::withoutVerifying()
            ->get("https://pokeapi.co/api/v2/pokemon/{$name}");
        
        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();

        $type = isset($data['types'][0]['type']['name']) 
            ? ucfirst($data['types'][0]['type']['name']) 
            : 'Unknown';

        $ability = isset($data['abilities'][0]['ability']['name']) 
            ? ucfirst(str_replace('-', ' ', $data['abilities'][0]['ability']['name'])) 
            : 'Unknown';

        $image = $data['sprites']['other']['official-artwork']['front_default'] 
            ?? $data['sprites']['front_default'] 
            ?? null;

        return [
            'name' => ucfirst($name),
            'type' => $type,
            'ability' => $ability,
            'image' => $image
        ];
    }

    public function listTeams(): JsonResponse
    {
        return response()->json([
            'teams' => auth()->user()->teams()->with('pokemons')->get()
        ]);
    }

    public function deleteTeam(Team $team): JsonResponse
    {
        if(!$team) {
            return response()->json(['message' => 'Time não encontrado'], 404);
        }
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