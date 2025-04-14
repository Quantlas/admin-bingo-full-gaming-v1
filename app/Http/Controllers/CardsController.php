<?php

namespace App\Http\Controllers;

use App\Mail\AcceptPaymentNotification;
use App\Mail\CancelPaymentNotification;
use App\Models\Cards;
use App\Models\Payments;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Imagine\Imagick\Imagine;
use Imagine\Image\Point;
use Imagine\Image\Palette\RGB;

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

        $fecha_sorteo = DB::connection('bingo-game')->table('games')->where('id', $player->game_id)->value('start_time');

        switch ($request->status) {
            case 'paid':
                $statusMail = 'Pagado';
                $this->aplicarMarcaDeAgua($player->card_path, $player->serial_number, 'A', Carbon::parse($fecha_sorteo)->format('d-m-Y'));
                Mail::to($request->email)->send(new AcceptPaymentNotification(
                    $player->card_path,
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
                $this->aplicarMarcaDeAgua($player->card_path, $player->serial_number, 'C', Carbon::parse($fecha_sorteo)->format('d-m-Y'));
                Mail::to($request->email)->send(new CancelPaymentNotification(
                    $player->card_path,
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

    public function aplicarMarcaDeAgua($inputPath, $text, $type, $gameDate)
    {
        try {
            $filename = $inputPath;
            $inputPath = 'bingo-cards/' . $inputPath;

            $imagine = new Imagine();
            $image = $imagine->open($inputPath);
            $size = $image->getSize();

            $palette = new RGB();
            $angle = 15; // Ángulo para los primeros dos textos

            switch ($type) {
                case 'p':
                    $type = 'PENDIENTE';
                    $publicDir = public_path('images/pendding-cards/');
                    break;
                case 'A':
                    $type = 'PAGADO';
                    $publicDir = public_path('images/paid-cards/');
                    break;
                case 'C':
                    $type = 'RECHAZADO';
                    $publicDir = public_path('images/cancelled-cards/');
                    break;
                case 'R':
                    $type = 'REEMBOLSO';
                    $publicDir = public_path('images/refunded-cards/');
                    break;
                default:
                    $type = 'PENDIENTE';
                    $publicDir = public_path('images/pendding-cards/');
            }

            // Configuración de textos
            $linea1 = $type;
            $linea2 = $text;
            $linea3 = $gameDate;

            // Tamaños de fuente con mínimos seguros
            $fontSize1 = max(24, min(36, $size->getHeight() * 0.08)); // Mínimo 24px
            $fontSize2 = max(16, min(24, $size->getHeight() * 0.04)); // Mínimo 16px (cambié de 0.02 a 0.04)
            $fontSize3 = max(14, 18); // Mínimo 14px

            // Fuentes con validación de tamaño
            $fontPath = public_path('webfonts/arial-bold.ttf');
            $font1 = $imagine->font($fontPath, $fontSize1, $palette->color('ff0000', 70));
            $font2 = $imagine->font($fontPath, $fontSize2, $palette->color('666666', 70));
            $font3 = $imagine->font($fontPath, $fontSize3, $palette->color('000000', 60));

            // Función para calcular posición segura con protección contra tamaños inválidos
            $calculateSafePosition = function ($text, $font, $angle, $size, $offsetX = 0, $offsetY = 0) {
                $textBox = $font->box($text);

                // Validar dimensiones del texto
                if ($textBox->getWidth() <= 0 || $textBox->getHeight() <= 0) {
                    throw new \Exception("Dimensiones de texto inválidas");
                }

                if ($angle != 0) {
                    $rad = deg2rad($angle);
                    $w = $textBox->getWidth();
                    $h = $textBox->getHeight();

                    $rotatedWidth = abs($w * cos($rad)) + abs($h * sin($rad));
                    $rotatedHeight = abs($w * sin($rad)) + abs($h * cos($rad));

                    // Validar dimensiones rotadas
                    if ($rotatedWidth <= 0 || $rotatedHeight <= 0) {
                        throw new \Exception("Dimensiones rotadas inválidas");
                    }

                    $x = ($size->getWidth() - $rotatedWidth) / 2 + $offsetX;
                    $y = ($size->getHeight() - $rotatedHeight) / 2 + $offsetY;

                    $margin = 20;
                    $x = max($margin, min($x, $size->getWidth() - $rotatedWidth - $margin));
                    $y = max($margin, min($y, $size->getHeight() - $rotatedHeight - $margin));

                    return [
                        'x' => $x + ($rotatedWidth - $w) / 2,
                        'y' => $y + ($rotatedHeight - $h) / 2,
                        'width' => $w,
                        'height' => $h
                    ];
                } else {
                    $x = max(20, ($size->getWidth() - $textBox->getWidth()) / 2 + $offsetX);
                    $y = max(20, min($size->getHeight() - 20, $size->getHeight() * 0.9 + $offsetY));
                    return [
                        'x' => $x,
                        'y' => $y,
                        'width' => $textBox->getWidth(),
                        'height' => $textBox->getHeight()
                    ];
                }
            };

            // Calcular posiciones con manejo de errores
            $pos1 = $calculateSafePosition($linea1, $font1, $angle, $size, 0, -50);
            $pos2 = $calculateSafePosition($linea2, $font2, $angle, $size, 40, 50);
            $pos3 = $calculateSafePosition($linea3, $font3, 0, $size, 0, 35);

            // Dibujar textos
            $image->draw()->text($linea1, $font1, new Point($pos1['x'], $pos1['y']), $angle);
            $image->draw()->text($linea2, $font2, new Point($pos2['x'], $pos2['y']), $angle);
            $image->draw()->text($linea3, $font3, new Point($pos3['x'], $pos3['y']));

            // Guardar imagen
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            $outputPath = $publicDir . $filename;
            $image->save($outputPath, ['quality' => 100]);

            return $outputPath;
        } catch (\Exception $e) {
            logger()->error("Error en aplicarMarcaDeAgua: " . $e->getMessage() . " | Parámetros: " . json_encode([
                'inputPath' => $inputPath ?? null,
                'text' => $text ?? null,
                'type' => $type ?? null,
                'gameDate' => $gameDate ?? null
            ]));
            throw $e;
        }
    }
}
