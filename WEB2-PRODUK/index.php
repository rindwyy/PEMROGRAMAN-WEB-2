<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Inventaris Produk</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
        }
        .container {
            width: 100%;
            max-width: 800px;
            background-color: #1e1e1e;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        h2 { margin-top: 0; color: #ffffff; border-bottom: 2px solid #333; padding-bottom: 10px; }
        
        /* CSS Form Input */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #444;
            border-radius: 8px;
            background-color: #2a2a2a;
            color: #fff;
            box-sizing: border-box; /* Agar padding tidak merusak lebar */
        }
        input:focus { outline: none; border-color: #4CAF50; }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
        }
        button:hover { background-color: #45a049; }
        button:disabled { background-color: #555; cursor: not-allowed; }

        /* CSS Tabel */
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #333; }
        th { background-color: #2c2c2c; color: #ffffff; }
        tbody tr:hover { background-color: #2a2a2a; }
        .price { font-weight: bold; color: #4CAF50; }
        .status { text-align: center; color: #888; font-style: italic; padding: 20px; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Tambah Produk Baru</h2>
        
        <form id="form-tambah">
            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input type="text" id="name" required placeholder="Contoh: Kacamata Stellar">
            </div>
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <input type="text" id="description" placeholder="Deskripsi singkat produk">
            </div>
            <div class="form-group">
                <label for="price">Harga (Rp)</label>
                <input type="number" id="price" required min="0" placeholder="Contoh: 150000">
            </div>
            <button type="submit" id="btn-submit">Simpan Produk</button>
        </form>

        <h2 style="margin-top: 40px;">Daftar Produk</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Produk</th>
                    <th>Deskripsi</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody id="product-list">
                <tr><td colspan="4" class="status" id="loading-text">Mengambil data dari API...</td></tr>
            </tbody>
        </table>
    </div>

    <script>
        const API_URL = 'http://localhost:8000/products';

        // 1. Fungsi Tampilkan Data (Read)
        async function fetchProducts() {
            const tableBody = document.getElementById('product-list');
            try {
                const response = await fetch(API_URL);
                const result = await response.json();
                const products = result.data;
                
                tableBody.innerHTML = '';
                if (products.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" class="status">Belum ada data produk.</td></tr>';
                    return;
                }

                products.forEach(product => {
                    const row = document.createElement('tr');
                    const formattedPrice = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0}).format(product.price);
                    
                    row.innerHTML = `
                        <td>${product.id}</td>
                        <td><strong>${product.name}</strong></td>
                        <td>${product.description || '-'}</td>
                        <td class="price">${formattedPrice}</td>
                    `;
                    tableBody.appendChild(row);
                });
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="4" class="status" style="color: #ff5252;">Gagal memuat data.</td></tr>`;
            }
        }

        // 2. Fungsi Tambah Data (Create)
        document.getElementById('form-tambah').addEventListener('submit', async function(e) {
            e.preventDefault(); // Mencegah browser melakukan reload halaman
            
            const btnSubmit = document.getElementById('btn-submit');
            btnSubmit.innerText = 'Menyimpan...';
            btnSubmit.disabled = true;

            // Mengambil nilai dari input
            const newProduct = {
                name: document.getElementById('name').value,
                description: document.getElementById('description').value,
                price: parseInt(document.getElementById('price').value)
            };

            try {
                // Mengirim request POST ke API
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json' // Penting untuk memberitahu Laravel bahwa kita mengharapkan balasan JSON
                    },
                    body: JSON.stringify(newProduct)
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Mantap! Produk berhasil ditambahkan.');
                    document.getElementById('form-tambah').reset(); // Kosongkan form
                    fetchProducts(); // Refresh tabel secara otomatis
                } else {
                    alert('Gagal menyimpan: ' + (result.message || 'Cek kembali data kamu.'));
                }
            } catch (error) {
                alert('Terjadi kesalahan sistem: ' + error.message);
            } finally {
                // Kembalikan tombol seperti semula
                btnSubmit.innerText = 'Simpan Produk';
                btnSubmit.disabled = false;
            }
        });

        // Jalankan fetch saat halaman dibuka
        window.addEventListener('DOMContentLoaded', fetchProducts);
    </script>

</body>
</html>