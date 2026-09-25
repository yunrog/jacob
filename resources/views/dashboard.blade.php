<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5f7ff 0%, #eef7f4 100%); }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #0f172a 0%, #111827 100%); color: white; }
        .nav-link { color: rgba(255,255,255,.75); border-radius: 10px; padding: .7rem .9rem; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(148, 163, 184, 0.16); }
        .stat-card { border-radius: 20px; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); border: 0; }
        .chart-card { border: 0; border-radius: 20px; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); }
        .chart-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .chart-label { width: 100px; font-weight: 600; color: #475569; }
        .chart-bar-wrap { flex: 1; height: 14px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
        .chart-bar { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #4f46e5 0%, #22c55e 100%); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-2 sidebar p-4">
                <h3 class="mb-4 fw-bold">Jacobs Admin</h3>
                <nav class="nav flex-column gap-2">
                    <a class="nav-link active" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="nav-link" href="{{ route('cashier.index') }}">Kasir POS</a>
                    <a class="nav-link" href="{{ route('products.index') }}">Products</a>
                    <a class="nav-link" href="{{ route('categories.index') }}">Categories</a>
                    <a class="nav-link" href="{{ route('brands.index') }}">Brands</a>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button class="btn btn-light w-100" type="submit">Logout</button>
                    </form>
                </nav>
            </aside>

            <main class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0 fw-bold">Dashboard</h2>
                        <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }}</p>
                    </div>
                    <a href="{{ route('products.create') }}" class="btn btn-primary px-4 py-2">+ Add Product</a>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card p-3">
                            <div class="text-muted small">Products</div>
                            <h3 class="mt-2 mb-0 fw-bold">{{ $stats['products'] }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card p-3">
                            <div class="text-muted small">Categories</div>
                            <h3 class="mt-2 mb-0 fw-bold">{{ $stats['categories'] }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card p-3">
                            <div class="text-muted small">Brands</div>
                            <h3 class="mt-2 mb-0 fw-bold">{{ $stats['brands'] }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card p-3">
                            <div class="text-muted small">Total Stock</div>
                            <h3 class="mt-2 mb-0 fw-bold">{{ $stats['stock'] }}</h3>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-5">
                        <div class="card chart-card p-4 h-100">
                            <h5 class="fw-bold mb-3">Stock by Category</h5>
                            @if($chartData->isNotEmpty())
                                @foreach($chartData as $item)
                                    @php
                                        $width = $maxChartValue > 0 ? ($item['value'] / $maxChartValue) * 100 : 0;
                                    @endphp
                                    <div class="chart-row">
                                        <div class="chart-label">{{ Str::limit($item['label'], 10) }}</div>
                                        <div class="chart-bar-wrap">
                                            <div class="chart-bar" style="width: {{ $width }}%"></div>
                                        </div>
                                        <strong>{{ $item['value'] }}</strong>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted mb-0">No stock data yet.</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card chart-card p-4">
                            <h5 class="fw-bold mb-3">Recent Products</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Brand</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($latestProducts as $product)
                                            <tr>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->category?->name ?? '-' }}</td>
                                                <td>{{ $product->brand?->name ?? '-' }}</td>
                                                <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                                <td>{{ $product->stock }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No products yet</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
