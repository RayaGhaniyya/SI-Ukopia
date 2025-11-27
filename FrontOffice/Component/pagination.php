<?php
function renderPaginator($totalPages, $currentPage, $baseUrl) {
    if ($totalPages <= 1) return; // Gak perlu pagination kalau cuma 1 halaman

    echo '<div class="pagination-container">';
    
    // Tombol Previous
    if ($currentPage > 1) {
        $prevPage = $currentPage - 1;
        echo "<a href='{$baseUrl}page=$prevPage' class='page-btn'>&laquo; Prev</a>";
    } else {
        echo "<span class='page-btn disabled'>&laquo; Prev</span>";
    }

    // Angka Halaman
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        echo "<a href='{$baseUrl}page=$i' class='page-btn $active'>$i</a>";
    }

    // Tombol Next
    if ($currentPage < $totalPages) {
        $nextPage = $currentPage + 1;
        echo "<a href='{$baseUrl}page=$nextPage' class='page-btn'>Next &raquo;</a>";
    } else {
        echo "<span class='page-btn disabled'>Next &raquo;</span>";
    }

    echo '</div>';
}
?>