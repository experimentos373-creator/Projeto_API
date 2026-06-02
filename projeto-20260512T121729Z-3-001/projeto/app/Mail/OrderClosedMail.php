<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OrderClosedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject("Encomenda Enviada — FunShirt #{$this->order->id}")
                     ->view('emails.order_closed');

        $pdfPath = $this->order->receipt_url ?? 'private/pdf_receipts/recibo-' . $this->order->id . '.pdf';

        if (Storage::disk('local')->exists($pdfPath)) {
            $mail->attach(Storage::disk('local')->path($pdfPath), [
                'as' => 'recibo-encomenda-' . $this->order->id . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
