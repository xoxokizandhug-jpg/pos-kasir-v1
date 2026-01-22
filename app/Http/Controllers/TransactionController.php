<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index()
    {
        return view('pos.index');
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        // Exact match for barcode
        $product = Product::where('barcode', $query)->first();
        
        if (!$product) {
             // Partial match for name
             $products = Product::where('name', 'like', "%{$query}%")->limit(10)->get();
             if ($products->count() > 0) {
                 return response()->json(['status' => 'multiple', 'data' => $products]);
             }
             return response()->json(['status' => 'not_found']);
        }

        return response()->json(['status' => 'success', 'data' => $product]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'total_amount' => 'required|numeric',
            'pay_amount' => 'required|numeric',
            'payment_method' => 'required',
        ]);

        try {
            $transaction = DB::transaction(function () use ($request) {
                $change_amount = $request->pay_amount - $request->total_amount;
                
                $transaction = Transaction::create([
                    'transaction_code' => 'TRX-' . strtoupper(Str::random(10)),
                    'total_amount' => $request->total_amount,
                    'pay_amount' => $request->pay_amount,
                    'change_amount' => $change_amount,
                    'payment_method' => $request->payment_method,
                ]);
    
                foreach ($request->cart as $item) {
                    $product = Product::find($item['id']);
                    if ($product) {
                        if ($product->stock < $item['qty']) {
                            throw new \Exception("Stok tidak cukup untuk produk: " . $product->name);
                        }
                        
                        TransactionItem::create([
                            'transaction_id' => $transaction->id,
                            'product_id' => $product->id,
                            'quantity' => $item['qty'],
                            'price' => $product->price,
                            'subtotal' => $item['qty'] * $product->price,
                        ]);
                        
                        // Decrease stock
                        $product->decrement('stock', $item['qty']);
                    }
                }
                
                return $transaction;
            });
            
            return response()->json([
                'status' => 'success', 
                'redirect_url' => route('pos.print', $transaction->id)
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function print(Transaction $transaction)
    {
        return view('transactions.print', compact('transaction'));
    }
}
