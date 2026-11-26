@extends('layouts.app')

@section('title', 'Demande envoyée — '.config('app.name'))
@section('meta_description', 'Votre demande de réservation a été envoyée au professionnel.')

@section('content')
  @php
    $slots = [
      'morning' => __('ui.slots.morning'),
      'afternoon' => __('ui.slots.afternoon'),
      'evening' => __('ui.slots.evening'),
      'any' => __('ui.slots.any'),
    ];
  @endphp

  <div class="success-wrap">
    <div class="card success-card">
      <span class="success-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m4.5 12.5 5 5 10-11"/></svg>
      </span>

      <h1>Demande envoyée !</h1>
      <p class="lead">Votre demande a bien été transmise à {{ $booking->worker->name }}. Il dispose de 24 heures pour vous répondre.</p>

      <div class="success-summary">
        <div class="row"><span class="k">Professionnel</span><span class="v">{{ $booking->worker->name }} · {{ $booking->trade->localizedName() }}</span></div>
        <div class="row"><span class="k">Date souhaitée</span><span class="v">{{ $booking->preferred_date->format('d/m/Y') }}</span></div>
        <div class="row"><span class="k">Créneau</span><span class="v">{{ $slots[$booking->time_slot] ?? $booking->time_slot }}</span></div>
        <div class="row"><span class="k">Quartier</span><span class="v">{{ $booking->address }}</span></div>
        <div class="row"><span class="k">Contact</span><span class="v">{{ $booking->contact_phone }}</span></div>
        <div class="row"><span class="k">Statut</span><span class="v"><span class="badge badge-amber">{{ $booking->status->label() }}</span></span></div>
      </div>

      <div class="success-actions">
        <a class="btn btn-outline" href="{{ route('dashboard') }}">Voir mes réservations</a>
        <a class="btn btn-primary" href="{{ route('workers.show', $booking->worker) }}">Voir le profil</a>
      </div>
    </div>

    <div class="card card-body" style="margin-top: 1rem">
      <h2 style="font-size: 1rem; margin-bottom: 0.75rem">Que se passe-t-il ensuite ?</h2>
      <ol class="next-steps">
        <li>{{ $booking->worker->name }} reçoit votre demande et vous appelle au numéro indiqué pour confirmer le rendez-vous.</li>
        <li>Il vous annonce le prix avant de commencer. Vous pouvez accepter ou refuser.</li>
        <li>Une fois le travail terminé, vous recevez un lien pour laisser une note et un commentaire.</li>
      </ol>
      <p class="help" style="margin-top: 1rem">Pas de réponse après 24 h ? Vous pouvez contacter un autre professionnel sans frais.</p>
    </div>
  </div>
@endsection
