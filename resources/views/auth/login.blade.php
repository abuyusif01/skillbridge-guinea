@extends('layouts.app')

@section('title', 'Connexion — '.config('app.name'))
@section('meta_description', 'Connectez-vous à votre compte SkillBridge Guinée.')
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
        <p class="auth-heading">Bienvenue sur SkillBridge Guinée.</p>
        <p>Le réseau de confiance pour trouver un artisan qualifié près de chez vous, ou pour développer votre activité.</p>
        <ul class="auth-points">
          <li><span class="tick" aria-hidden="true">✓</span>Profils vérifiés par notre équipe</li>
          <li><span class="tick" aria-hidden="true">✓</span>Réservation gratuite, sans paiement en ligne</li>
          <li><span class="tick" aria-hidden="true">✓</span>Avis authentiques de clients locaux</li>
        </ul>
      </div>

      <p class="auth-quote">« J'ai trouvé un électricien fiable en cinq minutes, directement dans mon quartier. » — Aïssatou, Conakry</p>
    </aside>

    <div class="auth-panel">
      <div class="auth-card">
        <h1>Bon retour</h1>
        <p class="sub">Connectez-vous pour gérer vos réservations et vos avis.</p>

        @if ($errors->any())
          <div class="auth-alert" role="alert" style="margin-bottom: 1rem">
            {{ $errors->first() }}
          </div>
        @endif

        <form class="form-grid" method="POST" action="{{ route('login.store') }}">
          @csrf

          <div class="field">
            <label for="login">Téléphone ou e-mail</label>
            <input class="input" id="login" name="login" type="text" value="{{ old('login') }}" placeholder="+224 6XX XXX XXX" autocomplete="username" required autofocus>
            @error('login')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field">
            <label for="password">Mot de passe</label>
            <input class="input" id="password" name="password" type="password" placeholder="Votre mot de passe" autocomplete="current-password" required>
            @error('password')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="auth-meta">
            <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Se souvenir de moi</label>
            <a href="#" data-demo="Lien de réinitialisation envoyé (démonstration)">Mot de passe oublié ?</a>
          </div>

          <button class="btn btn-primary btn-lg btn-block" type="submit">Se connecter</button>

          <div class="auth-divider">ou</div>

          <a class="btn btn-outline btn-block" href="{{ route('register') }}">Créer un compte gratuit</a>
        </form>

        <p class="auth-footer">Vous êtes un professionnel ? <a href="{{ route('register') }}">Créez votre profil</a></p>
      </div>
    </div>
  </div>
@endsection
