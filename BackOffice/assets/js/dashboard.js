// Variable global untuk menyimpan data dashboard
let dashboardData = {};

// FUNGSI FETCH DATA DARI DATABASE (REAL-TIME)
async function fetchDashboardData() {
    try {
        // Path ke file PHP action yang baru dibuat
        const response = await fetch('action/get_dashboard_data.php');
        const result = await response.json();

        if (result.status === 'success') {
            dashboardData = result.data;
            console.log('Data fetched:', dashboardData);
            initDashboard(); // Render tampilan setelah data didapat
        } else {
            console.error('Gagal memuat data:', result.message);
        }
    } catch (error) {
        console.error('Terjadi kesalahan:', error);
    }
}

// Fungsi untuk animasi counter (Angka naik pelan-pelan)
function animateCounter(id, target, isCurrency = false) {
    const element = document.getElementById(id);
    if (!element) return;
    
    const duration = 1500;
    const start = 0;
    // Jika target 0, langsung tampilkan 0
    if(target === 0) {
        element.textContent = isCurrency ? "Rp 0" : "0";
        return;
    }

    const increment = target / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        
        // Format angka (Pemisah Ribuan)
        let formatted = Math.floor(current).toLocaleString('id-ID');
        if (isCurrency) formatted = "Rp " + formatted;
        
        element.textContent = formatted;
    }, 16);
}

// Render Stats Cards (Total Produk & Omzet)
function renderStats() {
    animateCounter('totalProducts', dashboardData.totalProducts);
    animateCounter('totalSales', dashboardData.totalSales, true); // True = Format Rupiah
    
    const topProductElement = document.getElementById('topProduct');
    if (topProductElement) {
        topProductElement.textContent = dashboardData.topProduct;
    }
}

// Render Sales Chart (Grafik Batang)
function updateSalesChart() {
    const period = document.getElementById('salesPeriod').value;
    const data = period === 'week' ? dashboardData.salesData.week : dashboardData.salesData.month;
    
    const chartElement = document.getElementById('salesChart');
    if (!chartElement) return;

    if (!data || data.length === 0) {
        chartElement.innerHTML = '<p class="text-center text-muted">Belum ada data penjualan</p>';
        return;
    }
    
    const maxValue = Math.max(...data.map(item => item.value)) || 1; // Hindari bagi 0
    
    const chartHTML = data.map(item => {
        const height = (item.value / maxValue) * 100;
        // Tinggi minimal 5% biar bar tetap kelihatan walau nilainya kecil
        const displayHeight = height < 5 && item.value > 0 ? 5 : height; 
        
        return `
            <div class="bar" style="height: ${displayHeight}%" title="${item.value} Pesanan">
                <span class="bar-value">${item.value}</span>
                <span class="bar-label">${item.label}</span>
            </div>
        `;
    }).join('');
    
    chartElement.innerHTML = chartHTML;
}

// Render Top Products (Bar Horizontal)
function renderTopProducts() {
    const chartElement = document.getElementById('topProductsChart');
    if (!chartElement) return;

    if (!dashboardData.topProducts || dashboardData.topProducts.length === 0) {
        chartElement.innerHTML = '<p class="text-center text-muted py-4">Belum ada penjualan</p>';
        return;
    }
    
    const maxValue = Math.max(...dashboardData.topProducts.map(item => item.sales)) || 1;
    
    const chartHTML = dashboardData.topProducts.map(item => {
        const width = (item.sales / maxValue) * 100;
        return `
            <div class="h-bar-item">
                <div class="h-bar-label">${item.name}</div>
                <div class="h-bar-track">
                    <div class="h-bar-fill" style="width: ${width}%">
                        <span class="h-bar-value">${item.sales}</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    chartElement.innerHTML = chartHTML;
}

// Render Recent Orders (Tabel)
function renderRecentOrders() {
    const ordersElement = document.getElementById('recentOrders');
    if (!ordersElement) return;

    if (!dashboardData.recentOrders || dashboardData.recentOrders.length === 0) {
        ordersElement.innerHTML = `
            <div class="empty-state p-4">
                <i class="fas fa-box-open" style="font-size:2rem; color:#ddd;"></i>
                <p>Belum ada pesanan masuk</p>
            </div>`;
        return;
    }
    
    const ordersHTML = dashboardData.recentOrders.map(order => {
        // Mapping status ke warna badge (Sesuai CSS Global)
        let icon = 'clock';
        let colorClass = 'status-pending';
        
        if(order.status === 'Selesai') { icon = 'check-circle'; colorClass = 'status-success'; }
        else if(order.status === 'Dikirim') { icon = 'truck'; colorClass = 'status-process'; }
        else if(order.status === 'Diproses') { icon = 'box'; colorClass = 'status-process'; }
        else if(order.status === 'Batal' || order.status === 'Kadaluarsa') { icon = 'times-circle'; colorClass = 'status-danger'; }

        return `
        <div class="order-item">
            <div class="order-info">
                <div class="order-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="order-details">
                    <h4>${order.id} - ${order.customer}</h4>
                    <p>${order.product}</p>
                </div>
            </div>
            <span class="order-status ${colorClass}">
                <i class="fas fa-${icon}"></i>
                ${order.status}
            </span>
        </div>
    `;
    }).join('');
    
    ordersElement.innerHTML = ordersHTML;
}

// Render Stock Alerts (Stok Menipis)
function renderStockAlerts() {
    const alertsElement = document.getElementById('stockAlerts');
    if (!alertsElement) return;

    if (!dashboardData.lowStock || dashboardData.lowStock.length === 0) {
        alertsElement.innerHTML = `
            <div class="text-center p-4" style="color:#28a745;">
                <i class="fas fa-check-circle mb-2" style="font-size:2rem;"></i>
                <p class="m-0">Semua stok aman</p>
            </div>`;
        return;
    }
    
    const alertsHTML = dashboardData.lowStock.map(item => `
        <div class="alert-item">
            <h4><i class="fas fa-exclamation-circle"></i> ${item.name}</h4>
            <p>Stok tersisa: <span>${item.stock} ${item.unit}</span></p>
        </div>
    `).join('');
    
    alertsElement.innerHTML = alertsHTML;
}

// Initialize Dashboard
function initDashboard() {
    renderStats();
    updateSalesChart();
    renderTopProducts();
    renderRecentOrders();
    renderStockAlerts();
}

// LOAD DATA SAAT HALAMAN READY
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fetchDashboardData);
} else {
    fetchDashboardData();
}