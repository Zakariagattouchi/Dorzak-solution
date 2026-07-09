<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Product $product)
    {
        $this->afterCommit(); // dispatch only after the transaction commits
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'stock' => $this->product->stock,
            'min_stock' => $this->product->min_stock,
        ];
    }
}
