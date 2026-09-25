<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); }
        .form-card { max-width: 900px; margin: 60px auto; border: 0; border-radius: 28px; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card form-card p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h1 class="h3 fw-bold mb-1">Edit Product</h1>
                    <p class="text-muted mb-0">Update product details and inventory info.</p>
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label for="name" class="form-label fw-semibold">Product Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" class="form-control form-control-lg" placeholder="Enter product name">
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="category_id" class="form-label fw-semibold">Category</label>
                    <select id="category_id" name="category_id" class="form-select form-select-lg">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="brand_id" class="form-label fw-semibold">Brand</label>
                    <select id="brand_id" name="brand_id" class="form-select form-select-lg">
                        <option value="">Select brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="stock" class="form-label fw-semibold">Stock</label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" class="form-control form-control-lg" placeholder="0">
                    @error('stock')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="price" class="form-label fw-semibold">Price</label>
                    <input type="number" id="price" name="price" step="0.01" value="{{ old('price', $product->price) }}" class="form-control form-control-lg" placeholder="0">
                    @error('price')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="image" class="form-label fw-semibold">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*" class="form-control form-control-lg">
                    @if($product->image)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="Current product image" class="img-thumbnail" style="max-width: 120px;">
                        </div>
                    @endif
                    @error('image')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea id="description" name="description" rows="4" class="form-control" placeholder="Short product description...">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-lg">Update Product</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
