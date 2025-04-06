<?php

namespace App\Http\Controllers;

use App\Models\Pokemon;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;


class AuthController extends Controller
{
    public function login(Request $request):JsonResponse
    {
        $request->validate([
            'email' =>['required','email', 'max:200'],
            'password'=>['required','string','max:8'],
]);

    $user=User::where('email',operator: $request->email)->first();   
    if(!$user || !Hash::check($request->password, $user->password))
    {
        return response()->json(['message' =>'The Provided credentials are incorrect'],	401);
    }
    $token=$user->createToken($user->name . 'Auth_Token')->plainTextToken;
    
    return response()->json(['message'=> 'Login Sucessful','token_type'=>'Bearer','token'=> $token],200);
}

public function register(Request $request):JsonResponse
{
    $request->validate([
        'name'=> ['required','string','max:20'],
        'email' =>['required','email', 'max:200','unique:users,email'],
        'password'=>['required','string','max:8'],
]);

$user = User::create([
    'name'=> $request->name,
    'email'=> $request->email, 
    'password'=> Hash::make($request->password),  
]);

if(!$user){
    return response()->json(['message'=> 'Somethin when wrong!!'],500);
}

$token=$user->createToken($user->name . 'Auth_Token')->plainTextToken;

return response()->json(['message'=> 'Login Sucessful','token_type'=>'Bearer','token'=> $token],200);
}

public function getApi()
{
    $response = Http::withoutVerifying()->get('https://pokeapi.co/api/v2/');

    if($response->successful())
    {
        $dados = $response-> json();
        return response()->json($dados);
    } 
    else{
        return response() -> json(['erro => Não foi possivel'],500);
    }
}

public function createTeams(Request $request):JsonResponse
{
    $request->validate([
        'name'=> ['required','string','max:20'],
        'type' =>['required','string', 'max:15'],
        'ability' => ['required','string','max:15'],
        'image'=>['required','string','max:120'],
]);

$pokemonCount = Pokemon::where('user_id', auth()->id())->count();
    if ($pokemonCount >= 5) {
        return response()->json([
            'message' => 'Você já tem o limite máximo de 5 pokémons no seu time'
        ], 422);
    }

    $pokemon = Pokemon::create([
        'user_id' => auth()->id(), 
        'name' => $request->name,
        'type' => $request->type, 
        'ability' => $request->ability,
        'image' => $request->image
    ]);

    if (!$pokemon) {
        return response()->json(['message' => 'Something went wrong!!'], 500);
    }

    return response()->json([
        'message' => 'Pokémon added to your team successfully',
        'remaining_slots' => 5 - ($pokemonCount + 1)
    ], 201);
}

public function listTeams()
{
    $team = Pokemon::where('user_id', auth()->id())
                 ->latest()
                 ->take(5)
                 ->get();

    return response()->json([
        'team' => $team,
        'count' => $team->count()
    ]);
}

public function deleteTeams($id)
{
    $pokemon = Pokemon::where('user_id', auth()->id())
                     ->findOrFail($id);
    
    $pokemon->delete();
    
    return response()->json(['message' => 'Pokémon released']);
}


}

