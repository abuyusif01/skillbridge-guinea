@extends('layouts.app')

@section('title', 'Nouveau mot de passe — '.config('app.name'))
@section('meta_description', 'Choisissez un nouveau mot de passe pour votre compte SkillBridge Guinée.')
@section('body_class', 'no-bottom-nav')

@section('content')
  <div class="auth-layout">
    <aside class="auth-aside">
      <a class="brand brand--light" href="{{ route('home') }}" aria-label="SkillBridge Guinée — Accueil">
        <span class="brand-mark" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-4a9 9 0 0 1 18 0v4"/><path d="M3 18h18"/><path d="M8 18v-2.5M16 18v-2.5M12 18v-2.5"/></svg>
        </span>
        <span class="brand-name">SkillBridge <em>Guinée</em></span>
      </a>

      <div>
        <p class="auth-heading">Choisissez un nouveau mot de passe.</p>
        <p>Utilisez au moins huit caractères. Un mot de passe long et unique protège mieux vos réservations et vos conversations.</p>
        <ul class="auth-points">
          <li><span class="tick" aria-hidden="true">✓</span>Huit caractères minimum</li>
          <li><span class="tick" aria-hidden="true">✓</span>Évitez un mot de passe déjà utilisé ailleurs</li>
          <li><span class="tick" aria-hidden="true">✓</span>Vous serez déconnecté des autres sessions</li>
        </ul>
      </div>

      <p class="auth-quote">« Le réseau de confiance des artisans et des clients en Guinée. »</p>
    </aside>

    <div class="auth-panel">
      <div class="auth-card">
        <h1>Nouveau mot de passe</h1>
        <p class="sub">Choisissez un mot de passe pour sécuriser votre compte.</p>

        @if ($errors->any())
          <div class="auth-alert" role="alert" style="margin-bottom: 1rem">
            {{ $errors->first() }}
          </div>
        @endif

        <form class="form-grid" method="POST" action="{{ route('password.store') }}">
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">

          <div class="field">
            <label for="email">E-mail</label>
            <input class="input" id="email" name="email" type="email" value="{{ old('email', $email) }}" placeholder="vous@example.com" autocomplete="username" required>
            @error('email')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field">
            <label for="password">Nouveau mot de passe</label>
            <input class="input" id="password" name="password" type="password" placeholder="Au moins 8 caractères" autocomplete="new-password" required autofocus>
            @error('password')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input class="input" id="password_confirmation" name="password_confirmation" type="password" placeholder="Répétez le mot de passe" autocomplete="new-password" required>
          </div>

          <button class="btn btn-primary btn-lg btn-block" type="submit">Réinitialiser mon mot de passe</button>

          <a class="btn btn-outline btn-block" href="{{ route('login') }}">Retour à la connexion</a>
        </form>
      </div>
    </div>
  </div>
@endsection
