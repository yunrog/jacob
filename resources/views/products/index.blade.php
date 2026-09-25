<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%); }
        .top-card { border: 0; border-radius: 24px; box-shadow: 0 14px 35px rgba(15, 23, 42, 0.08); }
        .product-thumb { width: 56px; height: 56px; object-fit: cover; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; }
        .empty-state { padding: 40px 20px; text-align: center; color: #64748b; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card top-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-1 fw-bold">Product Management</h1>
                    <p class="text-muted mb-0">Track products, stock, categories, and brand performance.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-primary rounded-pill px-3 py-2">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-dark">Logout</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card top-card p-4 mb-4">
            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <h2 class="h4 mb-0 fw-bold">Products</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Dashboard</a>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">+ Add Product</a>
                </div>
            </div>

            <form method="GET" action="{{ route('products.index') }}" class="row g-3 mb-4">
                <div class="col-md-10">
                    <input type="text" name="search" value="{{ old('search', $search ?? '') }}" class="form-control form-control-lg" placeholder="Search by name, category, brand, or description...">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-thumb">
                                    @else
                                        <div class="product-thumb d-flex align-items-center justify-content-center text-muted small">No image</div>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $product->name }}</td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
                                <td>{{ $product->brand?->name ?? '-' }}</td>
                                <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                <td>{{ $product->stock }}</td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">No products found for this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
