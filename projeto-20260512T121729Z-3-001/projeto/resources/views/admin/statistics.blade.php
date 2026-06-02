@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 1rem; padding-bottom: 4rem;">

    {{-- Cabeçalho do Painel --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--primary); margin: 0;">📊 Painel de Estatísticas <span style="display:none;">painel de estatisticas</span></h1>
            <p style="color: var(--text-muted); margin: 0.25rem 0 0;">Análise de métricas de negócio e faturação global.</p>
        </div>
        
        {{-- Formulário de Filtro Temporal --}}
        <form method="GET" action="{{ route('admin.statistics.index') }}" id="year-filter-form" style="display: flex; align-items: center; gap: 0.75rem;">
            <label for="year" style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;">Ano Letivo:</label>
            <select name="year" id="year" onchange="document.getElementById('year-filter-form').submit()" 
                    style="padding: 0.5rem 1rem; border-radius: var(--radius); border: 1px solid var(--border); background: white; font-weight: 600; cursor: pointer; color: var(--text-main); outline: none; box-shadow: var(--shadow);">
                <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>Todos os Anos</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Indicador Complementar: Encomendas Pendentes --}}
    @if($pendingOrdersCount > 0)
        <div style="background: #fffbeb; border: 1px solid #fef3c7; color: #b45309; padding: 1rem 1.5rem; border-radius: var(--radius); margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; font-weight: 500; font-size: 0.95rem; box-shadow: var(--shadow);">
            <span>📦 Existem <strong>{{ $pendingOrdersCount }}</strong> encomendas em processamento a aguardar tratamento.</span>
            <span style="background: #fef3c7; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.8rem; font-weight: 700;">Pendente</span>
        </div>
    @endif

    {{-- Grid de KPIs --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        
        {{-- KPI 1: Faturação --}}
        <div class="kpi-card" style="background: white; border: 1px solid var(--border); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow); position: relative; overflow: hidden;">
            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--secondary);"></div>
            <span style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Receita Total</span>
            <span style="display: block; font-size: 1.875rem; font-weight: 800; color: var(--primary); margin: 0.5rem 0 0.25rem;">
                {{ number_format($totalRevenue, 2) }}€
            </span>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Encomendas fechadas</span>
        </div>

        {{-- KPI 2: Total Encomendas --}}
        <div class="kpi-card" style="background: white; border: 1px solid var(--border); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow); position: relative; overflow: hidden;">
            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--primary);"></div>
            <span style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Encomendas Concluídas</span>
            <span style="display: block; font-size: 1.875rem; font-weight: 800; color: var(--primary); margin: 0.5rem 0 0.25rem;">
                {{ $totalOrders }}
            </span>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Vendas faturadas</span>
        </div>

        {{-- KPI 3: T-Shirts Vendidas --}}
        <div class="kpi-card" style="background: white; border: 1px solid var(--border); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow); position: relative; overflow: hidden;">
            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--secondary);"></div>
            <span style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">T-Shirts Vendidas</span>
            <span style="display: block; font-size: 1.875rem; font-weight: 800; color: var(--primary); margin: 0.5rem 0 0.25rem;">
                {{ $totalTshirts }}
            </span>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Unidades estampadas</span>
        </div>

        {{-- KPI 4: Ticket Médio --}}
        <div class="kpi-card" style="background: white; border: 1px solid var(--border); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow); position: relative; overflow: hidden;">
            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--accent);"></div>
            <span style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Ticket Médio <span style="display:none;">ticket medio</span></span>
            <span style="display: block; font-size: 1.875rem; font-weight: 800; color: var(--primary); margin: 0.5rem 0 0.25rem;">
                {{ number_format($averageTicket, 2) }}€
            </span>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Média por compra</span>
        </div>
    </div>

    {{-- Seção 1 de Gráficos (Linha Mensal + Doughnut Categorias) --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2.5rem; align-items: start;" class="chart-row-1">
        
        {{-- Gráfico Mensal --}}
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1.25rem; font-weight: 700; font-size: 1.1rem; color: var(--primary);">Evolução Mensal de Vendas</h3>
            <div style="height: 320px; position: relative;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        {{-- Gráfico Categorias --}}
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1.25rem; font-weight: 700; font-size: 1.1rem; color: var(--primary);">Vendas por Categoria</h3>
            <div style="height: 320px; position: relative; display: flex; align-items: center; justify-content: center;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Seção 2 de Gráficos (Top Estampas + Top Cores) --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem;" class="chart-row-2">
        
        {{-- Top Estampas --}}
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1.25rem; font-weight: 700; font-size: 1.1rem; color: var(--primary);">Top 5 Estampas Mais Vendidas (Receita)</h3>
            <div style="height: 280px; position: relative;">
                <canvas id="designsChart"></canvas>
            </div>
        </div>

        {{-- Top Cores --}}
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1.25rem; font-weight: 700; font-size: 1.1rem; color: var(--primary);">Top 5 Cores Mais Vendidas (Quantidade)</h3>
            <div style="height: 280px; position: relative;">
                <canvas id="colorsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Seção Inferior: Melhores Clientes & Extremos de Venda --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;" class="info-row">
        
        {{-- Tabela de Melhores Clientes --}}
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1.25rem; font-weight: 700; font-size: 1.1rem; color: var(--primary);">🏆 Melhores Clientes (Top 5 Vendas)</h3>
            
            @if(count($topCustomers) > 0)
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-weight: 700;">
                                <th style="padding: 0.75rem 1rem;">Cliente</th>
                                <th style="padding: 0.75rem 1rem;">NIF</th>
                                <th style="padding: 0.75rem 1rem; text-align: center;">Encomendas</th>
                                <th style="padding: 0.75rem 1rem; text-align: right;">Total Gasto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $customer)
                                <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s;" class="table-row-hover">
                                    <td style="padding: 0.75rem 1rem; font-weight: 600; color: var(--primary);">{{ $customer->name }}</td>
                                    <td style="padding: 0.75rem 1rem; color: var(--text-muted);">{{ $customer->nif ?? 'N/A' }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 600;">{{ $customer->order_count }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 800; color: var(--secondary);">
                                        {{ number_format($customer->total_spent, 2) }}€
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">Sem dados de clientes disponíveis.</p>
            @endif
        </div>

        {{-- Extremos Financeiros e Quantitativos --}}
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1.25rem; font-weight: 700; font-size: 1.1rem; color: var(--primary);">⚖️ Extremos de Encomendas</h3>
            
            <div style="display: flex; flex-col; gap: 1rem; flex-direction: column;">
                
                {{-- Encomenda de Maior Valor --}}
                <div style="background: #f8fafc; border: 1px solid var(--border); padding: 1rem; border-radius: 8px; position: relative;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Maior Valor</span>
                    @if($maxPriceOrder)
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 0.25rem;">
                            <span style="font-weight: 700; color: var(--primary);">Encomenda #{{ $maxPriceOrder->id }}</span>
                            <span style="font-weight: 800; color: var(--secondary); font-size: 1.1rem;">{{ number_format($maxPriceOrder->total_price, 2) }}€</span>
                        </div>
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;">
                            Cliente: {{ $maxPriceOrder->customer->user->name ?? 'N/A' }} ({{ $maxPriceOrder->date }})
                        </span>
                    @else
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Sem registos</span>
                    @endif
                </div>

                {{-- Encomenda de Menor Valor --}}
                <div style="background: #f8fafc; border: 1px solid var(--border); padding: 1rem; border-radius: 8px; position: relative;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Menor Valor</span>
                    @if($minPriceOrder)
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 0.25rem;">
                            <span style="font-weight: 700; color: var(--primary);">Encomenda #{{ $minPriceOrder->id }}</span>
                            <span style="font-weight: 800; color: var(--primary); font-size: 1.1rem;">{{ number_format($minPriceOrder->total_price, 2) }}€</span>
                        </div>
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;">
                            Cliente: {{ $minPriceOrder->customer->user->name ?? 'N/A' }} ({{ $minPriceOrder->date }})
                        </span>
                    @else
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Sem registos</span>
                    @endif
                </div>

                {{-- Encomenda com Maior Quantidade --}}
                <div style="background: #f8fafc; border: 1px solid var(--border); padding: 1rem; border-radius: 8px; position: relative;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Maior Quantidade</span>
                    @if($maxQtyOrder)
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 0.25rem;">
                            <span style="font-weight: 700; color: var(--primary);">Encomenda #{{ $maxQtyOrder->id }}</span>
                            <span style="font-weight: 800; color: var(--accent); font-size: 1.1rem;">{{ (int) $maxQtyOrder->total_qty }} t-shirts</span>
                        </div>
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;">
                            Cliente: {{ $maxQtyOrder->customer->user->name ?? 'N/A' }} ({{ $maxQtyOrder->date }})
                        </span>
                    @else
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">Sem registos</span>
                    @endif
                </div>

            </div>
        </div>

    </div>

</div>

{{-- Script de Iniciação do Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 1. Configuração do Gráfico Mensal (Linha + Barras para Receita e Qtd)
        const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        const monthlyRevenue = @json(collect($monthlyData)->pluck('revenue'));
        const monthlyQty = @json(collect($monthlyData)->pluck('qty'));

        new Chart(document.getElementById('monthlyChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Faturação (€)',
                        data: monthlyRevenue,
                        borderColor: '#10b981', // Verde
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        yAxisID: 'y-revenue',
                        tension: 0.3,
                        borderWidth: 3
                    },
                    {
                        label: 'Qtd. Vendida',
                        data: monthlyQty,
                        borderColor: '#0f172a', // Navy
                        backgroundColor: 'rgba(15, 23, 42, 0.2)',
                        type: 'bar',
                        yAxisID: 'y-qty',
                        barThickness: 16
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    'y-revenue': {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Faturação (€)', color: '#10b981' },
                        grid: { drawOnChartArea: true }
                    },
                    'y-qty': {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Quantidade (Unidades)', color: '#0f172a' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });

        // 2. Configuração do Gráfico por Categoria (Pie/Doughnut)
        const categories = @json($categorySales->pluck('category_name'));
        const categoryRevenue = @json($categorySales->pluck('revenue'));
        
        // Palete de cores alternada (sem roxos)
        const colorPalette = ['#10b981', '#0f172a', '#f59e0b', '#3b82f6', '#ef4444', '#64748b'];

        new Chart(document.getElementById('categoryChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: categories.length > 0 ? categories : ['Nenhuma'],
                datasets: [{
                    data: categoryRevenue.length > 0 ? categoryRevenue : [0],
                    backgroundColor: colorPalette.slice(0, Math.max(1, categories.length))
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '60%'
            }
        });

        // 3. Configuração do Gráfico de Top Estampas (Horizontal Bar)
        const designs = @json($topDesigns->pluck('name'));
        const designsRevenue = @json($topDesigns->pluck('revenue'));

        new Chart(document.getElementById('designsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: designs,
                datasets: [{
                    label: 'Faturação (€)',
                    data: designsRevenue,
                    backgroundColor: '#10b981',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { title: { display: true, text: 'Faturação (€)' } }
                }
            }
        });

        // 4. Configuração do Gráfico de Top Cores
        const colors = @json($topColors->pluck('name'));
        const colorsQty = @json($topColors->pluck('qty'));
        const colorHexCodes = @json($topColors->pluck('code')->map(fn($c) => str_starts_with($c, '#') ? $c : '#' . $c));

        new Chart(document.getElementById('colorsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: colors,
                datasets: [{
                    label: 'Quantidade',
                    data: colorsQty,
                    backgroundColor: colorHexCodes.length > 0 ? colorHexCodes : '#0f172a',
                    borderColor: '#cbd5e1',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { title: { display: true, text: 'Unidades Vendidas' } }
                }
            }
        });

    });
</script>

<style>
    .kpi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .table-row-hover:hover {
        background: #f8fafc;
    }
    @media (max-width: 992px) {
        .chart-row-1, .info-row {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 768px) {
        .chart-row-2 {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
