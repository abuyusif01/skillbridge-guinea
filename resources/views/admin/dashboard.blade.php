@extends('layouts.app')

@section('title', 'Administration — '.config('app.name'))
@section('body_class', 'no-bottom-nav')

@section('content')
  <div class="wrap">
    <div class="admin-head">
      <div>
        <nav class="breadcrumb" aria-label="Fil d'Ariane" style="margin-bottom: 0.6rem">
          <a href="{{ route('home') }}">Accueil</a>
          <span class="sep" aria-hidden="true">/</span>
          <span>Administration</span>
        </nav>
        <h1>Administration</h1>
        <p class="sub">Vue d'ensemble de la plateforme et modération.</p>
      </div>
      <span class="badge badge-blue">Admin · {{ auth()->user()->name }}</span>
    </div>

    <div class="stat-grid">
      @foreach ($stats as $stat)
        <div class="stat">
          <div class="num">{{ $stat['value'] }}</div>
          <div class="lbl">{{ $stat['label'] }}</div>
        </div>
      @endforeach
    </div>

    <section class="card card-body admin-body" aria-label="Gestion de la plateforme">
      <div class="tabs">
        <input type="radio" name="admin-tab" id="adm-users" checked>
        <label for="adm-users">Utilisateurs</label>

        <input type="radio" name="admin-tab" id="adm-reviews">
        <label for="adm-reviews">Avis</label>

        <input type="radio" name="admin-tab" id="adm-bookings">
        <label for="adm-bookings">Réservations</label>

        <div class="tab-panels">
          <div class="panel panel-users">
            <div class="results-head">
              <h2>{{ $users->count() }} utilisateurs récents</h2>
              <span class="sort">{{ $pendingBookings }} réservations en attente</span>
            </div>

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th scope="col">Utilisateur</th>
                    <th scope="col">Rôle</th>
                    <th scope="col">Ville</th>
                    <th scope="col">Statut</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($users as $managedUser)
                    <tr>
                      <td>
                        <div class="cell-user">
                          <span class="avatar avatar-sm" aria-hidden="true">{{ $managedUser->initials() }}</span>
                          <div>
                            <strong>{{ $managedUser->name }}</strong><br>
                            <span class="muted" style="font-size: 0.74rem">{{ $managedUser->email }}</span>
                          </div>
                        </div>
                      </td>
                      <td>{{ $managedUser->role->label() }}</td>
                      <td>{{ $managedUser->city ?? '—' }}</td>
                      <td>
                        <span class="badge {{ $managedUser->is_active ? 'badge-green' : 'badge-red' }}">
                          {{ $managedUser->is_active ? 'Actif' : 'Suspendu' }}
                        </span>
                      </td>
                      <td>
                        @if ($managedUser->id === auth()->id())
                          <span class="muted" style="font-size: 0.74rem">Vous</span>
                        @else
                          <div class="table-actions">
                            <form class="inline-form" method="POST" action="{{ route('admin.users.status', $managedUser) }}">
                              @csrf
                              @method('PATCH')
                              <input type="hidden" name="is_active" value="{{ $managedUser->is_active ? 0 : 1 }}">
                              <button class="btn {{ $managedUser->is_active ? 'btn-danger' : 'btn-outline' }}" type="submit">
                                {{ $managedUser->is_active ? 'Suspendre' : 'Réactiver' }}
                              </button>
                            </form>
                          </div>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <div class="panel panel-reviews">
            <div class="results-head">
              <h2>Modération des avis</h2>
              <span class="sort">{{ $reviews->count() }} avis récents</span>
            </div>

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th scope="col">Auteur</th>
                    <th scope="col">Professionnel</th>
                    <th scope="col">Note</th>
                    <th scope="col">Commentaire</th>
                    <th scope="col">Statut</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($reviews as $review)
                    <tr>
                      <td>{{ $review->customer->name }}</td>
                      <td>{{ $review->worker->name }}</td>
                      <td><span><x-star-rating :rating="$review->rating" /> {{ $review->rating }}/5</span></td>
                      <td>{{ $review->comment ?? '—' }}</td>
                      <td><span class="badge {{ $review->status->value === 'published' ? 'badge-green' : 'badge-red' }}">{{ $review->status->label() }}</span></td>
                      <td>
                        <div class="table-actions">
                          <form class="inline-form" method="POST" action="{{ route('admin.reviews.destroy', $review) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit">Supprimer</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="6">Aucun avis.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          <div class="panel panel-bookings">
            <div class="results-head">
              <h2>Dernières réservations</h2>
              <span class="sort">{{ $bookings->count() }} récentes</span>
            </div>

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th scope="col">Client</th>
                    <th scope="col">Professionnel</th>
                    <th scope="col">Date</th>
                    <th scope="col">Statut</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($bookings as $booking)
                    <tr>
                      <td>{{ $booking->customer->name }}</td>
                      <td>{{ $booking->worker->name }}</td>
                      <td>{{ $booking->preferred_date->format('d/m/Y') }}</td>
                      <td><x-booking-status :status="$booking->status" /></td>
                    </tr>
                  @empty
                    <tr><td colspan="4">Aucune réservation.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
