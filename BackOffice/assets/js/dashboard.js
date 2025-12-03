
let dashboardData = {};


async function fetchDashboardData() {
    try {
        
        const response = await fetch('action/get_dashboard_data.php');
        const result = await response.json();

        if (result.status === 'success') {
            dashboardData = result.data;
            console.log('Data fetched:', dashboardData);
            initDashboard(); 
        } else {
            console.error('Gagal memuat data:', result.message);
        }
    } catch (error) {
        console.error('Terjadi kesalahan:', error);
    }
}


function animateCounter(id, target, isCurrency = false) {
    const element = document.getElementById(id);
    if (!element) return;
    
    const duration = 1500;
    const start = 0;
    
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
        
        
        let formatted = Math.floor(current).toLocaleString('id-ID');
        if (isCurrency) formatted = "Rp " + formatted;
        
        element.textContent = formatted;
    }, 16);
}


function renderStats() {
    animateCounter('totalProducts', dashboardData.totalProducts);
    animateCounter('totalSales', dashboardData.totalSales, true); 
    
    const topProductElement = document.getElementById('topProduct');
    if (topProductElement) {
        topProductElement.textContent = dashboardData.topProduct;
    }
}


function updateSalesChart() {
    const period = document.getElementById('salesPeriod').value;
    const data = period === 'week' ? dashboardData.salesData.week : dashboardData.salesData.month;
    
    const chartElement = document.getElementById('salesChart');
    if (!chartElement) return;

    if (!data || data.length === 0) {
        chartElement.innerHTML = '<p class="text-center text-muted">Belum ada data penjualan</p>';
        return;
    }
    
    const maxValue = Math.max(...data.map(item => item.value)) || 1; 
    
    const chartHTML = data.map(item => {
        const height = (item.value / maxValue) * 100;
        
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


function renderRecentOrders() {
    const ordersElement = document.getElementById('recentOrders');
    if (!ordersElement) return;

    
    if (!dashboardData.recentOrders || dashboardData.recentOrders.length === 0) {
        ordersElement.innerHTML = `
            <div class="empty-state p-4 text-center text-muted">
                <i class="fas fa-box-open" style="font-size:2rem; color:#ddd; margin-bottom:10px;"></i>
                <p class="m-0">Belum ada pesanan masuk</p>
            </div>`;
        return;
    }
    
    
    const ordersHTML = dashboardData.recentOrders.map(order => {
        
        let icon = 'clock';
        if(order.statusText === 'Selesai') icon = 'check-circle';
        else if(order.statusText === 'Dikirim') icon = 'truck';
        else if(order.statusText === 'Diproses' || order.statusText === 'Sudah Dibayar') icon = 'box';
        else if(order.statusText === 'Batal' || order.statusText === 'Kadaluarsa') icon = 'times-circle';

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
            <span class="badge bg-danger ${order.statusClass}" style="display:flex; align-items:center; gap:5px; padding:8px 12px;">
                <i class="fas fa-${icon}"></i>
                ${order.statusText}
            </span>
        </div>
    `;
    }).join('');
    
    ordersElement.innerHTML = ordersHTML;
}


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


function initDashboard() {
    renderStats();
    updateSalesChart();
    renderTopProducts();
    renderRecentOrders();
    renderStockAlerts();
}


if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fetchDashboardData);
} else {
    fetchDashboardData();
}