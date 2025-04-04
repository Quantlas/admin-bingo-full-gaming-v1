<?php

namespace App\Http\Controllers;

use App\Mail\AcceptPaymentNotification;
use App\Mail\CancelPaymentNotification;
use App\Models\Cards;
use App\Models\Payments;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

        $user = DB::connection('bingo-game')->table('users')->where('id', $payment->user_id)->first();

        $fecha_sorteo = DB::connection('bingo-game')->table('games')->where('id', $payment->game_id)->value('start_time');


        switch ($request->status) {
            case 'paid':
                $statusMail = 'Pagado';
                Mail::to($request->email)->send(new AcceptPaymentNotification(
                    json_decode($player->numbers, true),
                    $player->serial_number,
                    $statusMail,
                    $user,
                    $fecha_sorteo
                ));
                break;
            case 'refunded':
                $statusMail = 'Reembolsado';
                break;
            case 'cancelled':
                $statusMail = 'No Pagado';
                Mail::to($request->email)->send(new CancelPaymentNotification(
                    json_decode($player->numbers, true),
                    $player->serial_number,
                    $statusMail,
                    $user
                ));
                break;
            default:
                $statusMail = 'Pendiente';
        }



        return redirect()->route('players.index');
    }
}
