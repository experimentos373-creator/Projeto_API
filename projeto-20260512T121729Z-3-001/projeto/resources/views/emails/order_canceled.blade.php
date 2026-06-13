<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Encomenda Cancelada</title>
</head>
<body style="font-family: 'Outfit', 'Inter', sans-serif; background-color: #f8fafc; margin: 0; padding: 2rem; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 2.5rem; text-align: center; border-bottom: 4px solid #ef4444;">
            <h1 style="color: #ffffff; margin: 0; font-size: 1.75rem; font-weight: 800; letter-spacing: -0.025em;">FunShirt</h1>
            <p style="color: #fca5a5; margin: 0.5rem 0 0; font-size: 0.95rem; font-weight: 500;">A sua encomenda foi cancelada.</p>
        </div>
        
        <!-- Content -->
        <div style="padding: 2.5rem;">
            <p style="font-size: 1rem; line-height: 1.6; margin: 0 0 1.5rem;">Olá, <strong>{{ $order->customer->user->name }}</strong>,</p>
            <p style="font-size: 1rem; line-height: 1.6; margin: 0 0 1.5rem;">Lamentamos informar que a sua encomenda <strong>#{{ $order->id }}</strong> foi cancelada pelo nosso sistema ou por decisão administrativa.</p>
            
            @if($order->reason_for_cancellation)
                <!-- Cancellation Reason Box -->
                <div style="background-color: #fef2f2; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid #fee2e2; color: #991b1b;">
                    <h3 style="margin-top: 0; font-size: 1rem; font-weight: 700; border-bottom: 1px solid #fecaca; padding-bottom: 0.5rem;">Motivo do Cancelamento</h3>
                    <p style="margin: 0.5rem 0 0; font-size: 0.95rem; line-height: 1.6; font-style: italic;">
                        "{{ $order->reason_for_cancellation }}"
                    </p>
                </div>
            @endif

            <p style="font-size: 1rem; line-height: 1.6; margin: 0 0 1.5rem;">O reembolso correspondente à transação será processado de acordo com o método de pagamento selecionado (MB WAY, Visa ou PayPal). Em caso de dúvidas, não hesite em contactar o nosso suporte ao cliente.</p>
            
            <div style="text-align: center; margin: 2.5rem 0 1rem;">
                <a href="{{ route('orders.show', $order->id) }}" style="background-color: #ef4444; color: #ffffff; padding: 0.75rem 1.5rem; text-decoration: none; font-weight: 700; border-radius: 6px; display: inline-block; box-shadow: 0 2px 4px rgba(239,68,68,0.2);">Ver Detalhes na Loja</a>
            </div>
        </div>
        
        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 1.5rem 2.5rem; text-align: center; border-top: 1px solid #e2e8f0; font-size: 0.8rem; color: #64748b;">
            © {{ date('Y') }} FunShirt. Todos os direitos reservados.<br>
            Aplicações para a Internet · Escola Superior de Tecnologia e Gestão
        </div>
    </div>
</body>
</html>
