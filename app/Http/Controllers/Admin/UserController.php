<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->id === $request->user()->id && ! $data['is_active']) {
            return back()->withErrors([
                'is_active' => 'Vous ne pouvez pas suspendre votre propre compte.',
            ]);
        }

        $user->forceFill(['is_active' => (bool) $data['is_active']])->save();

        return back()->with(
            'status',
            $data['is_active'] ? 'Compte réactivé.' : 'Compte suspendu.'
        );
    }
}
