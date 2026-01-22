<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'barcode' => 'nullable|unique:products',
        ]);

        $barcode = $request->barcode ?? strtoupper(Str::random(10));

        Product::create([
            'name' => $request->name,
            'barcode' => $barcode,
            'price' => $request->price,
            'stock' => $request->stock,
            'description' => $request->description,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }
    
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'barcode' => 'required|unique:products,barcode,' . $product->id,
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function quickStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'nullable|integer',
            'barcode' => 'nullable|unique:products',
        ]);

        $barcode = $request->barcode ?? strtoupper(Str::random(10));

        $product = Product::create([
            'name' => $request->name,
            'barcode' => $barcode,
            'price' => $request->price,
            'stock' => $request->stock ?? 0,
            'description' => $request->description,
        ]);

        return response()->json(['status' => 'success', 'data' => $product]);
    }
}
