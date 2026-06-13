<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo de Encomenda #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-logo {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }
        .header-invoice {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #10b981;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .info-cell {
            width: 50%;
            vertical-align: top;
        }
        .info-title {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 5px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }
        .info-content {
            font-size: 12px;
            color: #1e293b;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 2px solid #cbd5e1;
        }
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .total-box {
            float: right;
            width: 250px;
            margin-top: 10px;
            border-top: 2px solid #0f172a;
            padding-top: 10px;
        }
        .total-row {
            width: 100%;
        }
        .total-label {
            font-weight: bold;
            color: #0f172a;
            font-size: 13px;
        }
        .total-value {
            font-weight: 800;
            color: #10b981;
            font-size: 16px;
            text-align: right;
        }
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Header Table -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                FunShirt
            </td>
            <td class="header-invoice">
                RECIBO / FATURA<br>
                <span style="font-size: 11px; color: #64748b; font-weight: normal;">Encomenda #{{ $order->id }}</span>
            </td>
        </tr>
    </table>

    <!-- Info Table (split 50/50 for Customer and Order details) -->
    <table class="info-table">
        <tr>
            <td class="info-cell" style="padding-right: 20px;">
                <div class="info-title">Dados do Cliente</div>
                <div class="info-content">
                    <strong>Nome:</strong> {{ $order->customer->user->name }}<br>
                    <strong>NIF:</strong> {{ $order->nif }}<br>
                    <strong>Morada:</strong> {{ $order->address }}
                </div>
            </td>
            <td class="info-cell" style="padding-left: 20px;">
                <div class="info-title">Detalhes da Encomenda</div>
                <div class="info-content">
                    <strong>Data de Faturação:</strong> {{ $order->date }}<br>
                    <strong>Método de Pagamento:</strong> {{ $order->payment_type }}<br>
                    <strong>Referência de Pagamento:</strong> {{ $order->payment_ref }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Produto / Design</th>
                <th style="width: 15%; text-align: center;">Tamanho</th>
                <th style="width: 15%; text-align: center;">Cor</th>
                <th style="width: 10%; text-align: center;">Qtd</th>
                <th style="width: 15%; text-align: right;">Preço Unit.</th>
                <th style="width: 15%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        {{ $item->tshirt_image->name }}<br>
                        <span style="font-size: 10px; color: #64748b;">
                            {{ $item->tshirt_image->customer_id ? 'Estampa Própria (Cliente)' : 'Estampa do Catálogo' }}
                        </span>
                    </td>
                    <td style="text-align: center;">{{ $item->size }}</td>
                    <td style="text-align: center;">{{ $item->color->name ?? $item->color_code }}</td>
                    <td style="text-align: center;">{{ $item->qty }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}€</td>
                    <td style="text-align: right;">{{ number_format($item->sub_total, 2) }}€</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total Area -->
    <div style="width: 100%; overflow: hidden;">
        <table class="total-box" style="border-collapse: collapse;">
            <tr>
                <td class="total-label" style="padding-bottom: 5px;">Total Geral:</td>
                <td class="total-value" style="padding-bottom: 5px;">{{ number_format($order->total_price, 2) }}€</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        Obrigado pela sua preferência!<br>
        <strong>FunShirt Lda.</strong> · NIF: 999999999 · ESTG - IPLeiria · Portugal
    </div>

</body>
</html>
