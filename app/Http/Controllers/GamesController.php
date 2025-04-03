<?php

namespace App\Http\Controllers;

use App\Models\Cards;
use App\Models\Games;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class GamesController extends Controller
{
    public function index()
    {
        $games = Games::orderBy('id', 'desc')->get();
        return Inertia::render('Games/Index', [
            'games' => $games,
        ]);
    }

    public function getgames()
    {
        $games = Games::orderBy('id', 'desc')->get();

        return response()->json($games);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'start_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        Games::create($request->all());

        $games = Games::orderBy('id', 'desc')->get();

        return response()->json(['message' => 'Game created successfully', 'games' => $games], 201);
    }

    public function changeStatus(Request $request, $id)
    {
        $game = Games::find($id);
        $game->status = $request->status;
        $game->save();

        $games = Games::orderBy('id', 'desc')->get();

        return response()->json(['message' => 'Game status updated successfully', 'games' => $games], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price_per_card' => 'required',
            'start_time' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $game = Games::find($id);
        $game->name = $request->name;
        $game->description = $request->description;
        $game->price_per_card = $request->price_per_card;
        $game->start_time = $request->start_time;
        $game->save();

        $games = Games::orderBy('id', 'desc')->get();

        return response()->json(['message' => 'Game updated successfully', 'games' => $games], 200);
    }

    public function delete($id)
    {

        $cards = Cards::where('game_id', $id)->get();

        if ($cards->count() > 0) {
            $game = Games::find($id);
            $game->status = 'canceled';
            $game->save();
            $games = Games::orderBy('id', 'desc')->get();
            return response()->json(['message' => 'Cannot delete game with cards', 'games' => $games], 200);
        }

        Games::find($id)->delete();
        $games = Games::orderBy('id', 'desc')->get();
        return response()->json(['message' => 'Game deleted successfully', 'games' => $games], 200);
    }
}
