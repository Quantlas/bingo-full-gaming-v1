<?php

namespace App\Http\Controllers;

use App\Mail\BuyGameNotification;
use App\Models\Cards;
use App\Models\Payment;
use App\Models\User;
use App\Services\BingoCardGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class BingoCardsController extends Controller
{
    public function generateBingoNumbers($game_id, $number = null)
    {

        if (!$number) {
            $serialNumber = 'BINGO-' . $game_id . '-' . mt_rand(0001, 1000);
        } else {
            $serialNumber = 'BINGO-' . $game_id . '-' . $number;
        }

        $existingCard = Cards::where('serial_number', $serialNumber)->first();

        if ($existingCard) {
            return response()->json(['error' => 'El número de serie ya está en uso. Por favor, ingresa otro número de serie.'], 409);
        }

        $numbers = [];
        $ranges = [
            'B' => [1, 15],
            'I' => [16, 30],
            'N' => [31, 45],
            'G' => [46, 60],
            'O' => [61, 75]
        ];

        foreach ($ranges as $col => $range) {
            $colNumbers = range($range[0], $range[1]);
            shuffle($colNumbers);
            $numbers[$col] = array_slice($colNumbers, 0, 5);

            // Espacio libre en la columna N
            if ($col === 'N') {
                $numbers[$col][2] = null;
            }
        }

        $numbers['numbers'] = $numbers;
        $numbers['serial'] = $serialNumber;

        return $numbers;
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
            }

            $card = Cards::create([
                'numbers' => json_encode($request['carton']['numbers']),
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

            Mail::to($request->email)->send(new BuyGameNotification($request['carton']['numbers'], $request['carton']['serial'], $user));

            return response()->json(['message' => 'Su compra será procesada en breve'], 201);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
