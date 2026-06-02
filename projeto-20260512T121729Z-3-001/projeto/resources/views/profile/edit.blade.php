@extends('layouts.app')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
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

    <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
        
        <!-- Secção: Dados de Perfil -->
        <div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">Dados Pessoais</h2>
            
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div style="display: flex; gap: 2rem; align-items: center; margin-bottom: 2rem; flex-wrap: wrap;">
                    <div style="position: relative;">
                        @if($user->photo_url)
                            <img src="{{ asset('storage/' . $user->photo_url) }}" alt="Foto de {{ $user->name }}" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--border);">
                        @else
                            <div style="width: 100px; height: 100px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: #64748b; border: 3px solid var(--border);">
                                {{ $user->initials() }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <label for="photo" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Fotografia de Perfil</label>
                        <input type="file" id="photo" name="photo" style="font-size: 0.875rem; color: var(--text-muted);">
                        <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Formatos suportados: JPG, PNG. Tamanho máx: 1MB.</p>
                        @error('photo')
                            <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label for="name" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Nome Completo</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                        @error('name')
                            <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="email" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                        @error('email')
                            <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="gender" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Género</label>
                    <select id="gender" name="gender" required 
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
                        <option value="M" {{ old('gender', $user->gender) == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('gender', $user->gender) == 'F' ? 'selected' : '' }}>Feminino</option>
                    </select>
                    @error('gender')
                        <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                @if($user->user_type === 'C' && $customer)
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-top: 2rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Dados de Faturação e Envio</h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div>
                            <label for="nif" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">NIF</label>
                            <input type="text" id="nif" name="nif" value="{{ old('nif', $customer->nif) }}" placeholder="NIF com 9 dígitos"
                                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                                onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                            @error('nif')
                                <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="default_payment_type" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Método de Pagamento Preferencial</label>
                            <select id="default_payment_type" name="default_payment_type"
                                style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; background: white;">
                                <option value="">Não especificado</option>
                                <option value="Visa" {{ old('default_payment_type', $customer->default_payment_type) == 'Visa' ? 'selected' : '' }}>Visa</option>
                                <option value="PayPal" {{ old('default_payment_type', $customer->default_payment_type) == 'PayPal' ? 'selected' : '' }}>PayPal</option>
                                <option value="MB WAY" {{ old('default_payment_type', $customer->default_payment_type) == 'MB WAY' ? 'selected' : '' }}>MB WAY</option>
                            </select>
                            @error('default_payment_type')
                                <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="default_payment_ref" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Referência de Pagamento Preferencial</label>
                        <input type="text" id="default_payment_ref" name="default_payment_ref" value="{{ old('default_payment_ref', $customer->default_payment_ref) }}" 
                            placeholder="Nº cartão (Visa), Email (PayPal) ou Telemóvel (MB WAY)"
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                        @error('default_payment_ref')
                            <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <label for="address" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Morada de Envio</label>
                        <textarea id="address" name="address" rows="3" placeholder="Insira a sua morada de entrega"
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none; font-family: inherit; resize: vertical;"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">{{ old('address', $customer->address) }}</textarea>
                        @error('address')
                            <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">Guardar Alterações</button>
            </form>
        </div>

        <!-- Secção: Alterar Palavra-passe -->
        <div style="background: white; padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">Alterar Palavra-passe</h2>
            
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 1.25rem;">
                    <label for="current_password" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Palavra-passe Atual</label>
                    <input type="password" id="current_password" name="current_password" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                    @error('current_password')
                        <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Nova Palavra-passe</label>
                        <input type="password" id="password" name="password" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                        @error('password')
                            <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Confirmar Nova Palavra-passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); outline: none;"
                            onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">Atualizar Palavra-passe</button>
            </form>
        </div>

    </div>
</div>
@endsection
