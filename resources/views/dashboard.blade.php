<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Store Order & Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
        }
        .navbar {
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .badge-low-stock {
            background-color: #fef3c7;
            color: #b45309;
            font-weight: 600;
        }
        .badge-out-stock {
            background-color: #fee2e2;
            color: #b91c1c;
            font-weight: 600;
        }
        .badge-in-stock {
            background-color: #dcfce7;
            color: #15803d;
            font-weight: 600;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="/">
                <i class="bi bi-shop me-2 fs-4"></i> Retail Counter & Inventory System
            </a>
            <span class="badge bg-primary px-3 py-2">Laravel 10 • MySQL • Queue</span>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        <ul class="nav nav-pills mb-4" id="mainTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4 py-2 fw-semibold" id="order-tab" data-bs-toggle="pill" data-bs-target="#order-pane" type="button" role="tab">
                    <i class="bi bi-cart-plus me-1"></i> New Order (POS)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4 py-2 fw-semibold" id="inventory-tab" data-bs-toggle="pill" data-bs-target="#inventory-pane" type="button" role="tab">
                    <i class="bi bi-boxes me-1"></i> Inventory & Stock
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4 py-2 fw-semibold" id="history-tab" data-bs-toggle="pill" data-bs-target="#history-pane" type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i> Order History Search
                </button>
            </li>
        </ul>

        <div id="alertBox" class="alert d-none" role="alert"></div>

        <div class="tab-content" id="mainTabContent">
            <div class="tab-pane fade show active" id="order-pane" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-3"><i class="bi bi-person-circle me-2"></i>Customer Details</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" id="custName" class="form-control" placeholder="e.g. Rohit Patil" value="Rohit Patil">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Customer Email <span class="text-danger">*</span></label>
                                    <input type="email" id="custEmail" class="form-control" placeholder="e.g. rohit@example.com" value="rohit@example.com">
                                </div>
                            </div>

                            <h5 class="fw-bold mb-3"><i class="bi bi-cart-check me-2"></i>Add Items to Order</h5>
                            <div class="row g-2 mb-3">
                                <div class="col-md-7">
                                    <select id="itemProductSelect" class="form-select">
                                        <option value="">-- Choose Product --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    data-name="{{ $product->name }}" 
                                                    data-code="{{ $product->code }}" 
                                                    data-price="{{ $product->price }}" 
                                                    data-tax="{{ $product->tax_percentage }}" 
                                                    data-stock="{{ $product->stock_quantity }}">
                                                {{ $product->name }} ({{ $product->code }}) - ₹{{ number_format($product->price, 2) }} [Stock: {{ $product->stock_quantity }}]
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" id="itemQuantity" class="form-control" min="1" value="1" placeholder="Qty">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-primary w-100" id="addItemBtn">
                                        <i class="bi bi-plus-lg"></i> Add
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-hover align-middle" id="orderItemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-end">Price (₹)</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Tax %</th>
                                            <th class="text-end">Tax (₹)</th>
                                            <th class="text-end">Total (₹)</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderItemsList">
                                        <tr id="emptyItemsRow">
                                            <td colspan="7" class="text-center text-muted py-4">No items added to order yet.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item d-flex justify-content-between px-0 py-3">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="fw-semibold" id="summarySubtotal">₹0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-3">
                                    <span class="text-muted">Total Tax Amount:</span>
                                    <span class="fw-semibold" id="summaryTax">₹0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-3 border-top border-2">
                                    <strong class="fs-5">Grand Total:</strong>
                                    <strong class="fs-5 text-success" id="summaryGrandTotal">₹0.00</strong>
                                </li>
                            </ul>

                            <button id="submitOrderBtn" class="btn btn-success btn-lg w-100 py-3 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Complete & Place Order
                            </button>

                            <div class="mt-4 p-3 bg-light rounded text-muted small">
                                <i class="bi bi-shield-check text-primary me-1"></i>
                                Inventory is protected against overselling using database transactions and pessimistic locking (<code class="text-dark">lockForUpdate</code>). Order confirmation email is simulated via queued jobs.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="inventory-pane" role="tabpanel">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="bi bi-boxes me-2"></i>Stock Inventory</h5>
                            <p class="text-muted mb-0 small">Real-time product catalog and available stock levels.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 fw-semibold small text-nowrap">Low Stock Filter (<):</label>
                            <input type="number" id="filterThreshold" class="form-control form-control-sm" style="width: 80px;" value="5" min="0">
                            <button id="filterLowStockBtn" class="btn btn-sm btn-outline-warning text-dark text-nowrap">
                                <i class="bi bi-filter"></i> Low Stock
                            </button>
                            <button id="resetInventoryBtn" class="btn btn-sm btn-outline-secondary text-nowrap">
                                <i class="bi bi-arrow-clockwise"></i> Reset All
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Code</th>
                                    <th>Name</th>
                                    <th class="text-end">Price (₹)</th>
                                    <th class="text-end">Tax Rate</th>
                                    <th class="text-center">Stock Available</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTableBody">
                                @foreach($products as $product)
                                    <tr data-stock="{{ $product->stock_quantity }}">
                                        <td><code>{{ $product->code }}</code></td>
                                        <td class="fw-semibold">{{ $product->name }}</td>
                                        <td class="text-end">₹{{ number_format($product->price, 2) }}</td>
                                        <td class="text-end">{{ number_format($product->tax_percentage, 2) }}%</td>
                                        <td class="text-center fw-bold">{{ $product->stock_quantity }}</td>
                                        <td class="text-center">
                                            @if($product->stock_quantity === 0)
                                                <span class="badge badge-out-stock px-2 py-1">Out of Stock</span>
                                            @elseif($product->stock_quantity < 5)
                                                <span class="badge badge-low-stock px-2 py-1">Low Stock (< 5)</span>
                                            @else
                                                <span class="badge badge-in-stock px-2 py-1">In Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="history-pane" role="tabpanel">
                <div class="card p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Customer Order History</h5>
                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <input type="email" id="historyEmailInput" class="form-control" placeholder="Search by customer email (e.g. rohit@example.com)" value="rohit@example.com">
                        </div>
                        <div class="col-md-2">
                            <button id="searchHistoryBtn" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div id="historyResultsContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const cart = [];

        function showAlert(message, type = 'success') {
            const alertBox = document.getElementById('alertBox');
            alertBox.className = `alert alert-${type} alert-dismissible fade show`;
            alertBox.innerHTML = `
                <div>${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertBox.classList.remove('d-none');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function updateCartSummary() {
            let totalSubtotal = 0;
            let totalTax = 0;

            const tbody = document.getElementById('orderItemsList');
            if (cart.length === 0) {
                tbody.innerHTML = '<tr id="emptyItemsRow"><td colspan="7" class="text-center text-muted py-4">No items added to order yet.</td></tr>';
                document.getElementById('summarySubtotal').innerText = '₹0.00';
                document.getElementById('summaryTax').innerText = '₹0.00';
                document.getElementById('summaryGrandTotal').innerText = '₹0.00';
                return;
            }

            tbody.innerHTML = '';
            cart.forEach((item, index) => {
                const lineSubtotal = Math.round((item.price * item.quantity) * 100) / 100;
                const lineTax = Math.round((lineSubtotal * item.tax / 100) * 100) / 100;
                const lineTotal = Math.round((lineSubtotal + lineTax) * 100) / 100;

                totalSubtotal += lineSubtotal;
                totalTax += lineTax;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div class="fw-semibold">${item.name}</div>
                        <small class="text-muted">${item.code}</small>
                    </td>
                    <td class="text-end">₹${item.price.toFixed(2)}</td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm text-center mx-auto" style="width: 75px;" min="1" value="${item.quantity}" onchange="changeItemQty(${index}, this.value)">
                    </td>
                    <td class="text-end">${item.tax.toFixed(2)}%</td>
                    <td class="text-end">₹${lineTax.toFixed(2)}</td>
                    <td class="text-end fw-bold">₹${lineTotal.toFixed(2)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${index})"><i class="bi bi-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            totalSubtotal = Math.round(totalSubtotal * 100) / 100;
            totalTax = Math.round(totalTax * 100) / 100;
            const grandTotal = Math.round((totalSubtotal + totalTax) * 100) / 100;

            document.getElementById('summarySubtotal').innerText = `₹${totalSubtotal.toFixed(2)}`;
            document.getElementById('summaryTax').innerText = `₹${totalTax.toFixed(2)}`;
            document.getElementById('summaryGrandTotal').innerText = `₹${grandTotal.toFixed(2)}`;
        }

        function changeItemQty(index, newQty) {
            const qty = parseInt(newQty, 10);
            if (isNaN(qty) || qty < 1) {
                cart[index].quantity = 1;
            } else {
                cart[index].quantity = qty;
            }
            updateCartSummary();
        }

        function removeItem(index) {
            cart.splice(index, 1);
            updateCartSummary();
        }

        document.getElementById('addItemBtn').addEventListener('click', () => {
            const select = document.getElementById('itemProductSelect');
            const qtyInput = document.getElementById('itemQuantity');
            const productId = parseInt(select.value, 10);
            const quantity = parseInt(qtyInput.value, 10);

            if (!productId) {
                alert('Please select a product.');
                return;
            }

            if (isNaN(quantity) || quantity < 1) {
                alert('Please enter a valid quantity of at least 1.');
                return;
            }

            const selectedOption = select.options[select.selectedIndex];
            const name = selectedOption.getAttribute('data-name');
            const code = selectedOption.getAttribute('data-code');
            const price = parseFloat(selectedOption.getAttribute('data-price'));
            const tax = parseFloat(selectedOption.getAttribute('data-tax'));
            const stock = parseInt(selectedOption.getAttribute('data-stock'), 10);

            const existingIndex = cart.findIndex(item => item.product_id === productId);
            if (existingIndex > -1) {
                cart[existingIndex].quantity += quantity;
            } else {
                cart.push({
                    product_id: productId,
                    name: name,
                    code: code,
                    price: price,
                    tax: tax,
                    stock: stock,
                    quantity: quantity
                });
            }

            qtyInput.value = 1;
            select.value = '';
            updateCartSummary();
        });

        document.getElementById('submitOrderBtn').addEventListener('click', async () => {
            const customerName = document.getElementById('custName').value.trim();
            const customerEmail = document.getElementById('custEmail').value.trim();

            if (!customerName || !customerEmail) {
                showAlert('Please provide both customer name and email.', 'danger');
                return;
            }

            if (cart.length === 0) {
                showAlert('Please add at least one product to the order.', 'warning');
                return;
            }

            const payload = {
                customer: {
                    name: customerName,
                    email: customerEmail
                },
                items: cart.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity
                }))
            };

            const submitBtn = document.getElementById('submitOrderBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing Order...';

            try {
                const response = await fetch('/api/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showAlert(`Order #${result.data.id} placed successfully! Grand Total: ₹${result.data.grand_total}`, 'success');
                    cart.length = 0;
                    updateCartSummary();
                    refreshInventoryData();
                } else {
                    let errMsg = result.message || 'Failed to place order.';
                    if (result.errors) {
                        const messages = Object.values(result.errors).flat();
                        errMsg += ' ' + messages.join(' ');
                    }
                    showAlert(errMsg, 'danger');
                }
            } catch (err) {
                showAlert('An unexpected error occurred while communicating with the server.', 'danger');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Complete & Place Order';
            }
        });

        async function refreshInventoryData() {
            try {
                const response = await fetch('/api/products');
                const result = await response.json();
                if (result.success && Array.isArray(result.data)) {
                    const select = document.getElementById('itemProductSelect');
                    select.innerHTML = '<option value="">-- Choose Product --</option>';
                    const tbody = document.getElementById('inventoryTableBody');
                    tbody.innerHTML = '';

                    result.data.forEach(p => {
                        const option = document.createElement('option');
                        option.value = p.id;
                        option.setAttribute('data-name', p.name);
                        option.setAttribute('data-code', p.code);
                        option.setAttribute('data-price', p.price);
                        option.setAttribute('data-tax', p.tax_percentage);
                        option.setAttribute('data-stock', p.stock_quantity);
                        option.innerText = `${p.name} (${p.code}) - ₹${parseFloat(p.price).toFixed(2)} [Stock: ${p.stock_quantity}]`;
                        select.appendChild(option);

                        let badgeHtml = '<span class="badge badge-in-stock px-2 py-1">In Stock</span>';
                        if (p.stock_quantity === 0) {
                            badgeHtml = '<span class="badge badge-out-stock px-2 py-1">Out of Stock</span>';
                        } else if (p.stock_quantity < 5) {
                            badgeHtml = '<span class="badge badge-low-stock px-2 py-1">Low Stock (< 5)</span>';
                        }

                        const tr = document.createElement('tr');
                        tr.setAttribute('data-stock', p.stock_quantity);
                        tr.innerHTML = `
                            <td><code>${p.code}</code></td>
                            <td class="fw-semibold">${p.name}</td>
                            <td class="text-end">₹${parseFloat(p.price).toFixed(2)}</td>
                            <td class="text-end">${parseFloat(p.tax_percentage).toFixed(2)}%</td>
                            <td class="text-center fw-bold">${p.stock_quantity}</td>
                            <td class="text-center">${badgeHtml}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            } catch (e) {}
        }

        document.getElementById('filterLowStockBtn').addEventListener('click', async () => {
            const threshold = document.getElementById('filterThreshold').value || 5;
            try {
                const response = await fetch(`/api/products/low-stock?threshold=${threshold}`);
                const result = await response.json();
                if (result.success) {
                    const tbody = document.getElementById('inventoryTableBody');
                    tbody.innerHTML = '';
                    if (result.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No products below the specified threshold.</td></tr>';
                        return;
                    }
                    result.data.forEach(p => {
                        let badgeHtml = '<span class="badge badge-low-stock px-2 py-1">Low Stock (< ' + threshold + ')</span>';
                        if (p.stock_quantity === 0) {
                            badgeHtml = '<span class="badge badge-out-stock px-2 py-1">Out of Stock</span>';
                        }

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><code>${p.code}</code></td>
                            <td class="fw-semibold">${p.name}</td>
                            <td class="text-end">₹${parseFloat(p.price).toFixed(2)}</td>
                            <td class="text-end">${parseFloat(p.tax_percentage).toFixed(2)}%</td>
                            <td class="text-center fw-bold">${p.stock_quantity}</td>
                            <td class="text-center">${badgeHtml}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            } catch (e) {}
        });

        document.getElementById('resetInventoryBtn').addEventListener('click', () => {
            refreshInventoryData();
        });

        document.getElementById('searchHistoryBtn').addEventListener('click', async () => {
            const email = document.getElementById('historyEmailInput').value.trim();
            const container = document.getElementById('historyResultsContainer');

            if (!email) {
                container.innerHTML = '<div class="alert alert-warning">Please enter an email address to search.</div>';
                return;
            }

            container.innerHTML = '<div class="text-center py-4"><span class="spinner-border text-primary"></span> Loading history...</div>';

            try {
                const response = await fetch(`/api/customers/orders?email=${encodeURIComponent(email)}`);
                const result = await response.json();

                if (!response.ok || !result.success) {
                    container.innerHTML = `<div class="alert alert-danger">${result.message || 'No orders found for this customer.'}</div>`;
                    return;
                }

                const data = result.data;
                const customer = data.customer;
                const orders = data.orders;

                if (!orders || orders.length === 0) {
                    container.innerHTML = `
                        <div class="alert alert-info">
                            <strong>${customer.name}</strong> (${customer.email}) has no previous orders on record.
                        </div>
                    `;
                    return;
                }

                let html = `
                    <div class="card bg-light p-3 mb-4">
                        <h6 class="fw-bold mb-1"><i class="bi bi-person me-1"></i> ${customer.name}</h6>
                        <small class="text-muted"><i class="bi bi-envelope me-1"></i> ${customer.email} • Total Orders: ${orders.length}</small>
                    </div>
                `;

                orders.forEach(order => {
                    const formattedDate = new Date(order.created_at).toLocaleString();
                    html += `
                        <div class="card mb-3">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <span class="fw-bold text-primary">Order #${order.id}</span>
                                    <span class="text-muted small ms-2"><i class="bi bi-calendar3"></i> ${formattedDate}</span>
                                </div>
                                <div>
                                    <span class="badge bg-success fs-6">₹${parseFloat(order.grand_total).toFixed(2)}</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Unit Price</th>
                                                <th class="text-end">Tax %</th>
                                                <th class="text-end">Tax Amount</th>
                                                <th class="text-end">Line Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;

                    order.order_items.forEach(item => {
                        const prodName = item.product ? item.product.name : `Product #${item.product_id}`;
                        const prodCode = item.product ? `<code>${item.product.code}</code>` : '';
                        html += `
                            <tr>
                                <td>${prodName} ${prodCode}</td>
                                <td class="text-center">${item.quantity}</td>
                                <td class="text-end">₹${parseFloat(item.unit_price).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(item.tax_percentage).toFixed(2)}%</td>
                                <td class="text-end">₹${parseFloat(item.tax_amount).toFixed(2)}</td>
                                <td class="text-end fw-semibold">₹${parseFloat(item.total).toFixed(2)}</td>
                            </tr>
                        `;
                    });

                    html += `
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="5" class="text-end text-muted">Subtotal:</td>
                                                <td class="text-end">₹${parseFloat(order.subtotal).toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="text-end text-muted">Tax Amount:</td>
                                                <td class="text-end">₹${parseFloat(order.tax_amount).toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                                                <td class="text-end fw-bold text-success">₹${parseFloat(order.grand_total).toFixed(2)}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
            } catch (err) {
                container.innerHTML = '<div class="alert alert-danger">Error retrieving order history.</div>';
            }
        });
    </script>
</body>
</html>
