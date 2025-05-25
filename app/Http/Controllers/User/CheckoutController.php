<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('scan.index')
                ->with('error', 'Your cart is empty');
        }

        return view('user.checkout.index', [
            'cart' => $cart,
            'total' => $this->calculateTotal($cart)
        ]);
    }

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);
        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'payment_method' => 'required|in:credit_card,cash,transfer'
        ]);

        try {
            DB::beginTransaction();

            // Validate stock before processing
            foreach ($cart as $item) {
                $product = Product::findOrFail($item['product']->id);
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }
            }

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . Str::upper(Str::random(10)),
                'user_id' => $user->id,
                'subtotal' => $this->calculateSubtotal($cart),
                'tax' => $this->calculateTax($cart),
                'total' => $this->calculateTotal($cart),
                'customer_info' => $validated,
                'payment_method' => $validated['payment_method']
            ]);


            // Attach products and update inventory
            foreach ($cart as $productId => $item) {
                $product = Product::findOrFail($productId);

                $invoice->products()->attach($productId, [
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total' => $item['quantity'] * $product->price
                ]);

                // Update product quantity
                $product->decrement('quantity', $item['quantity']);
            }

            DB::commit();

            session()->forget('cart');

            return redirect()->route('checkout.show', $invoice)
                ->with('success', 'Checkout completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Checkout failed: ' . $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return view('user.checkout.show', [
            'invoice' => $invoice,
            'products' => $invoice->products,
            'customer' => $invoice->customer_info
        ]);
    }

    public function downloadInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $pdf = PDF::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'products' => $invoice->products()->withPivot(['quantity', 'price'])->get(),
            'customer' => $invoice->customer_info
        ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    private function calculateSubtotal($cart)
    {
        return collect($cart)->sum(function ($item) {
            return $item['quantity'] * $item['product']->price;
        });
    }

    private function calculateTax($cart)
    {
        return $this->calculateSubtotal($cart) * 0.10; // 10% tax
    }

    private function calculateTotal($cart)
    {
        return $this->calculateSubtotal($cart) + $this->calculateTax($cart);
    }
}
