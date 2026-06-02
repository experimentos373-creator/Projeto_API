<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Encomenda Registada</title>
</head>
<body style="font-family: 'Outfit', 'Inter', sans-serif; background-color: #f8fafc; margin: 0; padding: 2rem; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 2.5rem; text-align: center; border-bottom: 4px solid #10b981;">
            <h1 style="color: #ffffff; margin: 0; font-size: 1.75rem; font-weight: 800; letter-spacing: -0.025em;">🎽 FunShirt</h1>
            <p style="color: #94a3b8; margin: 0.5rem 0 0; font-size: 0.95rem; font-weight: 500;">O seu pedido foi recebido com sucesso!</p>
        </div>
        
        <!-- Content -->
        <div style="padding: 2.5rem;">
            <p style="font-size: 1rem; line-height: 1.6; margin: 0 0 1.5rem;">Olá, <strong>{{ $order->customer->user->name }}</strong>,</p>
            <p style="font-size: 1rem; line-height: 1.6; margin: 0 0 1.5rem;">Confirmamos que o pagamento da sua encomenda <strong>#{{ $order->id }}</strong> foi recebido com sucesso e o pedido encontra-se agora em processamento (estampagem e preparação para envio).</p>
            
            <!-- Details Box -->
            <div style="background-color: #f1f5f9; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid #e2e8f0;">
                <h3 style="margin-top: 0; color: #0f172a; font-size: 1rem; font-weight: 700; border-bottom: 1px solid #cbd5e1; padding-bottom: 0.5rem;">Detalhes do Pedido</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; line-height: 1.6;">
                    <tr>
                        <td style="color: #64748b; font-weight: 600; padding: 0.25rem 0;">Data:</td>
                        <td style="color: #0f172a; font-weight: 700; text-align: right; padding: 0.25rem 0;">{{ $order->date }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: 600; padding: 0.25rem 0;">NIF:</td>
                        <td style="color: #0f172a; text-align: right; padding: 0.25rem 0;">{{ $order->nif }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: 600; padding: 0.25rem 0;">Método de Pagamento:</td>
                        <td style="color: #0f172a; text-align: right; padding: 0.25rem 0;">{{ $order->payment_type }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-weight: 600; padding: 0.25rem 0;">Morada de Entrega:</td>
                        <td style="color: #0f172a; text-align: right; padding: 0.25rem 0;">{{ $order->address }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #cbd5e1;">
                        <td style="color: #0f172a; font-weight: 700; padding: 0.75rem 0 0;">Total Pago:</td>
                        <td style="color: #10b981; font-weight: 800; text-align: right; font-size: 1.1rem; padding: 0.75rem 0 0;">{{ number_format($order->total_price, 2) }}€</td>
                    </tr>
                </table>
            </div>

            <p style="font-size: 1rem; line-height: 1.6; margin: 0 0 1.5rem;">Pode consultar o estado da sua encomenda a qualquer momento acedendo à sua área pessoal na nossa loja online.</p>
            
            <div style="text-align: center; margin: 2.5rem 0 1rem;">
                <a href="{{ route('orders.show', $order->id) }}" style="background-color: #10b981; color: #ffffff; padding: 0.75rem 1.5rem; text-decoration: none; font-weight: 700; border-radius: 6px; display: inline-block; box-shadow: 0 2px 4px rgba(16,185,129,0.2);">Ver Encomenda na Loja</a>
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
