<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BarcodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $products = Product::where('user_id', auth()->id())
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('admin.barcodes.index', compact('products'));
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        return view('admin.barcodes.show', [
            'product' => $product,
            'barcodeData' => $this->formatBarcodeData($product)
        ]);
    }

    public function print(Product $product)
    {
        $this->authorize('view', $product);

        return view('admin.barcodes.print', [
            'product' => $product,
            'barcodeData' => $this->formatBarcodeData($product)
        ]);
    }

    public function bulkPrint(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id,user_id,' . auth()->id()
        ]);

        $products = Product::whereIn('id', $validated['product_ids'])
            ->where('user_id', auth()->id())
            ->get();

        return view('admin.barcodes.bulk-print', [
            'products' => $products,
            'perPage' => 4 // Number of barcodes per page
        ]);
    }

    protected function formatBarcodeData(Product $product)
    {
        return [
            'value' => $product->barcode,
            'format' => 'CODE128',
            'text' => $product->name,
            'details' => "{$product->color} / {$product->size}",
            'price' => number_format($product->price, 2),
            'options' => [
                'height' => 100,
                'width' => 2,
                'fontSize' => 16,
                'displayValue' => true,
                'textMargin' => 10
            ]
        ];
    }
}
