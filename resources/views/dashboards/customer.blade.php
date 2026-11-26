@extends('layouts.app')

@section('title', 'Tableau de bord — '.config('app.name'))
@section('body_class', 'no-bottom-nav')

@section('content')
  @php
    $slots = [
      'morning' => __('ui.slots.morning'),
      'afternoon' => __('ui.slots.afternoon'),
      'evening' => __('ui.slots.evening'),
      'any' => __('ui.slots.any'),
    ];
  @endphp

  <div class="wrap">
    <div class="dash-head">
      <div>
        <h1><span>Bonjour,</span> {{ $user->name }}</h1>
        <p class="sub"><span>Client</span>@if ($user->city) · {{ $user->city }}@endif</p>
      </div>
      <a class="btn btn-primary btn-sm" href="{{ route('search') }}">Rechercher un professionnel</a>
    </div>

    <div class="stat-grid">
      @foreach ($stats as $stat)
        <div class="stat">
          <div class="num">{{ $stat['value'] }}</div>
          <div class="lbl">{{ $stat['label'] }}</div>
        </div>
      @endforeach
    </div>

    <section class="card card-body" aria-label="Vos réservations">
      <div class="results-head">
        <h2>Vos réservations</h2>
        <span class="sort">{{ $bookings->count() }} au total</span>
      </div>

      @if ($bookings->isEmpty())
        <div class="empty-state">
          <p>Vous n'avez pas encore de réservation.</p>
          <a class="btn btn-primary btn-sm" href="{{ route('search') }}">Trouver un professionnel</a>
        </div>
      @else
        <div class="request-list">
          @foreach ($bookings as $booking)
            <article class="request">
              <div class="request-head">
                <div class="request-who">
                  <span class="avatar avatar-sm" aria-hidden="true">{{ $booking->worker->initials() }}</span>
                  <div>
                    <strong>{{ $booking->worker->name }}</strong>
                    <span>{{ $booking->trade->localizedName() }}</span>
                  </div>
                </div>
                <x-booking-status :status="$booking->status" />
              </div>

              <p class="request-job">{{ $booking->description }}</p>

              <div class="request-meta">
                <span>{{ $booking->preferred_date->format('d/m/Y') }} · {{ $slots[$booking->time_slot] ?? $booking->time_slot }}</span>
                <span>{{ $booking->address }}</span>
              </div>

              @if ($booking->review)
                <div class="rating-line">
                  <x-star-rating :rating="$booking->review->rating" />
                  <strong>{{ $booking->review->rating }}/5</strong>
                  <span>Votre avis</span>
                </div>
              @endif

              <div class="request-actions">
                <a class="btn btn-outline btn-sm" href="{{ route('workers.show', $booking->worker) }}">Voir le profil</a>
                @if ($booking->canBeReviewed())
                  <a class="btn btn-primary btn-sm" href="{{ route('reviews.create', $booking) }}">Laisser un avis</a>
                @endif
                @if ($booking->canBeCancelled())
                  <form class="inline-form" method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="return confirm('Annuler cette réservation ?');">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-danger btn-sm" type="submit">Annuler la réservation</button>
                  </form>
                @endif
              </div>
            </article>
          @endforeach
        </div>
      @endif
    </section>
  </div>
@endsection
