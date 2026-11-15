<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', config('app.name'))</title>
  <meta name="description" content="@yield('meta_description', 'SkillBridge Guinea connects clients with trusted tradespeople across Guinea.')">
  <meta name="theme-color" content="#1d4ed8">
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='22' fill='%231d4ed8'/%3E%3Ctext x='50' y='70' font-size='58' text-anchor='middle' fill='white' font-family='Arial' font-weight='bold'%3ES%3C/text%3E%3C/svg%3E">
  <script src="{{ asset('assets/js/theme.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
  @stack('head')
</head>
<body class="@yield('body_class')">
  <a class="skip-link" href="#main">Aller au contenu</a>

  <header class="site-header">
    <div class="wrap header-inner">
      <a class="brand" href="{{ route('home') }}" aria-label="SkillBridge Guinée — Accueil">
        <span class="brand-mark" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-4a9 9 0 0 1 18 0v4"/><path d="M3 18h18"/><path d="M8 18v-2.5M16 18v-2.5M12 18v-2.5"/></svg>
        </span>
        <span class="brand-name">SkillBridge <em>Guinée</em></span>
      </a>

      <nav class="site-nav" id="site-nav" aria-label="Navigation principale">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}" @if (request()->routeIs('home')) aria-current="page" @endif>Accueil</a>

        @auth
          <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Tableau de bord</a>
          <form class="inline-form" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline btn-sm" type="submit">Se déconnecter</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Connexion</a>
          <a href="{{ route('register') }}" class="btn btn-primary btn-sm">S'inscrire</a>
        @endauth
      </nav>

      <div class="header-actions">
        <div class="lang-switch" role="group" aria-label="Language / Langue">
          <button type="button" data-lang="fr" class="active" aria-pressed="true">FR</button>
          <button type="button" data-lang="en" aria-pressed="false">EN</button>
        </div>
        <button class="theme-toggle" type="button" aria-label="Activer le mode sombre" aria-pressed="false" title="Activer le mode sombre">
          <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
          <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>
      </div>

      <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Ouvrir le menu">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </header>

  <main id="main">
    @if (session('status'))
      <div class="wrap" style="padding-top: 1rem">
        <div class="note">{{ session('status') }}</div>
      </div>
    @endif

    @yield('content')
  </main>

  <footer class="site-footer">
    <div class="wrap footer-inner">
      <div class="footer-brand">
        <a class="brand" href="{{ route('home') }}" aria-label="SkillBridge Guinée — Accueil">
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-4a9 9 0 0 1 18 0v4"/><path d="M3 18h18"/><path d="M8 18v-2.5M16 18v-2.5M12 18v-2.5"/></svg>
          </span>
          <span class="brand-name">SkillBridge <em>Guinée</em></span>
        </a>
        <p>La plateforme qui connecte les talents de Guinée avec les clients qui en ont besoin.</p>
      </div>
      <nav class="footer-links" aria-label="Liens de pied de page">
        <a href="{{ route('home') }}">Accueil</a>
        @auth
          <a href="{{ route('dashboard') }}">Tableau de bord</a>
        @else
          <a href="{{ route('login') }}">Connexion</a>
          <a href="{{ route('register') }}">S'inscrire</a>
        @endauth
      </nav>
    </div>
    <div class="wrap footer-legal">
      <span>© {{ date('Y') }} SkillBridge Guinée</span>
      <span>Prototype de démonstration · Données fictives</span>
    </div>
  </footer>

  <script src="{{ asset('assets/js/i18n-data.js') }}"></script>
  <script src="{{ asset('assets/js/i18n.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  @stack('scripts')
</body>
</html>
