<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Display the G8 Statistics Dashboard.
     */
    public function index(Request $request)
    {
        // 1. Obter anos disponíveis com base nas encomendas fechadas (para o filtro)
        $years = DB::table('orders')
            ->where('status', 'closed')
            ->whereNotNull('date')
            ->selectRaw('distinct substr(date, 1, 4) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // 2. Determinar o ano selecionado (predefinido para o mais recente ou 'all')
        $selectedYear = $request->input('year');
        if ($selectedYear === null) {
            $selectedYear = !empty($years) ? $years[0] : 'all';
        }

        // 3. Query base para as encomendas fechadas ( closed )
        $query = Order::query()->where('status', 'closed')->with(['customer.user']);

        if ($selectedYear !== 'all') {
            $query->whereRaw('substr(date, 1, 4) = ?', [$selectedYear]);
        }

        // --- KPIs Globais ---
        $totalRevenue = (float) (clone $query)->sum('total_price');
        $totalOrders  = (int) (clone $query)->count();
        $totalTshirts = (int) (clone $query)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('qty');
        $averageTicket = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0.0;

        // Indicador suplementar: Encomendas Pendentes (em processamento) - geral, sem filtro de ano
        $pendingOrdersCount = DB::table('orders')->where('status', 'pending')->count();

        // --- Extremos de Negócio ---
        $maxPriceOrder = (clone $query)->orderByDesc('total_price')->first();
        $minPriceOrder = (clone $query)->orderBy('total_price')->first();
        
        $maxQtyOrder = (clone $query)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select('orders.*', DB::raw('SUM(order_items.qty) as total_qty'))
            ->groupBy('orders.id')
            ->orderByDesc('total_qty')
            ->first();

        // --- Evolução de Vendas Mensal ---
        $monthlyRevenue = (clone $query)
            ->selectRaw('substr(date, 6, 2) as month, SUM(total_price) as revenue, COUNT(id) as order_count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyQty = (clone $query)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('substr(orders.date, 6, 2) as month, SUM(order_items.qty) as qty')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStr = str_pad($m, 2, '0', STR_PAD_LEFT);
            $rev = $monthlyRevenue->get($monthStr);
            $qty = $monthlyQty->get($monthStr);
            
            $monthlyData[] = [
                'month'       => $monthStr,
                'revenue'     => $rev ? (float) $rev->revenue : 0.0,
                'order_count' => $rev ? (int) $rev->order_count : 0,
                'qty'         => $qty ? (int) $qty->qty : 0,
            ];
        }

        // --- Vendas por Categoria ---
        $categorySales = (clone $query)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('tshirt_images', 'order_items.tshirt_image_id', '=', 'tshirt_images.id')
            ->leftJoin('categories', 'tshirt_images.category_id', '=', 'categories.id')
            ->selectRaw('COALESCE(categories.name, "Personalizadas") as category_name, SUM(order_items.sub_total) as revenue, SUM(order_items.qty) as qty')
            ->groupBy('category_name')
            ->orderByDesc('revenue')
            ->get();

        // --- Top 5 Estampas de Catálogo (Imagens Públicas) ---
        $topDesigns = (clone $query)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('tshirt_images', 'order_items.tshirt_image_id', '=', 'tshirt_images.id')
            ->whereNull('tshirt_images.customer_id')
            ->selectRaw('tshirt_images.name, SUM(order_items.sub_total) as revenue, SUM(order_items.qty) as qty')
            ->groupBy('tshirt_images.id', 'tshirt_images.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // --- Top 5 Cores ---
        $topColors = (clone $query)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('colors', 'order_items.color_code', '=', 'colors.code')
            ->selectRaw('colors.name, colors.code, SUM(order_items.qty) as qty, SUM(order_items.sub_total) as revenue')
            ->groupBy('colors.code', 'colors.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // --- Top 5 Melhores Clientes ---
        $topCustomers = (clone $query)
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->join('users', 'customers.id', '=', 'users.id')
            ->selectRaw('users.name, customers.nif, COUNT(orders.id) as order_count, SUM(orders.total_price) as total_spent')
            ->groupBy('customers.id', 'users.name', 'customers.nif')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        return view('admin.statistics', compact(
            'years',
            'selectedYear',
            'totalRevenue',
            'totalOrders',
            'totalTshirts',
            'averageTicket',
            'pendingOrdersCount',
            'maxPriceOrder',
            'minPriceOrder',
            'maxQtyOrder',
            'monthlyData',
            'categorySales',
            'topDesigns',
            'topColors',
            'topCustomers'
        ));
    }
}
