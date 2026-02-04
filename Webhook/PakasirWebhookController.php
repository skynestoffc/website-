namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PakasirWebhookController extends Controller
{
    public function handle(Request $request, FulfillmentService $fulfillment)
    {
        // 1) Ambil payload webhook
        $data = $request->all();
        $orderId = $data['order_id'] ?? null;
        $amount  = (int)($data['amount'] ?? 0);
        $status  = $data['status'] ?? null;

        if (!$orderId || $amount <= 0) {
            return response()->json(['ok' => false], 400);
        }

        $order = Order::where('order_code', $orderId)->first();
        if (!$order) return response()->json(['ok' => true]); // jangan bocorin

        // 2) Cek amount cocok
        if ((int)$order->subtotal_amount !== $amount) {
            return response()->json(['ok' => true]); // ignore
        }

        // 3) Webhook bilang completed → tetap verifikasi ke Transaction Detail API
        if ($status === 'completed') {
            $resp = Http::get('https://app.pakasir.com/api/transactiondetail', [
                'project'  => config('services.pakasir.slug'),
                'amount'   => $amount,
                'order_id' => $orderId,
                'api_key'  => config('services.pakasir.api_key'),
            ]);

            if ($resp->ok() && ($resp->json('transaction.status') === 'completed')) {
                if ($order->status !== 'paid' && $order->status !== 'fulfilled') {
                    $order->update(['status' => 'paid', 'paid_at' => now()]);
                }
                $fulfillment->fulfillPaidOrder($order);
            }
        }

        return response()->json(['ok' => true]);
    }
}
