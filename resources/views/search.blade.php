@extends('layouts.app')

@section('title', 'Recherche — '.config('app.name'))
@section('meta_description', 'Trouvez des professionnels vérifiés près de chez vous en Guinée.')

@push('head')
  <link rel="stylesheet" href="{{ asset('assets/css/pagination.css') }}">
@endpush

@section('content')
  <div class="wrap">
    <div class="page-head">
      <nav class="breadcrumb" aria-label="Fil d'Ariane">
        <a href="{{ route('home') }}">Accueil</a>
        <span class="sep" aria-hidden="true">/</span>
        <span>Recherche</span>
      </nav>
      <h1>Rechercher un professionnel</h1>
      <p class="sub">{{ $workers->total() }} professionnels disponibles</p>
    </div>

    <form class="search-bar" method="GET" action="{{ route('search') }}">
      <label class="sr-only" for="search-trade">Métier</label>
      <select class="select" id="search-trade" name="trade">
        <option value="">Tous les métiers</option>
        @foreach ($trades as $trade)
          <option value="{{ $trade->slug }}" @selected(($filters['trade'] ?? '') === $trade->slug)>{{ $trade->localizedName() }}</option>
        @endforeach
      </select>
      <label class="sr-only" for="search-city">Localisation</label>
      <input class="input" id="search-city" name="city" type="text" value="{{ $filters['city'] ?? '' }}" placeholder="Ville ou quartier">
      <button class="btn btn-primary" type="submit">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        Rechercher
      </button>
    </form>

    <div class="results-layout">
      <details class="card filters" open>
        <summary>Filtres</summary>
        <form method="GET" action="{{ route('search') }}">
          <h2 class="sr-only">Filtres</h2>

          <div class="filter-group">
            <h3>Métier</h3>
            <label class="sr-only" for="filter-trade">Métier</label>
            <select class="select" id="filter-trade" name="trade">
              <option value="">Tous les métiers</option>
              @foreach ($trades as $trade)
                <option value="{{ $trade->slug }}" @selected(($filters['trade'] ?? '') === $trade->slug)>{{ $trade->localizedName() }}</option>
              @endforeach
            </select>
          </div>

          <div class="filter-group">
            <h3>Localisation</h3>
            <label class="sr-only" for="filter-city">Localisation</label>
            <input class="input" id="filter-city" name="city" type="text" value="{{ $filters['city'] ?? '' }}" placeholder="Ville ou quartier">
          </div>

          <div class="filter-group">
            <h3>Note minimale</h3>
            <label class="check"><input type="radio" name="min_rating" value="" @checked(($filters['min_rating'] ?? '') === '')> Toutes les notes</label>
            <label class="check"><input type="radio" name="min_rating" value="4" @checked(($filters['min_rating'] ?? '') == 4)> 4★ et plus</label>
            <label class="check"><input type="radio" name="min_rating" value="4.5" @checked(($filters['min_rating'] ?? '') == 4.5)> 4,5★ et plus</label>
          </div>

          <button class="btn btn-primary btn-block" type="submit">Appliquer les filtres</button>
          <a class="btn btn-ghost btn-block btn-sm" href="{{ route('search') }}">Réinitialiser</a>
        </form>
      </details>

      <section aria-label="Professionnels">
        @if ($workers->isEmpty())
          <div class="card empty-state">
            <h2>Aucun professionnel trouvé</h2>
            <p>Essayez un autre métier ou une autre localisation.</p>
          </div>
        @else
          <div class="results-head">
            <h2>{{ $workers->total() }} professionnels</h2>
            <span class="sort">Triés par : meilleure note</span>
          </div>

          <div class="result-list">
            @foreach ($workers as $worker)
              @php
                $profile = $worker->workerProfile;
                $rating = $worker->published_reviews_avg_rating;
                $count = $worker->published_reviews_count;
              @endphp
              <article class="result-card">
                <span class="avatar" aria-hidden="true">{{ $worker->initials() }}</span>
                <div>
                  <div class="result-title">
                    <h3>{{ $worker->name }}</h3>
                    @if ($profile->verified_at)
                      <span class="badge badge-blue">Vérifié</span>
                    @endif
                    <span class="badge badge-green">Disponible</span>
                  </div>
                  <p class="result-sub">{{ $profile->trade->localizedName() }} · {{ $worker->city ?: $profile->service_area }}</p>
                  <div class="result-meta">
                    @if ($count > 0)
                      <span class="rating-line">
                        <x-star-rating :rating="(int) round((float) $rating)" />
                        <strong>{{ number_format((float) $rating, 1, ',', ' ') }}</strong> ({{ $count }} {{ trans_choice('ui.review', $count) }})
                      </span>
                    @else
                      <span class="badge">Nouveau</span>
                    @endif
                    <span>{{ $profile->experience_years }} ans d'exp.</span>
                    @if ($profile->rate_min)
                      <span>Dès {{ number_format($profile->rate_min, 0, ',', ' ') }} GNF</span>
                    @endif
                  </div>
                </div>
                <div class="result-actions">
                  <a class="btn btn-primary btn-sm" href="{{ route('workers.show', $worker) }}">Voir le profil</a>
                  @auth
                    @if (auth()->user()->isCustomer())
                      <a class="link-quiet" href="{{ route('bookings.create', $worker) }}">Réserver</a>
                    @endif
                  @else
                    <a class="link-quiet" href="{{ route('bookings.create', $worker) }}">Réserver</a>
                  @endauth
                </div>
              </article>
            @endforeach
          </div>

          @if ($workers->hasPages())
            <nav class="pagination" aria-label="Pagination des résultats">
              @if ($workers->onFirstPage())
                <span class="btn btn-outline btn-sm is-disabled" aria-disabled="true">Précédent</span>
              @else
                <a class="btn btn-outline btn-sm" href="{{ $workers->previousPageUrl() }}" rel="prev">Précédent</a>
              @endif

              <span class="pagination-status">Page {{ $workers->currentPage() }} sur {{ $workers->lastPage() }}</span>

              @if ($workers->hasMorePages())
                <a class="btn btn-outline btn-sm" href="{{ $workers->nextPageUrl() }}" rel="next">Suivant</a>
              @else
                <span class="btn btn-outline btn-sm is-disabled" aria-disabled="true">Suivant</span>
              @endif
            </nav>
          @endif
        @endif
      </section>
    </div>
  </div>
@endsection
