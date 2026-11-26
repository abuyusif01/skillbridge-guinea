@extends('layouts.app')

@section('title', 'Mot de passe oublié — '.config('app.name'))
@section('meta_description', 'Recevez un lien pour réinitialiser le mot de passe de votre compte SkillBridge Guinée.')
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
        <p class="auth-heading">Un mot de passe oublié ?</p>
        <p>Pas d’inquiétude. Indiquez l’adresse e-mail associée à votre compte et nous vous enverrons un lien sécurisé pour choisir un nouveau mot de passe.</p>
        <ul class="auth-points">
          <li><span class="tick" aria-hidden="true">✓</span>Lien valable une heure seulement</li>
          <li><span class="tick" aria-hidden="true">✓</span>Votre mot de passe actuel reste inchangé jusqu’à la réinitialisation</li>
          <li><span class="tick" aria-hidden="true">✓</span>Besoin d’aide ? Contactez l’administration</li>
        </ul>
      </div>

      <p class="auth-quote">« Le réseau de confiance des artisans et des clients en Guinée. »</p>
    </aside>

    <div class="auth-panel">
      <div class="auth-card">
        <h1>Mot de passe oublié</h1>
        <p class="sub">Nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>

        @if (session('status'))
          <div class="auth-alert auth-alert--success" role="status" style="margin-bottom: 1rem">
            {{ session('status') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="auth-alert" role="alert" style="margin-bottom: 1rem">
            {{ $errors->first() }}
          </div>
        @endif

        <form class="form-grid" method="POST" action="{{ route('password.email') }}">
          @csrf

          <div class="field">
            <label for="email">E-mail</label>
            <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="vous@example.com" autocomplete="email" required autofocus>
            @error('email')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <button class="btn btn-primary btn-lg btn-block" type="submit">Envoyer le lien de réinitialisation</button>

          <a class="btn btn-outline btn-block" href="{{ route('login') }}">Retour à la connexion</a>
        </form>

        <p class="auth-footer">Vous n’avez pas encore de compte ? <a href="{{ route('register') }}">Créer un compte gratuit</a></p>
      </div>
    </div>
  </div>
@endsection
