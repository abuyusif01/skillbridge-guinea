@extends('layouts.app')

@section('title', 'Tableau de bord — '.config('app.name'))
@section('body_class', 'no-bottom-nav')

@section('content')
  <div class="wrap">
    <div class="dash-head">
      <div>
        <h1><span>Bonjour,</span> {{ $user->name }}</h1>
        <p class="sub">
          <span>{{ $user->role->label() }}</span>
          @if ($user->city)
            · {{ $user->city }}
          @endif
        </p>
      </div>
      <span class="badge badge-green">Compte actif</span>
    </div>

    @if ($user->isWorker() && $user->workerProfile)
      <div class="card card-body" style="margin-bottom: 1rem">
        <div class="mini-worker">
          <span class="avatar" aria-hidden="true">
            {{ strtoupper(mb_substr($user->name, 0, 1).mb_substr(strrchr($user->name, ' ') ?: '', 1, 1)) }}
          </span>
          <div>
            <strong>{{ $user->workerProfile->trade->localizedName() }}</strong>
            <span>{{ $user->workerProfile->service_area }}</span>
            <span class="rating-line">
              <span class="badge badge-green">Disponible</span>
              <span class="badge">{{ $user->workerProfile->experience_years }} <span>ans d'exp.</span></span>
            </span>
          </div>
        </div>
      </div>
    @endif

    <div class="stat-grid">
      @foreach ($stats as $stat)
        <div class="stat">
          <div class="num">{{ $stat['value'] }}</div>
          <div class="lbl">{{ $stat['label'] }}</div>
        </div>
      @endforeach
    </div>

    <div class="card card-body" style="margin-top: 1rem">
      <h2 style="font-size: 1rem; margin-bottom: 0.5rem">Votre activité</h2>
      <p class="muted" style="font-size: 0.9rem; margin-bottom: 1rem">
        Les parcours de recherche, réservation et avis arrivent dans les prochaines étapes.
      </p>
      <a class="btn btn-primary" href="{{ route('home') }}">Retour à l'accueil</a>
    </div>
  </div>
@endsection
