<style>
    /* Container Search agar bisa diatur posisinya di navbar */
    .search-container {
        display: flex;
        align-items: center;
        /* KUNCI: Agar sejajar vertikal dengan logo/menu */
        width: 100%;
        margin-bottom: 0;
        /* Hapus margin bawah agar tidak mendorong layout */
    }

    .search-form {
        display: flex;
        align-items: center;
        /* Pastikan input & tombol sejajar */
        width: 100%;
        max-width: 350px;
        /* Lebar maksimal diperkecil sedikit agar proporsional */

        border: 1px solid #ccc;
        border-radius: 8px;
        /* Sudut sedikit lebih tajam agar terlihat modern */
        background: #fff;
        overflow: hidden;
        height: 40px;
        /* KUNCI: Tinggi fix agar ramping */
        transition: box-shadow 0.3s ease, border-color 0.3s ease;
    }

    .search-form:focus-within {
        border-color: #555;
    }

    .search-input {
        flex: 1;
        padding: 0 15px;
        /* Padding kiri kanan saja */
        height: 100%;
        /* Mengikuti tinggi form */
        border: none;
        outline: none;
        font-size: 0.9rem;
        /* Font sedikit lebih kecil */
        color: #333;
        background: transparent;
    }

    .search-input::placeholder {
        color: #aaa;
        font-size: 0.85rem;
    }

    .search-button {
        background: transparent;
        color: #555;
        border: none;
        border-left: 1px solid #eee;
        /* Garis pemisah tipis */

        height: 100%;
        /* Mengikuti tinggi form */
        padding: 0 15px;
        cursor: pointer;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .search-button:hover {
        background: #f9f9f9;
        color: #000;
    }
</style>

<div class="search-container">
    <form action="" method="GET" class="search-form">
        <input
            type="text"
            name="keyword"
            class="search-input"
            placeholder="Search..."
            value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>"
            autocomplete="off">
        <button type="submit" class="search-button">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>
</div>