<?php

//  namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\Pokemon;
// use App\Models\User;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Http;

// class PokemonController extends Controller
// {
    
// public function getApi()
// {
//     $response = Http::withoutVerifying()->get('https://pokeapi.co/api/v2/');

//     if($response->successful())
//     {
//         $dados = $response-> json();
//         return response()->json($dados);
//     } 
//     else{
//         return response() -> json(['erro => Não foi possivel'],500);
//     }
// }

// public function createTeams(Request $request):JsonResponse
// {
//     $request->validate([
//         'name'=> ['required','string','max:20'],
//         'type' =>['required','string', 'max:15'],
//         'ability' => ['required','string','max:15'],
//         'image'=>['required','string','max:120'],
// ]);

// $pokemonCount = Pokemon::where('user_id', auth()->id())->count();
//     if ($pokemonCount >= 5) {
//         return response()->json([
//             'message' => 'Você já tem o limite máximo de 5 pokémons no seu time'
//         ], 422);
//     }

//     $pokemon = Pokemon::create([
//         'user_id' => auth()->id(), 
//         'name' => $request->name,
//         'type' => $request->type, 
//         'ability' => $request->ability,
//         'image' => $request->image
//     ]);

//     if (!$pokemon) {
//         return response()->json(['message' => 'Something went wrong!!'], 500);
//     }

//     return response()->json([
//         'message' => 'Pokémon added to your team successfully',
//         'remaining_slots' => 5 - ($pokemonCount + 1)
//     ], 201);
// }

// public function listTeams()
// {
//     $team = Pokemon::where('user_id', auth()->id())
//                  ->latest()
//                  ->take(5)
//                  ->get();

//     return response()->json([
//         'team' => $team,
//         'count' => $team->count()
//     ]);
// }

// public function deleteTeams($id)
// {
//     $pokemon = Pokemon::where('user_id', auth()->id())
//                      ->findOrFail($id);
    
//     $pokemon->delete();
    
//     return response()->json(['message' => 'Pokémon released']);
// }
// }
