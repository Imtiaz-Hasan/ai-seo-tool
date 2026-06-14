<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'pieces' => $user->contentPieces()->take(8)->get(),
            'stats' => [
                'pieces' => $user->contentPieces()->count(),
                'avg_score' => (int) round($user->contentPieces()->whereNotNull('last_score')->avg('last_score') ?? 0),
                'generations' => $user->generations()->count(),
                'tokens' => (int) $user->generations()->sum('output_tokens'),
            ],
            'recentGenerations' => $user->generations()->take(6)->get(),
        ]);
    }
}
