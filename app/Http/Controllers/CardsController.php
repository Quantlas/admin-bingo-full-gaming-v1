<?php

namespace App\Http\Controllers;

use App\Models\Cards;
use App\Models\Payments;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CardsController extends Controller
{
    public function index()
    {
        $players = Cards::select('cards.*', 'users.name', 'games.name as game_name', 'payments.reference as payment_reference')
            ->join('users', 'cards.user_id', '=', 'users.id')
            ->join('games', 'cards.game_id', '=', 'games.id')
            ->join('payments', 'cards.id', '=', 'payments.payable_id')
            ->orderBy('id', 'desc')
            ->get();

        return Inertia::render('Players/Index', [
            'players' => $players,
        ]);
    }

    public function update(Request $request, $id)
    {
        $player = Cards::find($id);
        $player->status = $request->status;
        $player->save();

        $paymentStatus = match ($request->status) {
            'paid' => 'completed',
            'refunded' => 'refunded',
            'cancelled' => 'failed',
            default => 'pending',
        };

        $payment = Payments::where('payable_id', $id)->first();
        $payment->status = $paymentStatus;
        $payment->save();

        return redirect()->route('players.index');
    }
}
