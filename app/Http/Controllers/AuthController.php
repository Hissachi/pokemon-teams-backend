<?php

namespace App\Http\Controllers;

use App\Models\{Pokemon, Team, User};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Hash, Http};

/**
 * @OA\Info(
 *     title="Pokemon Team API",
 *     version="1.0.0",
 *     description="API para gerenciamento de times de Pokémon",
 *     @OA\Contact(
 *         email="seu@email.com"
 *     )
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * @OA\Schema(
 *     schema="User",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="password", type="string", format="password")
 * )
 * @OA\Schema(
 *     schema="Team",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string", maxLength=50),
 *     @OA\Property(property="user_id", type="integer")
 * )
 * @OA\Schema(
 *     schema="Pokemon",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string", maxLength=20),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="ability", type="string"),
 *     @OA\Property(property="image", type="string", format="url"),
 *     @OA\Property(property="team_id", type="integer")
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Autentica um usuário",
     *     operationId="login",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Credenciais do usuário",
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@exemplo.com"),
     *             @OA\Property(property="password", type="string", format="password", example="senha123", minLength=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login bem-sucedido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login realizado com sucesso"),
     *             @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz"),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Credenciais inválidas")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Registra um novo usuário",
     *     operationId="register",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do usuário",
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Novo Usuário"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="novo@usuario.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="senhaSegura123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="senhaSegura123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/teams",
     *     tags={"Teams"},
     *     summary="Cria um novo time",
     *     operationId="createTeam",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do time",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=50, example="Meu Time Pokémon")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Time criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Time criado com sucesso"),
     *             @OA\Property(property="team", ref="#/components/schemas/Team")
     *         )
     *     )
     * )
     */
    public function createTeam(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50']);
        
        $team = auth()->user()->teams()->create($request->only('name'));
        
        return response()->json([
            'message' => 'Time criado com sucesso',
            'team' => $team
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/teams/{teamId}/pokemons",
     *     tags={"Pokemons"},
     *     summary="Adiciona um Pokémon ao time",
     *     operationId="addPokemon",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="teamId",
     *         in="path",
     *         required=true,
     *         description="ID do time",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do Pokémon",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=20, example="pikachu")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pokémon adicionado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pokémon adicionado com sucesso"),
     *             @OA\Property(property="pokemon", ref="#/components/schemas/Pokemon"),
     *             @OA\Property(property="remaining_slots", type="integer", example=4)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pokémon não encontrado na PokeAPI",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pokémon não encontrado na PokeAPI")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Limite de pokémons atingido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Limite de 5 pokémons por time atingido")
     *         )
     *     )
     * )
     */
    public function addPokemon(Request $request, $teamId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:20',
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

    /**
     * @OA\Get(
     *     path="/api/teams",
     *     tags={"Teams"},
     *     summary="Lista todos os times do usuário",
     *     operationId="listTeams",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de times",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="teams",
     *                 type="array",
     *                 @OA\Items(
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/Team"),
     *                         @OA\Schema(
     *                             @OA\Property(
     *                                 property="pokemons",
     *                                 type="array",
     *                                 @OA\Items(ref="#/components/schemas/Pokemon")
     *                             )
     *                         )
     *                     }
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function listTeams(): JsonResponse
    {
        return response()->json([
            'teams' => auth()->user()->teams()->with('pokemons')->get()
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/teams/{team}",
     *     tags={"Teams"},
     *     summary="Remove um time",
     *     operationId="deleteTeam",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="team",
     *         in="path",
     *         required=true,
     *         description="ID do time",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Time removido com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Time removido com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Time não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Time não encontrado")
     *         )
     *     )
     * )
     */
    public function deleteTeam(Team $team): JsonResponse
    {
        if(!$team) {
            return response()->json(['message' => 'Time não encontrado'], 404);
        }
        $team->delete();
        return response()->json(['message' => 'Time removido com sucesso']);
    }

    /**
     * @OA\Delete(
     *     path="/api/teams/{team}/pokemons/{pokemon}",
     *     tags={"Pokemons"},
     *     summary="Remove um Pokémon do time",
     *     operationId="deletePokemon",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="team",
     *         in="path",
     *         required=true,
     *         description="ID do time",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="pokemon",
     *         in="path",
     *         required=true,
     *         description="ID do Pokémon",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pokémon removido com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pokémon removido com sucesso")
     *         )
     *     )
     * )
     */
    public function deletePokemon(Team $team, Pokemon $pokemon): JsonResponse
    {
        $pokemon->delete();
        return response()->json(['message' => 'Pokémon removido com sucesso']);
    }

    /**
     * @OA\Get(
     *     path="/api/pokeapi",
     *     tags={"PokeAPI"},
     *     summary="Acessa a PokeAPI",
     *     operationId="getApi",
     *     @OA\Response(
     *         response=200,
     *         description="Resposta da PokeAPI",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Falha ao acessar a PokeAPI",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Falha ao acessar a PokeAPI")
     *         )
     *     )
     * )
     */
    public function getApi(): JsonResponse
    {
        $response = Http::withoutVerifying()->get('https://pokeapi.co/api/v2/');
        
        return $response->successful()
            ? response()->json($response->json())
            : response()->json(['error' => 'Falha ao acessar a PokeAPI'], 500);
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
}