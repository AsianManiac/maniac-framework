<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Invoice;
use Core\Mail\Mailable;
use Core\Contracts\Queue\ShouldQueue;
use Core\Notifications\BaseNotification;

class InvoicePaid extends BaseNotification implements ShouldQueue
{
    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable (e.g., User instance)
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        $channels = ['database']; // Always store in DB
        // Send email only if user has email notifications enabled (example)
        if ($notifiable instanceof User && $notifiable->prefers_email_notifications) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): ?Mailable
    {
        // You can create a dedicated Mailable or build it here
        $mailable = new Mailable(); // Use base mailable for simple cases
        $mailable->subject('Your Invoice #' . $this->invoice->id . ' Has Been Paid')
            ->markdown('mail.invoice.paid', ['invoice' => $this->invoice, 'user' => $notifiable]);
        // ->attach(...)
        return $mailable;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(mixed $notifiable): array
    {
        // Data to store in the 'data' JSON column
        return [
            'invoice_id' => $this->invoice->id,
            'amount_paid' => $this->invoice->amount,
            'message' => "Invoice #{$this->invoice->id} for {$this->invoice->amount} has been paid.",
            'action_url' => url('/invoices/' . $this->invoice->id), // Link for UI
        ];
    }

    /**
     * Get the array representation (used by toDatabase if not overridden).
     */
    // public function toArray(mixed $notifiable): array {
    //     return [
    //         'invoice_id' => $this->invoice->id,
    //     ];
    // }
}
