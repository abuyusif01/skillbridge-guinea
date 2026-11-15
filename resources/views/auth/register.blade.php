@extends('layouts.app')

@section('title', 'Créer un compte — '.config('app.name'))
@section('meta_description', 'Inscrivez-vous gratuitement sur SkillBridge Guinée en tant que client ou professionnel.')
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
        <p class="auth-heading">Rejoignez la communauté.</p>
        <p>Que vous cherchiez un artisan ou que vous proposiez vos services, la création de compte est gratuite et prend moins de deux minutes.</p>
        <ul class="auth-points">
          <li><span class="tick" aria-hidden="true">✓</span>Gratuit, sans carte bancaire</li>
          <li><span class="tick" aria-hidden="true">✓</span>Aucune commission sur vos premiers travaux</li>
          <li><span class="tick" aria-hidden="true">✓</span>Notifications par téléphone, pas d'appli à installer</li>
        </ul>
      </div>

      <p class="auth-quote">« En une semaine, j'ai reçu mes trois premières demandes de clients. » — Ibrahima, plombier à Ratoma</p>
    </aside>

    <div class="auth-panel">
      <div class="auth-card auth-card--wide">
        <h1>Créer un compte</h1>
        <p class="sub">Choisissez votre profil pour commencer.</p>

        @if ($errors->any())
          <div class="auth-alert" role="alert" style="margin-bottom: 1rem">
            {{ $errors->first() }}
          </div>
        @endif

        <form class="form-grid register-form" method="POST" action="{{ route('register.store') }}">
          @csrf

          <div class="role-picker" role="radiogroup" aria-label="Type de compte">
            <label class="role-option">
              <input type="radio" name="role" id="role-client" value="customer" @checked(old('role', 'customer') === 'customer')>
              <span class="role-card">
                <strong>Je suis client</strong>
                <span>Je cherche un professionnel</span>
              </span>
            </label>
            <label class="role-option">
              <input type="radio" name="role" id="role-pro" value="worker" @checked(old('role') === 'worker')>
              <span class="role-card">
                <strong>Je suis professionnel</strong>
                <span>Je propose mes services</span>
              </span>
            </label>
          </div>
          @error('role')<span class="field-error">{{ $message }}</span>@enderror

          <div class="field">
            <label for="name">Nom complet</label>
            <input class="input" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Ex. Mamadou Diallo" autocomplete="name" required>
            @error('name')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="form-row">
            <div class="field">
              <label for="email">E-mail</label>
              <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="vous@example.com" autocomplete="email" required>
              @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
              <label for="phone">Téléphone</label>
              <input class="input" id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="+224 6XX XXX XXX" autocomplete="tel" required>
              @error('phone')<span class="field-error">{{ $message }}</span>@enderror
            </div>
          </div>

          <div class="field">
            <label for="city">Ville / commune</label>
            <select class="select" id="city" name="city" required>
              <option value="">Choisir…</option>
              @foreach (['Kaloum', 'Matoto', 'Ratoma', 'Matam', 'Kankan', 'Labé', 'Nzérékoré', 'Kindia'] as $city)
                <option value="{{ $city }}" @selected(old('city') === $city)>{{ $city }}</option>
              @endforeach
            </select>
            @error('city')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="form-row">
            <div class="field">
              <label for="password">Mot de passe</label>
              <input class="input" id="password" name="password" type="password" placeholder="Au moins 8 caractères" autocomplete="new-password" required>
              @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
              <label for="password_confirmation">Confirmer</label>
              <input class="input" id="password_confirmation" name="password_confirmation" type="password" placeholder="Répétez le mot de passe" autocomplete="new-password" required>
            </div>
          </div>

          <div class="worker-fields">
            <div class="field">
              <label for="trade_id">Métier</label>
              <select class="select" id="trade_id" name="trade_id">
                <option value="">Choisir…</option>
                @foreach ($trades as $trade)
                  <option value="{{ $trade->id }}" @selected((int) old('trade_id') === $trade->id)>{{ $trade->localizedName() }}</option>
                @endforeach
              </select>
              @error('trade_id')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-row">
              <div class="field">
                <label for="experience_years">Années d'expérience</label>
                <input class="input" id="experience_years" name="experience_years" type="number" min="0" max="60" value="{{ old('experience_years', 0) }}">
                @error('experience_years')<span class="field-error">{{ $message }}</span>@enderror
              </div>
              <div class="field">
                <label for="service_area">Zone de déplacement</label>
                <input class="input" id="service_area" name="service_area" type="text" value="{{ old('service_area') }}" placeholder="Ex. Kaloum, Matoto">
                @error('service_area')<span class="field-error">{{ $message }}</span>@enderror
              </div>
            </div>

            <div class="field">
              <label for="bio">Présentation de vos services</label>
              <textarea class="textarea" id="bio" name="bio" placeholder="Décrivez vos compétences, vos réalisations et vos horaires…">{{ old('bio') }}</textarea>
              @error('bio')<span class="field-error">{{ $message }}</span>@enderror
            </div>
          </div>

          <label class="check" style="align-items: flex-start">
            <input type="checkbox" name="terms" value="1" required style="margin-top: 0.2rem" @checked(old('terms'))>
            <span>J'accepte les <a href="#" data-demo="Conditions générales (démonstration)">conditions d'utilisation</a> et la politique de confidentialité de SkillBridge Guinée.</span>
          </label>
          @error('terms')<span class="field-error">{{ $message }}</span>@enderror

          <button class="btn btn-primary btn-lg btn-block" type="submit">Créer mon compte</button>
        </form>

        <p class="auth-footer">Vous avez déjà un compte ? <a href="{{ route('login') }}">Se connecter</a></p>
      </div>
    </div>
  </div>
@endsection
