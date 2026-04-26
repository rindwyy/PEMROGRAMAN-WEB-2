<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate; // Import Gate untuk mengecek hak akses

class ProductController extends Controller
{
    protected $productService;

    // Inject ProductService melalui constructor
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * 1. Fungsi Tampilkan Data (Read)
     */
    public function index()
    {
        $products = Product::all();
        
        // Return view atau response JSON (sesuaikan dengan kebutuhan)
        return response()->json([
            'message' => 'Berhasil mengambil data',
            'data' => $products
        ]);
    }

    /**
     * 2. Fungsi Tambah Data (Create)
     * Menggunakan Form Request Validation, Error Handling (Try-Catch), dan Service Pattern
     */
    public function store(StoreProductRequest $request)
    {
        // Cek dulu, apakah user yang request adalah Admin?
        if (!Gate::allows('isAdmin')) {
            return response()->json([
                'message' => 'Akses ditolak. Hanya Admin yang bisa menambah produk.'
            ], 403); // 403 artinya Forbidden (Dilarang)
        }

        try {
            // Data sudah tervalidasi melalui StoreProductRequest
            $validatedData = $request->validated();

            // Memanggil Service untuk memproses penyimpanan
            $product = $this->productService->storeProduct($validatedData);

            return response()->json([
                'message' => 'Produk berhasil ditambahkan',
                'data' => $product
            ], 201);

        } catch (Exception $e) {
            // Error Handling: Menangkap error jika proses service gagal
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. Fungsi Ubah Data (Update)
     */
    public function update(Request $request, $id)
    {
        // Cek dulu, apakah user yang request adalah Admin?
        if (!Gate::allows('isAdmin')) {
            return response()->json([
                'message' => 'Akses ditolak. Hanya Admin yang bisa mengubah produk.'
            ], 403);
        }

        $product = Product::findOrFail($id);
        
        // Validasi sederhana (sebaiknya juga pakai Form Request, tapi ini disederhanakan)
        $validatedData = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'integer|min:0',
        ]);

        $product->update($validatedData);

        return response()->json([
            'message' => 'Produk berhasil diperbarui',
            'data' => $product
        ]);
    }

    /**
     * 4. Fungsi Hapus Data (Delete)
     */
    public function destroy($id)
    {
        // Cek dulu, apakah user yang request adalah Admin?
        if (!Gate::allows('isAdmin')) {
            return response()->json([
                'message' => 'Akses ditolak. Hanya Admin yang bisa menghapus produk.'
            ], 403);
        }

        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
