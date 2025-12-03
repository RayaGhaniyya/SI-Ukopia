<style>
    .search-container {
        display: flex;
        align-items: center;
        width: 100%;
        margin-bottom: 0;
    }

    .search-form {
        display: flex;
        align-items: center;
        width: 100%;
        max-width: 350px;

        border: 1px solid #ccc;
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
        height: 40px;
        transition: box-shadow 0.3s ease, border-color 0.3s ease;
    }

    .search-form:focus-within {
        border-color: #555;
    }

    .search-input {
        flex: 1;
        padding: 0 15px;
        height: 100%;
        border: none;
        outline: none;
        font-size: 0.9rem;
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
        height: 100%;
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