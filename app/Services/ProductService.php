<?php

namespace App\Services;

use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Log;

class ProductService
{
    /**
     * Menyimpan data produk baru
     */
    public function storeProduct(array $data)
    {
        try {
            // Proses menyimpan data ke database
            $product = Product::create($data);
            
            return $product;
        } catch (Exception $e) {
            // Jika terjadi error, catat di log dan lempar kembali errornya
            Log::error('Error saat menyimpan produk: ' . $e->getMessage());
            throw new Exception('Gagal menyimpan data produk. Silakan coba lagi.');
        }
    }
}
