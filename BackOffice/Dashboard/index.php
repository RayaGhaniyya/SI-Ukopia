<?php
include("../Component/session.php");
include("../Component/head.php");
?>
<div class="container">
    <?php include("../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <!-- Stats Cards (3 CARD AJA, HAPUS PESANAN AKTIF) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Produk Terdaftar</h3>
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <div class="stat-value" id="totalProducts">0</div>
                <div class="stat-subtitle">
                    <span class="stat-trend up"><i class="fas fa-arrow-up"></i> 12%</span>
                    dari bulan lalu
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Total Penjualan</h3>
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                <div class="stat-value" id="totalSales">0</div>
                <div class="stat-subtitle">
                    <span class="stat-trend up"><i class="fas fa-arrow-up"></i> 8%</span>
                    minggu ini
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Produk Terlaris</h3>
                    <div class="stat-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                </div>
                <div class="stat-value" id="topProduct" style="font-size: 20px;">-</div>
                <div class="stat-subtitle">
                    Bulan ini
                </div>
            </div>
        </div>
        <!-- Charts -->
        <div class="charts-grid">
            <!-- Grafik Penjualan -->
            <div class="chart-card">
                <div class="chart-header">
                    <h2><i class="fas fa-chart-line"></i> Grafik Penjualan</h2>
                    <select id="salesPeriod" onchange="updateSalesChart()">
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan Ini</option>
                    </select>
                </div>
                <div class="bar-chart" id="salesChart"></div>
            </div>
            <!-- Produk Terlaris -->
            <div class="chart-card">
                <div class="chart-header">
                    <h2><i class="fas fa-crown"></i> Produk Terlaris</h2>
                </div>
                <div class="h-bar-chart" id="topProductsChart"></div>
            </div>
        </div>
        <!-- Bottom Section -->
        <div class="bottom-grid">
            <!-- Pesanan Terbaru -->
            <div class="table-card">
                <h2><i class="fas fa-receipt"></i> Pesanan Terbaru</h2>
                <div class="orders-table" id="recentOrders"></div>
            </div>
            <!-- Peringatan Stok -->
            <div class="table-card">
                <h2><i class="fas fa-exclamation-triangle"></i> Peringatan Stok</h2>
                <div class="alert-list" id="stockAlerts"></div>
            </div>
        </div>
    </div>
</div>
<?php if (!empty($notif)): ?>
    <script>
        showNotification("<?= htmlspecialchars($notif) ?>", "<?= $type ?>");
    </script>
<?php endif; ?>
<script src="../assets/js/dashboard.js"></script>
<?php include("../Component/bottom.php"); ?>
