<?php

namespace App\Http\Controllers;

use App\Mail\BuyGameNotification;
use App\Models\Cards;
use App\Models\Games;
use App\Models\Payment;
use App\Models\User;
use App\Services\BingoCardGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Imagine\Imagick\Imagine;
use Imagine\Image\Point;
use Imagine\Image\Color;
use Imagine\Image\Palette\RGB;

class BingoCardsController extends Controller
{
    public function generateBingoNumbers($game_id, $number = null)
    {

        if (!$number) {
            $SimpleNumber = mt_rand(1, 1000); // Genera un número aleatorio
            $number = str_pad($SimpleNumber, 4, '0', STR_PAD_LEFT);
            $serialNumber = 'BINGO-' . $game_id . '-' . $number;
        } else {
            $number = str_pad($number, 4, '0', STR_PAD_LEFT);
            $serialNumber = 'BINGO-' . $game_id . '-' . $number;
        }

        $game = Games::find($game_id);

        $gameDate = Carbon::parse($game->date)->format('d-m-Y');

        $codeMark =  Str::uuid();

        $existingCard = Cards::where('serial_number', $serialNumber)->first();

        if ($existingCard) {
            return response()->json(['error' => 'El número de serie ya está en uso. Por favor, ingresa otro número de serie.'], 409);
        }

        $filename = "bgf-carton-" . $number . ".webp";

        $path = storage_path('app/bingo-cards/' . $filename);


        if (file_exists($path)) {
            $card = $this->aplicarMarcaDeAgua($path, $codeMark, $number, 'P', $gameDate);
        } else {
            return response()->json(['error' => 'No se pudo generar el cartón. Por favor, intenta nuevamente.'], 500);
        }

        $response['card'] = config('app.url') . $card;
        $response['filename'] = $filename;
        $response['serial'] = $serialNumber;
        $response['codeMark'] = $codeMark;
        $response['origin'] = $path;
        $response['number'] = $number;

        return $response;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'carton' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'referencia' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (Cards::where('serial_number', $request['carton']['serial'])->exists()) {
            return response()->json(['error' => 'El número de serie ya está en uso. Por favor, ingresa otro número de serie.'], 409);
        }
        try {

            $user = User::where('email', $request['email'])->first();

            if (!$user) {
                $password = Str::random(8);
                $passwordCrypt = bcrypt(Str::random(8));
                $user = User::create([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'phone' => $request['phone'],
                    'password' => $passwordCrypt
                ]);
                // Enviar email de bienvenida
            }

            $card = Cards::create([
                'card_path' => $request['carton']['filename'],
                'serial_number' => $request['carton']['serial'],
                'user_id' => $user->id,
                'game_id' => $request['carton']['game_id']
            ]);

            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $request['carton']['amount'],
                'payable_type' => 'carton',
                'payable_id' => $card->id,
                'payment_method' => 'pm',
                'reference' => $request['referencia'],
            ]);

            Mail::to($request->email)->send(new BuyGameNotification($request['carton']['serial'], $user, $request['carton']['filename']));

            return response()->json(['message' => 'Su compra será procesada en breve'], 201);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function aplicarMarcaDeAgua($inputPath, $text, $number, $type, $gameDate)
    {
        try {
            $imagine = new Imagine();
            $image = $imagine->open($inputPath);
            $size = $image->getSize();

            $palette = new RGB();
            $angle = 15; // Ángulo para los primeros dos textos

            $type = match ($type) {
                'P' => 'PENDIENTE',
                'A' => 'PAGADO',
                'C' => 'CANCELADO',
                'R' => 'RENBOLSO',
                default => 'PENDIENTE',
            };

            // Configuración de textos
            $linea1 = $type; // Texto 1 (rotado)
            $linea2 = $text;       // Texto 2 (rotado)
            $linea3 = $gameDate; // Texto 3 (fecha, horizontal)

            // Tamaños de fuente
            $fontSize1 = min(36, $size->getHeight() * 0.08);
            $fontSize2 = min(24, $size->getHeight() * 0.02);
            $fontSize3 = 18; // Tamaño fijo para fecha

            // Fuentes (misma fuente pero diferente orientación)
            $font1 = $imagine->font(public_path('webfonts/arial-bold.ttf'), $fontSize1, $palette->color('ff0000', 70));
            $font2 = $imagine->font(public_path('webfonts/arial-bold.ttf'), $fontSize2, $palette->color('666666', 50));
            $font3 = $imagine->font(public_path('webfonts/arial-bold.ttf'), $fontSize3, $palette->color('000000', 60));

            // Función para calcular posición segura (mejorada para ambos casos)
            $calculateSafePosition = function ($text, $font, $angle, $size, $offsetX = 0, $offsetY = 0) {
                $textBox = $font->box($text);

                if ($angle != 0) {
                    // Cálculo para texto rotado
                    $rad = deg2rad($angle);
                    $w = $textBox->getWidth();
                    $h = $textBox->getHeight();
                    $rotatedWidth = abs($w * cos($rad)) + abs($h * sin($rad));
                    $rotatedHeight = abs($w * sin($rad)) + abs($h * cos($rad));

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
                    // Cálculo para texto horizontal
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

            // Posiciones
            $pos1 = $calculateSafePosition($linea1, $font1, $angle, $size, 0, -50);
            $pos2 = $calculateSafePosition($linea2, $font2, $angle, $size, 40, 50);
            $pos3 = $calculateSafePosition($linea3, $font3, 0, $size, 0, 35); // Ángulo 0 = horizontal

            // Dibujar textos
            $image->draw()->text($linea1, $font1, new Point($pos1['x'], $pos1['y']), $angle);
            $image->draw()->text($linea2, $font2, new Point($pos2['x'], $pos2['y']), $angle);
            $image->draw()->text($linea3, $font3, new Point($pos3['x'], $pos3['y'])); // Sin rotación

            // Guardar en directorio público
            $publicDir = public_path('images/pendding-cards/');
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            $filename = 'bgf-carton-' . $number . '.webp';
            $outputPath = $publicDir . $filename;
            $image->save($outputPath, ['quality' => 100]);

            return 'images/pendding-cards/' . $filename;
        } catch (\Exception $e) {
            logger()->error("Error en aplicarMarcaDeAgua: " . $e->getMessage());
            throw $e;
        }
    }
}
