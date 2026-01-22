@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Scan Barcode / Input Manual</div>
            <div class="card-body">
                <input type="text" id="barcode-input" class="form-control" placeholder="Scan barcode disini..." autofocus>
                <small class="text-muted">Tekan Enter setelah input manual.</small>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Keranjang Belanja</div>
            <div class="card-body">
                <table class="table" id="cart-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Cart items go here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h3>Total: Rp <span id="total-amount">0</span></h3>
                <div class="mb-3">
                    <label>Metode Pembayaran</label>
                    <select id="payment-method" class="form-control">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Nominal Bayar</label>
                    <input type="number" id="pay-amount" class="form-control" placeholder="0">
                </div>
                <div class="mb-3">
                    <label>Kembalian</label>
                    <h4 id="change-amount">Rp 0</h4>
                </div>
                <button class="btn btn-success w-100 btn-lg" id="btn-checkout">Bayar / Selesai</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let cart = [];
    let lastQuery = '';
    
    $('#barcode-input').on('keypress', function(e) {
        if (e.which == 13) { // Enter key
            let code = $(this).val();
            if(code) {
                lastQuery = code;
                searchProduct(code);
                $(this).val('');
            }
        }
    });

    function searchProduct(query) {
        $.ajax({
            url: '{{ route("pos.search") }}',
            data: { query: query },
            success: function(res) {
                if(res.status === 'success') {
                    addToCart(res.data);
                } else if(res.status === 'multiple') {
                    openSelectOverlay(res.data);
                } else {
                    openQuickAddOverlay(lastQuery);
                }
            }
        });
    }

    function addToCart(product) {
        let existing = cart.find(i => i.id === product.id);
        if(existing) {
            existing.qty++;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                qty: 1
            });
        }
        renderCart();
    }

    function renderCart() {
        let tbody = $('#cart-table tbody');
        tbody.empty();
        let total = 0;
        cart.forEach((item, index) => {
            let subtotal = item.price * item.qty;
            total += subtotal;
            tbody.append(`
                <tr>
                    <td>${item.name}</td>
                    <td>${item.price}</td>
                    <td>
                        <input type="number" value="${item.qty}" min="1" style="width: 60px" onchange="updateQty(${index}, this.value)">
                    </td>
                    <td>${subtotal}</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeItem(${index})">X</button></td>
                </tr>
            `);
        });
        $('#total-amount').text(total.toLocaleString('id-ID'));
        calculateChange();
    }

    $('#pay-amount').on('keyup change', function() {
        calculateChange();
    });

    function calculateChange() {
        let total = 0;
        cart.forEach(item => total += item.price * item.qty);
        
        let pay = parseInt($('#pay-amount').val()) || 0;
        let change = pay - total;
        
        if (pay > 0) {
            $('#change-amount').text('Rp ' + change.toLocaleString('id-ID'));
            if (change < 0) {
                $('#change-amount').addClass('text-danger');
            } else {
                $('#change-amount').removeClass('text-danger');
            }
        } else {
            $('#change-amount').text('Rp 0');
            $('#change-amount').removeClass('text-danger');
        }
    }

    window.updateQty = function(index, val) {
        cart[index].qty = parseInt(val);
        renderCart();
    }

    window.removeItem = function(index) {
        cart.splice(index, 1);
        renderCart();
    }

    $('#btn-checkout').click(function() {
        if(cart.length === 0) {
            alert('Keranjang kosong!');
            return;
        }

        let total = 0;
        cart.forEach(item => total += item.price * item.qty);
        
        let payAmount = parseInt($('#pay-amount').val()) || 0;
        
        // Validation for Cash payment
        if ($('#payment-method').val() === 'cash' && payAmount < total) {
            alert('Uang pembayaran kurang!');
            return;
        }
        
        // For non-cash, auto-fill exact amount if 0
        if ($('#payment-method').val() !== 'cash' && payAmount === 0) {
            payAmount = total;
        }

        $.ajax({
            url: '{{ route("pos.store") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                cart: cart,
                total_amount: total,
                pay_amount: payAmount,
                payment_method: $('#payment-method').val()
            },
            success: function(res) {
                if(res.status === 'success') {
                    // Open Receipt in new window
                    window.open(res.redirect_url, '_blank', 'width=400,height=600');
                    
                    alert('Transaksi Berhasil!');
                    cart = [];
                    $('#pay-amount').val('');
                    renderCart();
                } else {
                    alert('Gagal: ' + res.message);
                }
            },
            error: function(err) {
                alert('Terjadi kesalahan');
                console.error(err);
            }
        });
    });
</script>
@endsection

@section('content')
<!-- Quick Add Overlay -->
<div id="quick-add-overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1050;">
    <div style="max-width:500px; margin:60px auto;">
        <div class="card">
            <div class="card-header">Daftarkan Barang Baru</div>
            <div class="card-body">
                <div class="mb-2">
                    <label>Nama Produk</label>
                    <input type="text" id="qa-name" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Barcode</label>
                    <input type="text" id="qa-barcode" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Harga</label>
                    <input type="number" id="qa-price" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Stok Awal</label>
                    <input type="number" id="qa-stock" class="form-control" value="0">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary" id="qa-cancel">Batal</button>
                    <button class="btn btn-primary" id="qa-save">Simpan & Tambah ke Keranjang</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Select Product Overlay -->
<div id="select-overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1050;">
    <div style="max-width:600px; margin:60px auto;">
        <div class="card">
            <div class="card-header">Pilih Produk</div>
            <div class="card-body" id="select-list">
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-secondary" id="select-cancel">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openQuickAddOverlay(prefillBarcode = '') {
        $('#qa-name').val('');
        $('#qa-barcode').val(prefillBarcode || '');
        $('#qa-price').val('');
        $('#qa-stock').val(0);
        $('#quick-add-overlay').show();
    }
    $('#qa-cancel').on('click', function() {
        $('#quick-add-overlay').hide();
    });
    $('#qa-save').on('click', function() {
        const payload = {
            _token: '{{ csrf_token() }}',
            name: $('#qa-name').val(),
            barcode: $('#qa-barcode').val(),
            price: parseInt($('#qa-price').val()) || 0,
            stock: parseInt($('#qa-stock').val()) || 0
        };
        if (!payload.name || !payload.price) {
            alert('Nama dan harga wajib diisi');
            return;
        }
        $.post('{{ route("pos.quickStore") }}', payload)
            .done(function(res) {
                if (res.status === 'success') {
                    addToCart(res.data);
                    $('#quick-add-overlay').hide();
                } else {
                    alert('Gagal menambahkan produk');
                }
            })
            .fail(function(err) {
                alert('Terjadi kesalahan saat menyimpan');
                console.error(err);
            });
    });

    function openSelectOverlay(products) {
        const container = $('#select-list');
        container.empty();
        products.forEach(p => {
            const row = $(`
                <div class="d-flex justify-content-between align-items-center border p-2 mb-2">
                    <div>
                        <div><strong>${p.name}</strong></div>
                        <div class="text-muted">${p.barcode || '-'}</div>
                    </div>
                    <div>Rp ${p.price?.toLocaleString('id-ID') || '0'}</div>
                    <button class="btn btn-sm btn-primary">Pilih</button>
                </div>
            `);
            row.find('button').on('click', function() {
                addToCart(p);
                $('#select-overlay').hide();
            });
            container.append(row);
        });
        $('#select-overlay').show();
    }
    $('#select-cancel').on('click', function() {
        $('#select-overlay').hide();
    });
</script>
@endsection
