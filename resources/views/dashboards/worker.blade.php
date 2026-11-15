@extends('layouts.app')

@section('title', 'Tableau de bord — '.config('app.name'))
@section('body_class', 'no-bottom-nav')

@section('content')
  @php
    $slots = [
      'morning' => 'Matin',
      'afternoon' => 'Après-midi',
      'evening' => 'Soir',
      'any' => 'Peu importe',
    ];
  @endphp

  <div class="wrap">
    <div class="dash-head">
      <div>
        <h1><span>Bonjour,</span> {{ $user->name }}</h1>
        <p class="sub"><span>Professionnel</span>@if ($user->city) · {{ $user->city }}@endif</p>
      </div>
      <div class="dash-head-actions">
        @if ($user->workerProfile)
          <span class="badge badge-green">{{ $user->workerProfile->is_available ? 'Disponible' : 'Indisponible' }}</span>
          <a class="btn btn-outline btn-sm" href="{{ route('workers.show', $user) }}">Voir mon profil</a>
        @endif
      </div>
    </div>

    @if ($user->workerProfile)
      <div class="card card-body" style="margin-bottom: 1rem">
        <div class="mini-worker">
          <span class="avatar" aria-hidden="true">{{ $user->initials() }}</span>
          <div>
            <strong>{{ $user->workerProfile->trade->localizedName() }}</strong>
            <span>{{ $user->workerProfile->service_area }}</span>
            <span class="rating-line">
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

    <section class="card card-body" aria-label="Demandes de réservation">
      <div class="tabs">
        <input type="radio" name="worker-tab" id="worker-pending" checked>
        <label for="worker-pending">En attente <span class="count">{{ $pending->count() }}</span></label>

        <input type="radio" name="worker-tab" id="worker-accepted">
        <label for="worker-accepted">Acceptées <span class="count">{{ $accepted->count() }}</span></label>

        <input type="radio" name="worker-tab" id="worker-completed">
        <label for="worker-completed">Terminées <span class="count">{{ $completed->count() }}</span></label>

        <div class="tab-panels">
          <div class="panel panel-pending">
            @if ($pending->isEmpty())
              <div class="empty-state"><p>Aucune demande en attente.</p></div>
            @else
              <div class="request-list">
                @foreach ($pending as $booking)
                  <article class="request">
                    <div class="request-head">
                      <div class="request-who">
                        <span class="avatar avatar-sm" aria-hidden="true">{{ $booking->customer->initials() }}</span>
                        <div>
                          <strong>{{ $booking->customer->name }}</strong>
                          <span>{{ $booking->customer->city }}</span>
                        </div>
                      </div>
                      <x-booking-status :status="$booking->status" />
                    </div>

                    <p class="request-job">{{ $booking->description }}</p>

                    <div class="request-meta">
                      <span>{{ $booking->preferred_date->format('d/m/Y') }} · {{ $slots[$booking->time_slot] ?? $booking->time_slot }}</span>
                      <span>{{ $booking->address }}</span>
                      <span>{{ $booking->contact_phone }}</span>
                    </div>

                    <div class="request-actions">
                      <form class="inline-form" method="POST" action="{{ route('worker.bookings.accept', $booking) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-success btn-sm" type="submit">Accepter</button>
                      </form>
                      <form class="inline-form" method="POST" action="{{ route('worker.bookings.decline', $booking) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-danger btn-sm" type="submit">Décliner</button>
                      </form>
                    </div>
                  </article>
                @endforeach
              </div>
            @endif
          </div>

          <div class="panel panel-accepted">
            @if ($accepted->isEmpty())
              <div class="empty-state"><p>Aucune réservation acceptée.</p></div>
            @else
              <div class="request-list">
                @foreach ($accepted as $booking)
                  <article class="request">
                    <div class="request-head">
                      <div class="request-who">
                        <span class="avatar avatar-sm" aria-hidden="true">{{ $booking->customer->initials() }}</span>
                        <div>
                          <strong>{{ $booking->customer->name }}</strong>
                          <span>{{ $booking->customer->city }}</span>
                        </div>
                      </div>
                      <x-booking-status :status="$booking->status" />
                    </div>

                    <p class="request-job">{{ $booking->description }}</p>

                    <div class="request-meta">
                      <span>{{ $booking->preferred_date->format('d/m/Y') }} · {{ $slots[$booking->time_slot] ?? $booking->time_slot }}</span>
                      <span>{{ $booking->address }}</span>
                      <span>{{ $booking->contact_phone }}</span>
                    </div>

                    <div class="request-actions">
                      <a class="btn btn-outline btn-sm" href="tel:{{ $booking->contact_phone }}">Appeler le client</a>
                      <form class="inline-form" method="POST" action="{{ route('worker.bookings.complete', $booking) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-success btn-sm" type="submit">Marquer comme terminé</button>
                      </form>
                    </div>
                  </article>
                @endforeach
              </div>
            @endif
          </div>

          <div class="panel panel-completed">
            @if ($completed->isEmpty())
              <div class="empty-state"><p>Aucun travail terminé.</p></div>
            @else
              <div class="request-list">
                @foreach ($completed as $booking)
                  <article class="request">
                    <div class="request-head">
                      <div class="request-who">
                        <span class="avatar avatar-sm" aria-hidden="true">{{ $booking->customer->initials() }}</span>
                        <div>
                          <strong>{{ $booking->customer->name }}</strong>
                          <span>{{ $booking->customer->city }}</span>
                        </div>
                      </div>
                      <x-booking-status :status="$booking->status" />
                    </div>

                    <p class="request-job">{{ $booking->description }}</p>

                    <div class="request-meta">
                      <span>Terminé le {{ $booking->completed_at?->format('d/m/Y') ?? $booking->preferred_date->format('d/m/Y') }}</span>
                      @if ($booking->review)
                        <span class="rating-line">
                          <x-star-rating :rating="$booking->review->rating" />
                          <strong>{{ $booking->review->rating }}/5</strong>
                        </span>
                      @else
                        <span class="badge badge-amber">Avis en attente</span>
                      @endif
                    </div>
                  </article>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
