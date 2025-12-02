<?php
function renderPaginator($total_pages, $current_page, $base_url = '?') {
    if ($total_pages <= 1) {
        return; // Jangan tampilkan apa-apa jika cuma 1 halaman
    }
    echo '<div class="pagination">';
    if ($current_page > 1) {
        echo '<a href="' . $base_url . 'page=' . ($current_page - 1) . '">&laquo;</a>';
    } else {
        echo '<span class="disabled">&laquo;</span>';
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == 1 || $i == $total_pages || ($i >= $current_page - 2 && $i <= $current_page + 2)) {
            if ($i == $current_page) {
                echo '<span class="active">' . $i . '</span>';
            } else {
                echo '<a href="' . $base_url . 'page=' . $i . '">' . $i . '</a>';
            }
        } elseif (strpos($base_url, '...') === false && ($i == $current_page - 3 || $i == $current_page + 3) ) {
             echo '<span class="dots">...</span>';
        }
    }
    if ($current_page < $total_pages) {
        echo '<a href="' . $base_url . 'page=' . ($current_page + 1) . '">&raquo;</a>';
    } else {
        echo '<span class="disabled">&raquo;</span>';
    }
    echo '</div>';
}
?>

