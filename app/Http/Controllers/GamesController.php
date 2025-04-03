<?php

namespace App\Http\Controllers;

use App\Models\Games;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GamesController extends Controller
{
    public function index()
    {
        return Inertia::render('Games');
    }

    public function getGames()
    {
        $games = Games::where('status', 'scheduled')
            ->where('status', 'in_progress')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($games);
    }
}
