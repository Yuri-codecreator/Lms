<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipantsController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'perpage' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = (int)($validated['perpage'] ?? 20);

        $users = User::query()
            ->when($validated['search'] ?? null, function ($query, $term) {
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('fullname', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('participants.index', [
            'users' => $users,
        ]);
    }
}
