<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%); }
        .detail-card { max-width: 900px; margin: 60px auto; border-radius: 24px; border: 0; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card detail-card p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <h1 class="h3 fw-bold mb-0">Product Detail</h1>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back to Products</a>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded-4 border shadow-sm" style="max-height: 300px; object-fit: cover; width: 100%;">
                    @else
                        <div class="d-flex align-items-center justify-content-center rounded-4 border bg-light text-muted" style="min-height: 260px;">No image available</div>
                    @endif
                </div>

                <div class="col-md-8">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0">
                            <div class="text-muted small text-uppercase">Name</div>
                            <div class="fs-4 fw-bold">{{ $product->name }}</div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="text-muted small text-uppercase">Category</div>
                            <div>{{ $product->category?->name ?? '-' }}</div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="text-muted small text-uppercase">Brand</div>
                            <div>{{ $product->brand?->name ?? '-' }}</div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="text-muted small text-uppercase">Price</div>
                            <div>Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="text-muted small text-uppercase">Stock</div>
                            <div>{{ $product->stock }}</div>
                        </div>
                        <div class="list-group-item px-0">
                            <div class="text-muted small text-uppercase">Description</div>
                            <div>{{ $product->description ?: 'No description provided.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
