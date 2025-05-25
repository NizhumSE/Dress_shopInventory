<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('user_id', auth()->id())->latest()->get();
        return view('admin.products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'color' => 'required|string|max:50',
            'size' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image'] = $imagePath;
        }

        Product::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'color' => $validated['color'],
            'size' => $validated['size'],
            'quantity' => $validated['quantity'],
            'price' => $validated['price'],
            'image' => $validated['image'] ?? null,
            'user_id' => auth()->id(),
            'barcode' => $this->generateUniqueBarcode()
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully');
    }

    private function generateUniqueBarcode()
    {
        do {
            $barcode = 'DSS-' . Str::upper(Str::random(10));
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    // Add these other necessary methods
    public function create()
    {
        return view('admin.products.create');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        // Similar validation and update logic
    }

    public function destroy(Product $product)
    {
        // Delete logic with image removal
    }
}
