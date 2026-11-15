<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'trades' => Trade::query()->active()->orderBy('name_fr')->get(),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = UserRole::from($data['role']);

        $user = DB::transaction(function () use ($data, $role): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'city' => $data['city'],
                'locale' => app()->getLocale(),
            ]);

            $user->forceFill(['role' => $role])->save();

            if ($role === UserRole::Worker) {
                $user->workerProfile()->create([
                    'trade_id' => $data['trade_id'],
                    'bio' => $data['bio'],
                    'experience_years' => $data['experience_years'],
                    'service_area' => $data['service_area'],
                    'is_available' => true,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
