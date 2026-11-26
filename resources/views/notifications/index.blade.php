@extends('layouts.app')

@section('title', 'Notifications — '.config('app.name'))
@section('meta_description', 'Suivez les demandes de réservation, les réponses et les avis reçus sur SkillBridge Guinée.')
@section('body_class', 'no-bottom-nav')

@section('content')
  <div class="wrap">
    <div class="dash-head">
      <div>
        <nav class="breadcrumb" aria-label="Fil d'Ariane" style="margin-bottom: 0.6rem">
          <a href="{{ route('home') }}">Accueil</a>
          <span class="sep" aria-hidden="true">/</span>
          <span>Notifications</span>
        </nav>
        <h1>Notifications</h1>
        <p class="sub">
          @if ($unreadCount > 0)
            {{ $unreadCount }} notification{{ $unreadCount > 1 ? 's' : '' }} non lue{{ $unreadCount > 1 ? 's' : '' }}.
          @else
            Vous êtes à jour.
          @endif
        </p>
      </div>

      @if ($unreadCount > 0)
        <form class="inline-form" method="POST" action="{{ route('notifications.read-all') }}">
          @csrf
          <button class="btn btn-outline btn-sm" type="submit">Tout marquer comme lu</button>
        </form>
      @endif
    </div>

    <section class="card card-body" aria-label="Vos notifications">
      @if ($notifications->isEmpty())
        <div class="empty-state">
          <h2>Aucune notification</h2>
          <p>Les nouvelles demandes, réponses et avis apparaîtront ici.</p>
          <a class="btn btn-primary btn-sm" href="{{ route('dashboard') }}">Retour au tableau de bord</a>
        </div>
      @else
        <div class="notification-list">
          @foreach ($notifications as $notification)
            @php
              $type = $notification->data['type'] ?? '';
              $icon = match ($type) {
                  'booking_request' => '📩',
                  'review_received' => '⭐',
                  'booking_status' => match ($notification->data['status'] ?? '') {
                      'accepted' => '✅',
                      'declined' => '❌',
                      'completed' => '🏁',
                      default => '🔔',
                  },
                  default => '🔔',
              };
            @endphp

            <a class="notification-item {{ $notification->read_at ? '' : 'is-unread' }}"
               href="{{ route('notifications.open', $notification->id) }}">
              <span class="notification-icon" aria-hidden="true">{{ $icon }}</span>
              <span class="notification-body">
                <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                <span>{{ $notification->data['message'] ?? '' }}</span>
                <time datetime="{{ $notification->created_at?->toIso8601String() }}">
                  {{ $notification->created_at?->diffForHumans() }}
                </time>
              </span>
              @unless ($notification->read_at)
                <span class="notification-dot" aria-label="Non lue"></span>
              @endunless
            </a>
          @endforeach
        </div>
      @endif
    </section>
  </div>
@endsection
