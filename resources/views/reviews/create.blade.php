@extends('layouts.app')

@section('title', 'Laisser un avis — '.config('app.name'))
@section('meta_description', 'Partagez votre expérience et aidez les prochains clients.')

@section('content')
  <div class="review-wrap">
    <nav class="breadcrumb" aria-label="Fil d'Ariane" style="margin-bottom: 1rem">
      <a href="{{ route('home') }}">Accueil</a>
      <span class="sep" aria-hidden="true">/</span>
      <a href="{{ route('workers.show', $booking->worker) }}">{{ $booking->worker->name }}</a>
      <span class="sep" aria-hidden="true">/</span>
      <span>Laisser un avis</span>
    </nav>

    <form class="card review-card" method="POST" action="{{ route('reviews.store', $booking) }}">
      @csrf

      <div class="mini-worker">
        <span class="avatar" aria-hidden="true">{{ $booking->worker->initials() }}</span>
        <div>
          <strong>{{ $booking->worker->name }}</strong>
          <span>{{ $booking->worker->workerProfile->trade->localizedName() }} · {{ $booking->worker->city }}</span>
        </div>
        <span class="badge badge-green" style="margin-left: auto">Travail terminé</span>
      </div>

      <div>
        <h1 style="font-size: clamp(1.4rem, 4vw, 1.9rem); margin-bottom: 0.4rem">Comment s'est passé le travail ?</h1>
        <p class="muted" style="margin: 0; font-size: 0.9rem">Votre avis aide les prochains clients à choisir un professionnel de confiance.</p>
      </div>

      <hr class="divider" style="margin: 0">

      @if ($errors->any())
        <div class="auth-alert" role="alert">{{ $errors->first() }}</div>
      @endif

      <div>
        <p class="field-label" style="margin-bottom: 0.55rem">Votre note</p>
        <div class="star-input" role="radiogroup" aria-label="Votre note de 1 à 5 étoiles">
          @for ($rating = 5; $rating >= 1; $rating--)
            <input type="radio" name="rating" id="rate-{{ $rating }}" value="{{ $rating }}" @checked((int) old('rating', 5) === $rating)>
            <label for="rate-{{ $rating }}" aria-label="{{ $rating }} étoiles">★</label>
          @endfor
        </div>
        <p class="rating-caption">5 / 5 — Excellent</p>
        @error('rating')<span class="field-error">{{ $message }}</span>@enderror
      </div>

      <div class="field">
        <label for="comment">Commentaire (facultatif)</label>
        <textarea class="textarea" id="comment" name="comment" placeholder="Ex. Travail soigné, rapide et professionnel. Je recommande vivement !">{{ old('comment') }}</textarea>
        @error('comment')<span class="field-error">{{ $message }}</span>@enderror
        <span class="help">Restez courtois. Les commentaires insultants peuvent être supprimés par l'administration.</span>
      </div>

      <button class="btn btn-primary btn-lg btn-block" type="submit">Soumettre l'avis</button>
      <a class="btn btn-ghost btn-block" href="{{ route('dashboard') }}">Ignorer</a>

      <p class="help" style="text-align: center; margin: 0">Votre avis sera public sur le profil de {{ $booking->worker->name }}.</p>
    </form>
  </div>
@endsection
