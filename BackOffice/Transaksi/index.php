<?php
include("../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
?>

<?php include("../../Component/sidebar.php"); ?>

<div class="container">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Total Produk</h3>
                    <div class="stat-icon"><i class="fas fa-box"></i></div>
                </div>
                <div class="stat-value" id="totalProducts">0</div>
                <div class="stat-subtitle">Item terdaftar di sistem</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Total Pendapatan</h3>
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                </div>
                <div class="stat-value" id="totalSales">Rp 0</div>
                <div class="stat-subtitle">Dari pesanan 'Selesai'</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3>Produk Terlaris</h3>
                    <div class="stat-icon"><i class="fas fa-fire"></i></div>
                </div>
                <div class="stat-value" id="topProduct" style="font-size: 1.5rem;">-</div>
                <div class="stat-subtitle">Paling banyak terjual</div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h2><i class="fas fa-chart-bar"></i> Statistik Pesanan</h2>
                    <select id="salesPeriod" onchange="updateSalesChart()">
                        <option value="week">7 Hari Terakhir</option>
                        <option value="month">Bulan Ini (<?= date('Y') ?>)</option>
                    </select>
                </div>
                <div class="bar-chart" id="salesChart"></div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h2><i class="fas fa-crown"></i> Top 5 Produk</h2>
                </div>
                <div class="h-bar-chart" id="topProductsChart"></div>
            </div>
        </div>

        <div class="bottom-grid">
            <div class="table-card">
                <div class="chart-header">
                    <h2><i class="fas fa-clock"></i> Pesanan Terbaru</h2>
                    <a href="../Transaksi/index.php" class="btn btn-sm btn-primary" style="text-decoration:none;">Lihat Semua</a>
                </div>
                <div class="orders-table" id="recentOrders"></div>
            </div>

            <div class="table-card">
                <h2><i class="fas fa-exclamation-triangle"></i> Stok Menipis (< 5)</h2>
                        <div class="alert-list" id="stockAlerts"></div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js?v=<?= time() ?>"></script>

<?php include("../../Component/bottom.php"); ?>