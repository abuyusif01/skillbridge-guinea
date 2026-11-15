@props(['rating'])

<span class="stars" role="img" aria-label="{{ $rating }} {{ $rating === 1 ? 'étoile' : 'étoiles' }} sur 5">
    @for ($star = 1; $star <= 5; $star++)
        <span @class(['star-empty' => $star > $rating])>★</span>
    @endfor
</span>
