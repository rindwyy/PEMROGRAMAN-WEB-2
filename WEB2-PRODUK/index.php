<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Inventaris Produk</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #121212; color: #e0e0e0; margin: 0; padding: 40px; display: flex; justify-content: center; }
        .container { width: 100%; max-width: 900px; background-color: #1e1e1e; padding: 25px; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
        h2 { margin-top: 0; color: #ffffff; border-bottom: 2px solid #333; padding-bottom: 10px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; border: 1px solid #444; border-radius: 8px; background-color: #2a2a2a; color: #fff; box-sizing: border-box; }
        input:focus { outline: none; border-color: #4CAF50; }
        
        button { background-color: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; margin-bottom: 10px;}
        button:hover { background-color: #45a049; }
        button.btn-cancel { background-color: #555; display: none; }
        button.btn-cancel:hover { background-color: #777; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #333; }
        th { background-color: #2c2c2c; color: #ffffff; }
        tbody tr:hover { background-color: #2a2a2a; }
        .price { font-weight: bold; color: #4CAF50; }
        
        /* Tombol Aksi di Tabel */
        .btn-action { padding: 6px 12px; border-radius: 4px; font-size: 12px; border: none; cursor: pointer; margin-right: 5px; color: white; font-weight: bold; width: auto; }
        .btn-edit { background-color: #2196F3; }
        .btn-edit:hover { background-color: #0b7dda; }
        .btn-delete { background-color: #f44336; }
        .btn-delete:hover { background-color: #da190b; }
        
        .status { text-align: center; color: #888; font-style: italic; padding: 20px; }
    </style>
</head>
<body>

    <div class="container">
        <h2 id="form-title">Tambah Produk Baru</h2>
        
        <form id="form-tambah">
            <input type="hidden" id="edit-id">
            
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
            <button type="button" id="btn-cancel" class="btn-cancel" onclick="batalEdit()">Batal Edit</button>
        </form>

        <h2 style="margin-top: 40px;">Daftar Produk</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Produk</th>
                    <th>Deskripsi</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="product-list">
                <tr><td colspan="5" class="status" id="loading-text">Mengambil data dari API...</td></tr>
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
                    tableBody.innerHTML = '<tr><td colspan="5" class="status">Belum ada data produk.</td></tr>';
                    return;
                }

                products.forEach(product => {
                    const row = document.createElement('tr');
                    const formattedPrice = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0}).format(product.price);
                    
                    // Mengamankan string deskripsi jika kosong atau ada tanda kutip
                    const safeName = product.name.replace(/'/g, "\\'");
                    const safeDesc = (product.description || '').replace(/'/g, "\\'");

                    row.innerHTML = `
                        <td>${product.id}</td>
                        <td><strong>${product.name}</strong></td>
                        <td>${product.description || '-'}</td>
                        <td class="price">${formattedPrice}</td>
                        <td>
                            <button class="btn-action btn-edit" onclick="siapkanEdit(${product.id}, '${safeName}', '${safeDesc}', ${product.price})">Edit</button>
                            <button class="btn-action btn-delete" onclick="hapusProduk(${product.id})">Hapus</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="5" class="status" style="color: #ff5252;">Gagal memuat data.</td></tr>`;
            }
        }

        // 2. Fungsi Tambah & Ubah Data (Create & Update)
        document.getElementById('form-tambah').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btnSubmit = document.getElementById('btn-submit');
            const editId = document.getElementById('edit-id').value; // Cek apakah ini mode edit
            
            btnSubmit.innerText = 'Menyimpan...';
            btnSubmit.disabled = true;

            const productData = {
                name: document.getElementById('name').value,
                description: document.getElementById('description').value,
                price: parseInt(document.getElementById('price').value)
            };

            // Jika ada editId, gunakan method PUT, jika tidak gunakan POST
            const fetchMethod = editId ? 'PUT' : 'POST';
            const fetchUrl = editId ? `${API_URL}/${editId}` : API_URL;

            try {
                const response = await fetch(fetchUrl, {
                    method: fetchMethod,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(productData)
                });

                if (response.ok) {
                    alert(editId ? 'Data berhasil diperbarui!' : 'Produk berhasil ditambahkan!');
                    batalEdit(); // Reset form
                    fetchProducts(); // Refresh tabel
                } else {
                    alert('Gagal menyimpan data. Periksa kembali form Anda.');
                }
            } catch (error) {
                alert('Terjadi kesalahan sistem: ' + error.message);
            } finally {
                btnSubmit.disabled = false;
            }
        });

        // 3. Fungsi Menyiapkan Form untuk Mode Edit
        function siapkanEdit(id, name, description, price) {
            document.getElementById('edit-id').value = id;
            document.getElementById('name').value = name;
            document.getElementById('description').value = description;
            document.getElementById('price').value = price;
            
            document.getElementById('form-title').innerText = 'Edit Produk (ID: ' + id + ')';
            document.getElementById('btn-submit').innerText = 'Update Produk';
            document.getElementById('btn-submit').style.backgroundColor = '#2196F3';
            document.getElementById('btn-cancel').style.display = 'block'; // Tampilkan tombol batal
            
            window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll ke atas otomatis
        }

        // 4. Fungsi Kembalikan Form ke Mode Tambah
        function batalEdit() {
            document.getElementById('form-tambah').reset();
            document.getElementById('edit-id').value = '';
            
            document.getElementById('form-title').innerText = 'Tambah Produk Baru';
            document.getElementById('btn-submit').innerText = 'Simpan Produk';
            document.getElementById('btn-submit').style.backgroundColor = '#4CAF50';
            document.getElementById('btn-cancel').style.display = 'none';
        }

        // 5. Fungsi Hapus Data (Delete)
        async function hapusProduk(id) {
            // Tampilkan konfirmasi ke user
            const konfirmasi = confirm('Apakah Anda yakin ingin menghapus produk ini?');
            if (!konfirmasi) return;

            try {
                const response = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    fetchProducts(); // Refresh tabel jika sukses
                } else {
                    alert('Gagal menghapus produk.');
                }
            } catch (error) {
                alert('Terjadi kesalahan saat menghapus: ' + error.message);
            }
        }

        window.addEventListener('DOMContentLoaded', fetchProducts);
    </script>

</body>
</html>