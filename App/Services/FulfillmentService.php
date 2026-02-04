namespace App\Services;

use App\Models\Order;
use App\Models\ProductStock;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;

class FulfillmentService
{
    public function fulfillPaidOrder(Order $order): void
    {
        if ($order->status === 'fulfilled') return; // idempotent

        DB::transaction(function () use ($order) {
            $item = $order->items()->firstOrFail(); // asumsi 1 item 1 qty
            $stock = ProductStock::where('product_id', $item->product_id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                // stok habis → biarkan order paid tapi belum fulfilled (atau buat status khusus)
                $order->update(['status' => 'paid']);
                return;
            }

            $stock->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            Delivery::create([
                'order_id' => $order->id,
                'product_stock_id' => $stock->id,
                'delivered_to_email' => $order->customer_email,
                'delivered_payload_text' => $stock->payload_text,
                'delivered_at' => now(),
            ]);

            $order->update(['status' => 'fulfilled']);
        });
    }
}
