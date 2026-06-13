<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'FunShirt') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/premium.css') }}">
    @livewireStyles
</head>
<body>
    <div class="min-h-screen flex flex-col">
        <header>
            <div class="container">
                <nav>
                    <a href="{{ route('home') }}" class="logo">
                        Fun<span style="color: var(--secondary)">Shirt</span>
                    </a>

                    <div class="nav-links">
                        <a href="{{ route('home') }}" class="nav-link">Catálogo</a>
                        <a href="{{ route('cart.index') }}" class="nav-link" style="position: relative; display: flex; align-items: center; gap: 0.5rem;">
                            Carrinho
                            @if(session('cart') && count(session('cart')) > 0)
                                <span style="background: var(--primary); color: white; font-size: 0.7rem; font-weight: 800; padding: 0.1rem 0.4rem; border-radius: 99px; min-width: 18px; text-align: center;">
                                    {{ count(session('cart')) }}
                                </span>
                            @endif
                        </a>
                        
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-primary">Registar</a>
                        @else
                            @if(Auth::user()->user_type === 'A')
                                <a href="{{ route('admin.users.index') }}" class="nav-link">Utilizadores</a>
                                <a href="{{ route('admin.categories.index') }}" class="nav-link">Categorias</a>
                                <a href="{{ route('admin.tshirt-images.index') }}" class="nav-link">Estampas</a>
                                <a href="{{ route('admin.colors.index') }}" class="nav-link">Cores</a>
                                <a href="{{ route('admin.prices.edit') }}" class="nav-link">Preços</a>
                                <a href="{{ route('admin.statistics.index') }}" class="nav-link">Estatísticas</a>
                            @endif

                            @if(Auth::user()->user_type === 'C')
                                <a href="{{ route('orders.index') }}" class="nav-link">Encomendas</a>
                            @endif

                            <div style="display: flex; align-items: center; gap: 1rem;">
                                @if(Auth::user()->user_type === 'C')
                                    <a href="{{ route('dashboard') }}" class="nav-link" style="font-weight: 600;">{{ Auth::user()->name }}</a>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.9rem;">{{ Auth::user()->name }} ({{ Auth::user()->user_type === 'A' ? 'Admin' : 'Funcionário' }})</span>
                                @endif

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="nav-link" style="background: none; border: none; cursor: pointer; padding: 0; font-weight: 500;">Sair</button>
                                </form>
                            </div>
                        @endguest
                    </div>
                </nav>
            </div>
        </header>

        <main class="flex-grow">
            @if (isset($header))
                <div class="bg-white border-bottom py-6">
                    <div class="container">
                        {{ $header }}
                    </div>
                </div>
            @endif

            <div class="py-12">
                <div class="container">
                    @if(isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </div>
            </div>
        </main>

        <footer>
            <div class="container">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p>&copy; {{ date('Y') }} FunShirt. Todos os direitos reservados.</p>
                    <div style="display: flex; gap: 1.5rem;">
                        <a href="#" class="nav-link" style="color: #94a3b8">Termos</a>
                        <a href="#" class="nav-link" style="color: #94a3b8">Privacidade</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
