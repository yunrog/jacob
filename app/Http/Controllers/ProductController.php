<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    // Menampilkan daftar produk
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    // Menampilkan form tambah produk
    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all(); // Mengambil data brand untuk view create
        return view('products.create', compact('categories', 'brands'));
    }

    // Menyimpan data produk & memproses gambar (Acara 29)
    public function store(Request $request)
    {
        // 1. Validasi Input Form
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',
            'sku'         => 'required|string|max:100|unique:products,sku',
            'price'       => 'required|numeric',
            'stock'       => 'required|integer',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $file = $request->file('image');

        // 2. Manipulasi Gambar Menggunakan Intervention Image
        if ($file) {
            $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $original) . '.' . $ext;
            $folder = 'products';
            $path = $folder . '/' . $filename;

            // Inisialisasi ImageManager dengan GD Driver
            $manager = new ImageManager(Driver::class);

            // Resize & Crop Gambar ke 800x800
            $image = $manager->read($file->getRealPath())->cover(800, 800);

            // Encode Format Gambar
            if ($ext === 'png') {
                $encoded = $image->toPng();
            } else {
                $encoded = $image->toJpeg(85);
            }

            // Simpan ke storage public
            Storage::disk('public')->put($path, (string) $encoded);

            $data['image'] = $path;
        }

        // 3. Simpan Data ke Database
        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }
}