<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScanController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('user.scan.index', compact('cart'));
    }

    public function handleScan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barcode' => 'required|string|exists:products,barcode'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid barcode scanned'
            ], 422);
        }

        $product = Product::where('barcode', $request->barcode)
            ->where('quantity', '>', 0)
            ->firstOrFail();

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            // Prevent exceeding available quantity
            if ($cart[$product->id]['quantity'] < $product->quantity) {
                $cart[$product->id]['quantity']++;
            }
        } else {
            $cart[$product->id] = [
                'product' => $product,
                'quantity' => 1,
                'price' => $product->price
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'product' => $product,
            'cartTotal' => $this->calculateCartTotal($cart)
        ]);
    }

    public function manualEntry(Request $request)
    {
        $request->validate([
            'barcode' => 'required|exists:products,barcode'
        ]);

        $product = Product::where('barcode', $request->barcode)
            ->where('quantity', '>', 0)
            ->firstOrFail();

        $this->addToCart($product);

        return redirect()->route('scan.index')
            ->with('success', 'Product added: ' . $product->name);
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = session()->get('cart', []);
        $product = Product::findOrFail($request->product_id);

        if (isset($cart[$request->product_id])) {
            $newQuantity = min($request->quantity, $product->quantity);
            $cart[$request->product_id]['quantity'] = $newQuantity;

            session()->put('cart', $cart);
        }

        return redirect()->route('scan.index')
            ->with('success', 'Cart updated successfully');
    }

    public function removeFromCart($productId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        return redirect()->route('scan.index')
            ->with('success', 'Product removed from cart');
    }

    private function addToCart($product)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            if ($cart[$product->id]['quantity'] < $product->quantity) {
                $cart[$product->id]['quantity']++;
            }
        } else {
            $cart[$product->id] = [
                'product' => $product,
                'quantity' => 1,
                'price' => $product->price
            ];
        }

        session()->put('cart', $cart);
    }

    private function calculateCartTotal($cart)
    {
        return array_reduce($cart, function ($total, $item) {
            return $total + ($item['price'] * $item['quantity']);
        }, 0);
    }
}
