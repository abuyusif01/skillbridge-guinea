@extends('layouts.app')

@section('title', 'Réserver '.$worker->name.' — '.config('app.name'))
@section('meta_description', 'Envoyez une demande de réservation à '.$worker->name.'.')

@section('content')
  <div class="wrap">
    <div class="page-head">
      <nav class="breadcrumb" aria-label="Fil d'Ariane">
        <a href="{{ route('home') }}">Accueil</a>
        <span class="sep" aria-hidden="true">/</span>
        <a href="{{ route('workers.show', $worker) }}">{{ $worker->name }}</a>
        <span class="sep" aria-hidden="true">/</span>
        <span>Réservation</span>
      </nav>
      <h1>Réserver {{ $worker->name }}</h1>
      <p class="sub">Décrivez votre besoin. La demande est gratuite et sans engagement.</p>
    </div>

    <div class="booking-layout">
      <form class="card card-body form-grid" method="POST" action="{{ route('bookings.store', $worker) }}">
        @csrf

        @if ($errors->any())
          <div class="auth-alert" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="field">
          <label for="description">Description du travail</label>
          <textarea class="textarea" id="description" name="description" required placeholder="Ex. Réparer une prise électrique qui ne fonctionne plus dans le salon…">{{ old('description') }}</textarea>
          @error('description')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-row">
          <div class="field">
            <label for="preferred_date">Date souhaitée</label>
            <input class="input" id="preferred_date" name="preferred_date" type="date" value="{{ old('preferred_date', now()->addDay()->toDateString()) }}" min="{{ now()->toDateString() }}" required>
            @error('preferred_date')<span class="field-error">{{ $message }}</span>@enderror
          </div>
          <div class="field">
            <label for="time_slot">Créneau</label>
            <select class="select" id="time_slot" name="time_slot" required>
              @foreach (['morning' => 'Matin (8 h – 12 h)', 'afternoon' => 'Après-midi (12 h – 17 h)', 'evening' => 'Soir (17 h – 19 h)', 'any' => 'Peu importe'] as $value => $label)
                <option value="{{ $value }}" @selected(old('time_slot') === $value)>{{ $label }}</option>
              @endforeach
            </select>
            @error('time_slot')<span class="field-error">{{ $message }}</span>@enderror
          </div>
        </div>

        <div class="field">
          <label for="address">Quartier / adresse</label>
          <input class="input" id="address" name="address" type="text" value="{{ old('address', auth()->user()->city) }}" placeholder="Ex. Kaloum, rue KA-020, près de la mosquée" required>
          @error('address')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
          <label for="contact_phone">Votre numéro de contact</label>
          <input class="input" id="contact_phone" name="contact_phone" type="tel" value="{{ old('contact_phone', auth()->user()->phone) }}" placeholder="+224 6XX XXX XXX" autocomplete="tel" required>
          @error('contact_phone')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
          <span>Aucun paiement n'est demandé maintenant. Vous réglerez directement {{ $worker->name }} après le travail.</span>
        </div>

        <div class="form-actions">
          <button class="btn btn-primary btn-lg" type="submit">Envoyer la demande</button>
          <a class="btn btn-ghost" href="{{ route('workers.show', $worker) }}">Annuler</a>
        </div>
      </form>

      <aside class="summary-card card">
        <div class="mini-worker">
          <span class="avatar" aria-hidden="true">{{ $worker->initials() }}</span>
          <div>
            <strong>{{ $worker->name }}</strong>
            <span>{{ $worker->workerProfile->trade->localizedName() }} · {{ $worker->city }}</span>
          </div>
        </div>

        <hr class="divider" style="margin: 0">

        <ul class="summary-list">
          @if ($worker->workerProfile->rate_min)
            <li><span class="k">Tarif indicatif</span><span class="v">Dès {{ number_format($worker->workerProfile->rate_min, 0, ',', ' ') }} GNF</span></li>
          @endif
          <li><span class="k">Déplacement</span><span class="v">{{ $worker->workerProfile->service_area }}</span></li>
          <li><span class="k">Réponse</span><span class="v">Sous 24 h</span></li>
          <li><span class="k">Frais de réservation</span><span class="v">Gratuit</span></li>
        </ul>

        <hr class="divider" style="margin: 0">

        <div>
          <h2 style="font-size: 0.9rem; margin-bottom: 0.6rem">Ce qui se passe ensuite</h2>
          <ol class="next-steps">
            <li>{{ $worker->name }} reçoit votre demande et vous appelle pour confirmer.</li>
            <li>Il fixe le prix et le rendez-vous avec vous.</li>
            <li>Après le travail, vous laissez une note et un avis.</li>
          </ol>
        </div>

        <p class="help">En envoyant la demande, vous acceptez que vos coordonnées soient partagées avec ce professionnel uniquement.</p>
      </aside>
    </div>
  </div>
@endsection
