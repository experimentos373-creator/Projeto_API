<?php

namespace App\Http\Controllers;

use App\Mail\OrderCanceledMail;
use App\Mail\OrderClosedMail;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Customer: lists only their own orders
        if ($user->user_type === 'C') {
            $orders = Order::where('customer_id', $user->id)
                ->with(['items.tshirt_image'])
                ->orderBy('id', 'desc')
                ->paginate(10);

            return view('orders.index', compact('orders'));
        }

        // 2. Staff: lists only pending orders
        if ($user->user_type === 'F') {
            $orders = Order::where('status', 'pending')
                ->with(['customer.user'])
                ->orderBy('id', 'desc')
                ->paginate(15);

            return view('orders.index', compact('orders'));
        }

        // 3. Admin: lists all orders with advanced filters
        if ($user->user_type === 'A') {
            $query = Order::query()->with(['customer.user']);

            // Filter by Status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by Customer (Name or NIF)
            if ($request->filled('customer')) {
                $search = $request->customer;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('customer.user', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })->orWhereHas('customer', function ($sq) use ($search) {
                        $sq->where('nif', 'like', "%{$search}%");
                    });
                });
            }

            // Filter by Date
            if ($request->filled('date')) {
                $query->whereDate('date', $request->date);
            }

            $orders = $query->orderBy('id', 'desc')->paginate(15);

            return view('orders.index', compact('orders'));
        }

        abort(403, 'Acesso negado.');
    }

    /**
     * Display the specified order details.
     */
    public function show(Order $order)
    {
        // Authorize access via Policy
        Gate::authorize('view', $order);

        // Eager load items, images, and customer user details
        $order->load(['items.tshirt_image', 'items.color', 'customer.user']);

        return view('orders.show', compact('order'));
    }

    /**
     * Update the status of the specified order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Authorize status change via Policy
        Gate::authorize('updateStatus', $order);

        $request->validate([
            'status' => ['required', 'string', 'in:closed,canceled'],
            'reason_for_cancellation' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();

        // Employees can only close (send) orders
        if ($user->user_type === 'F' && $request->status !== 'closed') {
            return back()->with('error', 'Funcionários apenas podem marcar encomendas como Enviadas.');
        }

        $oldStatus = $order->status;
        $order->status = $request->status;

        if ($request->status === 'canceled') {
            // Cancellation reason is optional (nullable)
            $order->reason_for_cancellation = $request->reason_for_cancellation;

            // Trigger cancellation email (Integration G6)
            try {
                if (class_exists(OrderCanceledMail::class)) {
                    Mail::to($order->customer->user)->send(new OrderCanceledMail($order));
                }
            } catch (\Exception $e) {
                logger()->error('Falha ao enviar e-mail de cancelamento: '.$e->getMessage());
            }
        } elseif ($request->status === 'closed') {
            // 1. Generate and save PDF receipt before sending email
            try {
                $pdf = Pdf::loadView('pdf.receipt', compact('order'));
                $pdfDirectory = 'pdf_receipts';

                if (! Storage::disk('local')->exists($pdfDirectory)) {
                    Storage::disk('local')->makeDirectory($pdfDirectory);
                }

                $pdfPath = $pdfDirectory.'/recibo-'.$order->id.'.pdf';
                Storage::disk('local')->put($pdfPath, $pdf->output());

                $order->receipt_url = $pdfPath;
            } catch (\Exception $e) {
                logger()->error('Falha ao gerar recibo PDF: '.$e->getMessage());
            }

            // 2. Trigger closed/shipped email with invoice (Integration G6)
            try {
                if (class_exists(OrderClosedMail::class)) {
                    Mail::to($order->customer->user)->send(new OrderClosedMail($order));
                }
            } catch (\Exception $e) {
                logger()->error('Falha ao enviar e-mail de encerramento de encomenda: '.$e->getMessage());
            }
        }

        $order->save();

        return back()->with('success', 'Estado da encomenda atualizado com sucesso.');
    }

    /**
     * Download the invoice/receipt for the specified order.
     */
    public function downloadReceipt(Order $order)
    {
        // Check authorization (only client owner or staff/admins can download)
        Gate::authorize('view', $order);

        // Security restriction: Only client owner or administrators can download (Staff is blocked)
        $user = Auth::user();
        if ($user->user_type !== 'A' && (int) $order->customer_id !== (int) $user->id) {
            abort(403, 'Acesso negado. Apenas o proprietário ou um administrador podem descarregar o recibo.');
        }

        if ($order->status !== 'closed') {
            return back()->with('error', 'O recibo apenas está disponível para encomendas enviadas.');
        }

        $pdfPath = $order->receipt_url ?? ('pdf_receipts/recibo-'.$order->id.'.pdf');
        if ($pdfPath && ! str_starts_with($pdfPath, 'pdf_receipts/')) {
            $pdfPath = 'pdf_receipts/'.$pdfPath;
        }

        if (! Storage::disk('local')->exists($pdfPath)) {
            // Robust fallback: generate and save PDF if it does not exist on disk
            try {
                $pdf = Pdf::loadView('pdf.receipt', compact('order'));
                $pdfDirectory = 'pdf_receipts';

                if (! Storage::disk('local')->exists($pdfDirectory)) {
                    Storage::disk('local')->makeDirectory($pdfDirectory);
                }

                Storage::disk('local')->put($pdfPath, $pdf->output());

                if (empty($order->receipt_url)) {
                    $order->receipt_url = $pdfPath;
                    $order->save();
                }
            } catch (\Exception $e) {
                logger()->error('Falha ao regenerar recibo PDF no download: '.$e->getMessage());

                return back()->with('error', 'Erro ao gerar o recibo PDF.');
            }
        }

        return response()->download(storage_path('app/private/'.$pdfPath), 'recibo-encomenda-'.$order->id.'.pdf');
    }
}
