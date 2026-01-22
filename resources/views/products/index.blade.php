@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Gudang / Produk</h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary">Tambah Produk</a>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Barcode</th>
            <th>Nama</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
        <tr>
            <td>
                <svg class="barcode"
                     jsbarcode-format="CODE128"
                     jsbarcode-value="{{ $product->barcode }}"
                     jsbarcode-textmargin="0"
                     jsbarcode-fontoptions="bold"
                     jsbarcode-height="30"
                     jsbarcode-width="1"
                     jsbarcode-displayValue="true">
                </svg>
            </td>
            <td>{{ $product->name }}</td>
            <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
            <td>{{ $product->stock }}</td>
            <td>
                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">Edit</a>
                <button onclick="printBarcode('{{ $product->barcode }}')" class="btn btn-sm btn-secondary">Print Barcode</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Hidden Print Area -->
<div id="print-area" class="barcode-print">
    <div style="text-align: center;">
        <svg id="print-barcode"></svg>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('assets/js/JsBarcode.all.min.js') }}"></script>
<script>
    // Initialize all barcodes
    function initBarcodes() {
        JsBarcode(".barcode").init();
    }
    initBarcodes();

    window.printBarcode = function(code) {
        // Clear previous content
        document.getElementById('print-barcode').innerHTML = '';
        
        // Generate barcode for print
        JsBarcode("#print-barcode", code, {
            format: "CODE128",
            displayValue: true
        });
        
        window.print();
    }
</script>
@endsection
