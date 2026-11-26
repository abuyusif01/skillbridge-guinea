@extends('layouts.app')

@section('title', 'Modifier mon profil — '.config('app.name'))
@section('meta_description', 'Mettez à jour votre métier, votre présentation et votre disponibilité sur SkillBridge Guinée.')
@section('body_class', 'no-bottom-nav')

@section('content')
  <div class="wrap">
    <div class="dash-head">
      <div>
        <nav class="breadcrumb" aria-label="Fil d'Ariane" style="margin-bottom: 0.6rem">
          <a href="{{ route('dashboard') }}">Tableau de bord</a>
          <span class="sep" aria-hidden="true">/</span>
          <span>Modifier mon profil</span>
        </nav>
        <h1>Modifier mon profil</h1>
        <p class="sub">Les clients voient ces informations sur votre profil public.</p>
      </div>
      <div class="dash-head-actions">
        <a class="btn btn-outline btn-sm" href="{{ route('workers.show', $user) }}">Voir mon profil public</a>
      </div>
    </div>

    @if ($errors->any())
      <div class="auth-alert" role="alert" style="margin-bottom: 1rem">
        {{ $errors->first() }}
      </div>
    @endif

    <form class="card card-body form-grid" method="POST" action="{{ route('worker.profile.update') }}" aria-label="Modifier mon profil professionnel">
      @csrf
      @method('PATCH')

      <div class="form-row">
        <div class="field">
          <label for="trade_id">Métier</label>
          <select class="select" id="trade_id" name="trade_id" required>
            @foreach ($trades as $trade)
              <option value="{{ $trade->id }}" @selected((int) old('trade_id', $profile->trade_id) === $trade->id)>{{ $trade->localizedName() }}</option>
            @endforeach
          </select>
          @error('trade_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
          <label for="experience_years">Années d’expérience</label>
          <input class="input" id="experience_years" name="experience_years" type="number" min="0" max="60" value="{{ old('experience_years', $profile->experience_years) }}" required>
          @error('experience_years')<span class="field-error">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="form-row">
        <div class="field">
          <label for="service_area">Zone de déplacement</label>
          <input class="input" id="service_area" name="service_area" type="text" value="{{ old('service_area', $profile->service_area) }}" placeholder="Ex. Kaloum, Matoto" required>
          <span class="help">Les quartiers ou communes où vous acceptez des missions.</span>
          @error('service_area')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
          <label for="rate_min">Tarif de départ (GNF)</label>
          <input class="input" id="rate_min" name="rate_min" type="number" min="0" step="1000" value="{{ old('rate_min', $profile->rate_min) }}" placeholder="Ex. 150000">
          <span class="help">Facultatif. Laissez vide si vous préférez discuter du prix.</span>
          @error('rate_min')<span class="field-error">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="field">
        <label for="bio">Présentation de vos services</label>
        <textarea class="textarea" id="bio" name="bio" placeholder="Décrivez vos compétences, vos réalisations et vos horaires…" required>{{ old('bio', $profile->bio) }}</textarea>
        <span class="help">1000 caractères maximum.</span>
        @error('bio')<span class="field-error">{{ $message }}</span>@enderror
      </div>

      <div class="field">
        <span class="field-label">Disponibilité</span>
        <label class="switch">
          <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $profile->is_available))>
          <span class="switch-track" aria-hidden="true"></span>
          <span>Disponible pour de nouvelles demandes</span>
        </label>
        <span class="help">Désactivez cette option si vous ne pouvez pas prendre de nouveaux clients pour le moment.</span>
      </div>

      @if ($profile->verified_at)
        <p class="help">Votre identité est vérifiée par l’administration. Modifier votre profil ne retire pas ce badge.</p>
      @endif

      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>
        <a class="btn btn-ghost" href="{{ route('dashboard') }}">Annuler</a>
      </div>
    </form>
  </div>
@endsection
