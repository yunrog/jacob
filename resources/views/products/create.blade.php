<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #eef2ff 0%, #ecfeff 100%); }
        .form-card { max-width: 900px; margin: 60px auto; border: 0; border-radius: 28px; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card form-card p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h1 class="h3 fw-bold mb-1">Tambah Barang</h1>
                    <p class="text-muted mb-0">Masukkan data barang baru ke katalog.</p>
                </div>
                <a href="{{ old('return_to') === 'cashier' || request('return_to') === 'cashier' ? route('cashier.index') : route('products.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>

            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                @if (old('return_to') === 'cashier' || request('return_to') === 'cashier')
                    <input type="hidden" name="return_to" value="cashier">
                @endif

                <div class="col-md-6">
                    <label for="name" class="form-label fw-semibold">Nama Barang</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control form-control-lg" placeholder="Masukkan nama barang" required>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="category_id" class="form-label fw-semibold">Kategori</label>
                    <select id="category_id" name="category_id" class="form-select form-select-lg">
                        <option value="">Pilih kategori (opsional)</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="brand_id" class="form-label fw-semibold">Merek</label>
                    <select id="brand_id" name="brand_id" class="form-select form-select-lg">
                        <option value="">Pilih merek (opsional)</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="stock" class="form-label fw-semibold">Stok</label>
                    <input type="number" id="stock" name="stock" value="{{ old('stock') }}" class="form-control form-control-lg" placeholder="0" min="0" required>
                    @error('stock')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="price" class="form-label fw-semibold">Harga (Rp)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="{{ old('price') }}" class="form-control form-control-lg" placeholder="0" required>
                    @error('price')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="image" class="form-label fw-semibold">Foto Barang (opsional)</label>
                    <input type="file" id="image" name="image" accept="image/*" class="form-control form-control-lg">
                    @error('image')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label fw-semibold">Deskripsi (opsional)</label>
                    <textarea id="description" name="description" rows="4" class="form-control" placeholder="Keterangan singkat barang">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-lg">Simpan Barang</button>
                    <a href="{{ old('return_to') === 'cashier' || request('return_to') === 'cashier' ? route('cashier.index') : route('products.index') }}" class="btn btn-outline-secondary btn-lg">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
