@extends('layouts.app')

@section('content')
<div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary);">Gestão de Utilizadores</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Consulte, crie, bloqueie ou remova contas de utilizadores.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Novo Funcionário / Admin</a>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->has('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem;">
            {{ $errors->first('error') }}
        </div>
    @endif

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.users.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; background: var(--bg-main); padding: 1.25rem; border-radius: var(--radius); border: 1px solid var(--border);">
        <div>
            <label for="search" style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Pesquisa</label>
            <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Nome ou Email"
                style="width: 100%; padding: 0.625rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
        </div>

        <div>
            <label for="type" style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Tipo de Conta</label>
            <select id="type" name="type" style="width: 100%; padding: 0.625rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
                <option value="">Todos</option>
                <option value="A" {{ $type == 'A' ? 'selected' : '' }}>Administrador</option>
                <option value="F" {{ $type == 'F' ? 'selected' : '' }}>Funcionário</option>
                <option value="C" {{ $type == 'C' ? 'selected' : '' }}>Cliente</option>
            </select>
        </div>

        <div>
            <label for="blocked" style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.5rem; text-transform: uppercase;">Estado</label>
            <select id="blocked" name="blocked" style="width: 100%; padding: 0.625rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
                <option value="">Todos</option>
                <option value="0" {{ $blocked === '0' ? 'selected' : '' }}>Ativo</option>
                <option value="1" {{ $blocked === '1' ? 'selected' : '' }}>Bloqueado</option>
            </select>
        </div>

        <div style="display: flex; align-items: flex-end; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.625rem;">Filtrar</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline" style="width: 100%; padding: 0.625rem; text-align: center;">Limpar</a>
        </div>
    </form>

    <!-- Tabela de Utilizadores -->
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-size: 0.875rem;">
                    <th style="padding: 1rem 0.5rem;">Foto</th>
                    <th style="padding: 1rem 0.5rem;">Nome</th>
                    <th style="padding: 1rem 0.5rem;">Email</th>
                    <th style="padding: 1rem 0.5rem;">Tipo</th>
                    <th style="padding: 1rem 0.5rem;">Estado</th>
                    <th style="padding: 1rem 0.5rem; text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr style="border-bottom: 1px solid var(--border); font-size: 0.9375rem; vertical-align: middle;">
                        <td style="padding: 0.75rem 0.5rem;">
                            @if($user->photo_url)
                                <img src="{{ asset('storage/' . $user->photo_url) }}" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            @else
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 700; color: #64748b;">
                                    {{ $user->initials() }}
                                </div>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 0.5rem; font-weight: 600;">{{ $user->name }}</td>
                        <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);">{{ $user->email }}</td>
                        <td style="padding: 0.75rem 0.5rem;">
                            @if($user->user_type === 'A')
                                <span style="background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 99px;">Admin</span>
                            @elseif($user->user_type === 'F')
                                <span style="background: #dbeafe; color: #1e40af; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 99px;">Funcionário</span>
                            @else
                                <span style="background: #ecfdf5; color: #065f46; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 99px;">Cliente</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 0.5rem;">
                            @if($user->blocked)
                                <span style="background: #fef2f2; color: #991b1b; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 99px;">Bloqueado</span>
                            @else
                                <span style="background: #ecfdf5; color: #065f46; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 99px;">Ativo</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 0.5rem; text-align: right;">
                            <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                @if($user->user_type !== 'C')
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline" style="padding: 0.375rem 0.75rem; font-size: 0.8125rem;">Editar</a>
                                @endif
                                
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.block', $user) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline" style="padding: 0.375rem 0.75rem; font-size: 0.8125rem; color: #d97706; border-color: #f59e0b;">
                                            {{ $user->blocked ? 'Desbloquear' : 'Bloquear' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;" onsubmit="return confirm('Tem a certeza que deseja remover este utilizador? Esta ação pode ser desfeita via Soft Delete se houver histórico associado.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline" style="padding: 0.375rem 0.75rem; font-size: 0.8125rem; color: #dc2626; border-color: #f87171;">
                                            Remover
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                            Nenhum utilizador encontrado com os filtros selecionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div style="margin-top: 1.5rem;">
        {{ $users->links() }}
    </div>

</div>
@endsection
