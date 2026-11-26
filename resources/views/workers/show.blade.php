@extends('layouts.app')

@section('title', $worker->name.' — '.$worker->workerProfile->trade->localizedName().' | '.config('app.name'))
@section('meta_description', 'Profil de '.$worker->name.', '.$worker->workerProfile->trade->localizedName().' à '.$worker->city.'.')

@section('content')
  <div class="wrap">
    <div class="page-head">
      <nav class="breadcrumb" aria-label="Fil d'Ariane">
        <a href="{{ route('home') }}">Accueil</a>
        <span class="sep" aria-hidden="true">/</span>
        <a href="{{ route('search') }}">Recherche</a>
        <span class="sep" aria-hidden="true">/</span>
        <span>{{ $worker->name }}</span>
      </nav>
    </div>

    <div class="profile-layout">
      <div class="profile-main">
        <section class="card profile-hero">
          <div class="profile-id">
            <span class="avatar avatar-lg" aria-hidden="true">{{ $worker->initials() }}</span>
            <div>
              <h1>{{ $worker->name }}</h1>
              <p class="sub">{{ $worker->workerProfile->trade->localizedName() }} · {{ $worker->city ?: $worker->workerProfile->service_area }}</p>
              <div class="rating-line">
                @if ($reviewCount > 0)
                  <span class="stars" aria-hidden="true">★★★★★</span>
                  <strong>{{ number_format((float) $averageRating, 1, ',', ' ') }}</strong> ({{ $reviewCount }} {{ trans_choice('ui.review', $reviewCount) }})
                @else
                  <span class="badge">Nouveau</span>
                @endif
              </div>
            </div>
            @if ($worker->workerProfile->is_available)
              <span class="badge badge-green profile-badge">Disponible</span>
            @endif
          </div>

          <div class="profile-facts">
            <span class="fact">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              {{ $worker->workerProfile->service_area }}
            </span>
            @if ($worker->workerProfile->verified_at)
              <span class="fact">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4.5 6v5c0 4.5 3 8 7.5 10 4.5-2 7.5-5.5 7.5-10V6Z"/><path d="m9 12 2 2 4-4"/></svg>
                Identité vérifiée
              </span>
            @endif
            <span class="fact">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
              {{ $worker->workerProfile->experience_years }} ans d'expérience
            </span>
            <span class="fact">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
              {{ $completedJobs }} travaux réalisés
            </span>
          </div>

          <div class="chips" aria-label="Compétences">
            <span class="chip">{{ $worker->workerProfile->trade->localizedName() }}</span>
            <span class="chip">{{ $worker->workerProfile->service_area }}</span>
          </div>
        </section>

        <section class="card profile-section">
          <h2>À propos</h2>
          <p>{{ $worker->workerProfile->bio }}</p>
        </section>

        <section class="card profile-section">
          <h2>Avis des clients</h2>
          <div class="rating-summary">
            <div class="big-rating">
              <div class="value">{{ $reviewCount > 0 ? number_format((float) $averageRating, 1, ',', ' ') : '—' }}</div>
              <x-star-rating :rating="(int) round((float) $averageRating)" />
              <div class="count">{{ $reviewCount }} {{ trans_choice('ui.review', $reviewCount) }}</div>
            </div>
            <div class="rating-bars">
              @for ($star = 5; $star >= 1; $star--)
                @php
                  $count = (int) ($ratingCounts[$star] ?? 0);
                  $percent = $reviewCount > 0 ? round($count / $reviewCount * 100) : 0;
                @endphp
                <div class="rating-bar">
                  <span>{{ $star }}★</span>
                  <span class="bar"><span style="width: {{ $percent }}%"></span></span>
                  <span>{{ $count }}</span>
                </div>
              @endfor
            </div>
          </div>
        </section>

        <section aria-label="Commentaires">
          <div class="reviews-head">
            <h2>Derniers commentaires</h2>
            <span class="muted" style="font-size: 0.8rem">{{ $reviewCount }} {{ trans_choice('ui.verified_review', $reviewCount) }}</span>
          </div>

          @if ($reviews->isEmpty())
            <div class="card empty-state">
              <p>Aucun avis pour le moment.</p>
            </div>
          @else
            <div class="review-list">
              @foreach ($reviews as $review)
                <article class="review">
                  <div class="review-head">
                    <span class="avatar avatar-sm" aria-hidden="true">{{ $review->customer->initials() }}</span>
                    <strong>{{ $review->customer->name }}</strong>
                    <x-star-rating :rating="$review->rating" />
                    <span class="review-date">{{ $review->created_at->format('d/m/Y') }}</span>
                  </div>
                  @if ($review->comment)
                    <p>{{ $review->comment }}</p>
                  @endif
                </article>
              @endforeach
            </div>

            @if ($reviews->lastPage() > 1)
              <nav class="pagination" aria-label="Pagination">
                @for ($page = 1; $page <= $reviews->lastPage(); $page++)
                  <a class="page {{ $reviews->currentPage() === $page ? 'current' : '' }}" href="{{ $reviews->url($page) }}">{{ $page }}</a>
                @endfor
              </nav>
            @endif
          @endif
        </section>
      </div>

      <aside class="profile-aside">
        <div class="card booking-aside">
          <span class="badge badge-green">{{ $worker->workerProfile->is_available ? 'Disponible' : 'Indisponible' }}</span>
          <h2>Réserver {{ $worker->name }}</h2>
          @if ($worker->workerProfile->rate_min)
            <p class="price">À partir de <strong>{{ number_format($worker->workerProfile->rate_min, 0, ',', ' ') }} GNF</strong> / intervention</p>
          @endif

          <ul class="check-list">
            <li>Devis confirmé avant le travail</li>
            <li>Déplacement : {{ $worker->workerProfile->service_area }}</li>
            <li>{{ $worker->workerProfile->experience_years }} ans d'expérience</li>
            <li>Aucun paiement en ligne</li>
          </ul>

          @auth
            @if (auth()->user()->isCustomer())
              <a class="btn btn-primary btn-block" href="{{ route('bookings.create', $worker) }}">Réserver ce travailleur</a>
            @else
              <p class="help">Seuls les comptes clients peuvent envoyer une demande de réservation.</p>
            @endif
          @else
            <a class="btn btn-primary btn-block" href="{{ route('bookings.create', $worker) }}">Réserver ce travailleur</a>
            <a class="btn btn-ghost btn-block" href="{{ route('login') }}">Contacter avant de réserver</a>
          @endauth

          <p class="help">Vous réglez directement le professionnel après l'intervention. SkillBridge ne prélève aucun paiement.</p>
        </div>
      </aside>
    </div>
  </div>
@endsection
