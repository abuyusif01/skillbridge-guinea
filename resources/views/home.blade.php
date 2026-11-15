@extends('layouts.app')

@section('title', config('app.name').' — Trouvez un professionnel de confiance')
@section('meta_description', 'SkillBridge Guinea connects clients with electricians, plumbers, masons and other professionals in Guinea.')

@section('content')
  <section class="hero">
    <div class="wrap hero-grid">
      <div class="hero-copy">
        <span class="eyebrow"><span class="dot" aria-hidden="true"></span>La plateforme des métiers de Guinée</span>
        <h1>Trouvez le bon professionnel, <span class="text-brand">près de chez vous.</span></h1>
        <p class="lead">Électriciens, plombiers, maçons, mécaniciens… Comparez les profils et les avis, puis réservez en quelques secondes. Sans intermédiaire, sans paiement en ligne.</p>

        <div style="display: flex; gap: 0.6rem; flex-wrap: wrap">
          @auth
            <a class="btn btn-primary btn-lg" href="{{ route('dashboard') }}">Tableau de bord</a>
          @else
            <a class="btn btn-primary btn-lg" href="{{ route('register') }}">Créer mon compte</a>
            <a class="btn btn-outline btn-lg" href="{{ route('login') }}">Se connecter</a>
          @endauth
        </div>

        <dl class="hero-stats">
          <div>
            <dt>{{ $stats['workers'] }}</dt>
            <dd>professionnels</dd>
          </div>
          <div>
            <dt>{{ $stats['cities'] }}</dt>
            <dd>villes couvertes</dd>
          </div>
          <div>
            <dt>{{ $stats['rating'] === null ? '—' : number_format((float) $stats['rating'], 1, ',', ' ') }}</dt>
            <dd>note moyenne</dd>
          </div>
        </dl>
      </div>

      @if ($featured && $featured->workerProfile)
        @php
          $parts = preg_split('/\s+/', trim($featured->name));
          $initials = strtoupper(mb_substr($parts[0] ?? '', 0, 1).mb_substr($parts[count($parts) - 1] ?? '', 0, 1));
        @endphp
        <aside class="hero-art">
          <div class="card preview-card">
            <div class="preview-top">
              <span class="avatar avatar-lg" aria-hidden="true">{{ $initials }}</span>
              <div>
                <strong>{{ $featured->name }}</strong>
                <span><span>{{ $featured->workerProfile->trade->localizedName() }}</span> · {{ $featured->city }}</span>
                <span class="rating-line">
                  @if ($featuredReviewsCount > 0)
                    <x-star-rating :rating="(int) round((float) $featuredRating)" />
                    @if ($featuredRating !== null)
                      <strong>{{ $featuredRating }}</strong>
                    @endif
                    <span>{{ $featuredReviewsCount }} avis</span>
                  @else
                    <span class="badge">Nouveau</span>
                  @endif
                </span>
              </div>
            </div>
            <div class="preview-tags">
              <span class="badge badge-green">Disponible</span>
              <span class="badge">{{ $featured->workerProfile->experience_years }} <span>ans d'exp.</span></span>
            </div>
            <hr class="divider">
            <a class="btn btn-outline btn-block" href="{{ route('workers.show', $featured) }}">Voir son profil</a>
          </div>
          <span class="floating-tag floating-tag--one">✓ Avis vérifiés</span>
        </aside>
      @endif
    </div>
  </section>

  <section class="section">
    <div class="wrap">
      <div class="section-head">
        <div>
          <span class="eyebrow eyebrow--plain">Simple et rapide</span>
          <h2>Comment ça marche ?</h2>
        </div>
        <p class="section-sub">Trois étapes, aucune complication. Pas besoin d'être à l'aise avec la technologie.</p>
      </div>

      <div class="steps">
        <article class="step">
          <span class="step-num">1</span>
          <h3>Recherchez</h3>
          <p>Choisissez un métier et votre quartier. Les professionnels disponibles s'affichent avec leur note et leurs avis.</p>
        </article>
        <article class="step">
          <span class="step-num">2</span>
          <h3>Réservez</h3>
          <p>Envoyez une demande gratuite avec la description du travail et votre numéro de téléphone. Le pro accepte ou propose un autre horaire.</p>
        </article>
        <article class="step">
          <span class="step-num">3</span>
          <h3>Évaluez</h3>
          <p>Une fois le travail terminé, laissez une note et un commentaire pour aider les prochains clients à choisir.</p>
        </article>
      </div>
    </div>
  </section>

  @if ($trades->isNotEmpty())
    <section class="section" style="padding-top: 0">
      <div class="wrap">
        <div class="section-head">
          <div>
            <span class="eyebrow eyebrow--plain">Les plus recherchés</span>
            <h2>Métiers populaires</h2>
          </div>
        </div>

        <div class="trade-grid">
          @foreach ($trades as $trade)
            <a class="trade-card" href="{{ route('search', ['trade' => $trade->slug]) }}">
              <span class="trade-icon" aria-hidden="true">
                <strong style="font-size: 1.05rem">{{ mb_substr($trade->localizedName(), 0, 1) }}</strong>
              </span>
              <span class="trade-body">
                <strong>{{ $trade->localizedName() }}</strong>
                <span>{{ $trade->worker_profiles_count }} professionnels</span>
              </span>
            </a>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  <section class="section section--tight">
    <div class="wrap">
      <div class="cta-band">
        <div>
          <h2>Vous êtes un professionnel ?</h2>
          <p>Créez votre profil gratuitement et recevez des demandes de clients près de chez vous. Aucune commission sur vos premiers travaux.</p>
        </div>
        @auth
          <a class="btn btn-light btn-lg" href="{{ route('dashboard') }}">Tableau de bord</a>
        @else
          <a class="btn btn-light btn-lg" href="{{ route('register') }}">Créer mon profil</a>
        @endauth
      </div>
    </div>
  </section>
@endsection
