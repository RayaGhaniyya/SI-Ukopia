// Variable global untuk menyimpan data dashboard
let dashboardData = {};

// Data dummy untuk testing
function loadDummyData() {
    dashboardData = {
        totalProducts: 89,
        totalSales: 1247,
        topProduct: "Arabica Gayo",
        
        salesData: {
            week: [
                { label: 'Sen', value: 142 },
                { label: 'Sel', value: 128 },
                { label: 'Rab', value: 185 },
                { label: 'Kam', value: 156 },
                { label: 'Jum', value: 219 },
                { label: 'Sab', value: 267 },
                { label: 'Min', value: 234 }
            ],
            month: [
                { label: 'Jan', value: 420 },
                { label: 'Feb', value: 380 },
                { label: 'Mar', value: 510 },
                { label: 'Apr', value: 460 },
                { label: 'Mei', value: 620 },
                { label: 'Jun', value: 780 },
                { label: 'Jul', value: 650 },
                { label: 'Agu', value: 590 },
                { label: 'Sep', value: 710 },
                { label: 'Okt', value: 820 },
                { label: 'Nov', value: 760 },
                { label: 'Des', value: 890 }
            ]
        },
        
        topProducts: [
            { name: 'Arabica Gayo', sales: 245 },
            { name: 'Robusta Lampung', sales: 189 },
            { name: 'Toraja Premium', sales: 167 },
            { name: 'Kopi Luwak', sales: 134 },
            { name: 'Aceh Gayo Blend', sales: 98 }
        ],
        
        recentOrders: [
            { id: '#ORD-1234', customer: 'Budi Santoso', product: 'Arabica Gayo 500g', status: 'success', statusText: 'Dikirim' },
            { id: '#ORD-1235', customer: 'Siti Nurhaliza', product: 'Robusta Lampung 1kg', status: 'process', statusText: 'Diproses' },
            { id: '#ORD-1236', customer: 'Ahmad Yani', product: 'Toraja Premium 250g', status: 'pending', statusText: 'Pending' },
            { id: '#ORD-1237', customer: 'Dewi Lestari', product: 'Kopi Luwak 100g', status: 'success', statusText: 'Dikirim' },
            { id: '#ORD-1238', customer: 'Andi Wijaya', product: 'Flores Bajawa 250g', status: 'process', statusText: 'Diproses' }
        ],
        
        lowStock: [
            { name: 'Kopi Luwak Premium', stock: 5, unit: 'kg' },
            { name: 'Arabica Flores', stock: 8, unit: 'kg' },
            { name: 'Robusta Bali', stock: 12, unit: 'kg' },
            { name: 'Mandailing Arabica', stock: 6, unit: 'kg' }
        ]
    };
    
    console.log('Data loaded:', dashboardData);
    initDashboard();
}

// Fungsi untuk animasi counter
function animateCounter(id, target, duration = 1000) {
    const element = document.getElementById(id);
    if (!element) {
        console.error('Element not found:', id);
        return;
    }
    
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = typeof target === 'number' ? Math.floor(target) : target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// Render Stats Cards
function renderStats() {
    console.log('Rendering stats...');
    animateCounter('totalProducts', dashboardData.totalProducts);
    animateCounter('totalSales', dashboardData.totalSales);
    
    const topProductElement = document.getElementById('topProduct');
    if (topProductElement) {
        topProductElement.textContent = dashboardData.topProduct;
    }
}

// Render Sales Chart
function updateSalesChart() {
    console.log('Updating sales chart...');
    const period = document.getElementById('salesPeriod').value;
    const data = period === 'week' ? dashboardData.salesData.week : dashboardData.salesData.month;
    
    if (!data || data.length === 0) return;
    
    const maxValue = Math.max(...data.map(item => item.value));
    
    const chartHTML = data.map(item => {
        const height = (item.value / maxValue) * 100;
        return `
            <div class="bar" style="height: ${height}%">
                <span class="bar-value">${item.value}</span>
                <span class="bar-label">${item.label}</span>
            </div>
        `;
    }).join('');
    
    const chartElement = document.getElementById('salesChart');
    if (chartElement) {
        chartElement.innerHTML = chartHTML;
    }
}

// Render Top Products Chart
function renderTopProducts() {
    console.log('Rendering top products...');
    if (!dashboardData.topProducts || dashboardData.topProducts.length === 0) return;
    
    const maxValue = Math.max(...dashboardData.topProducts.map(item => item.sales));
    
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
    
    const chartElement = document.getElementById('topProductsChart');
    if (chartElement) {
        chartElement.innerHTML = chartHTML;
    }
}

// Render Recent Orders
function renderRecentOrders() {
    console.log('Rendering recent orders...');
    if (!dashboardData.recentOrders || dashboardData.recentOrders.length === 0) {
        const ordersElement = document.getElementById('recentOrders');
        if (ordersElement) {
            ordersElement.innerHTML = '<p style="color: #888; text-align: center;">Tidak ada pesanan terbaru</p>';
        }
        return;
    }
    
    const ordersHTML = dashboardData.recentOrders.map(order => `
        <div class="order-item">
            <div class="order-info">
                <div class="order-icon">
                    <i class="fas fa-coffee"></i>
                </div>
                <div class="order-details">
                    <h4>${order.id} - ${order.customer}</h4>
                    <p>${order.product}</p>
                </div>
            </div>
            <span class="order-status status-${order.status}">
                <i class="fas fa-${order.status === 'success' ? 'check-circle' : order.status === 'process' ? 'spinner fa-spin' : 'clock'}"></i>
                ${order.statusText}
            </span>
        </div>
    `).join('');
    
    const ordersElement = document.getElementById('recentOrders');
    if (ordersElement) {
        ordersElement.innerHTML = ordersHTML;
    }
}

// Render Stock Alerts
function renderStockAlerts() {
    console.log('Rendering stock alerts...');
    if (!dashboardData.lowStock || dashboardData.lowStock.length === 0) {
        const alertsElement = document.getElementById('stockAlerts');
        if (alertsElement) {
            alertsElement.innerHTML = '<p style="color: #888; text-align: center;">Stok aman</p>';
        }
        return;
    }
    
    const alertsHTML = dashboardData.lowStock.map(item => `
        <div class="alert-item">
            <h4><i class="fas fa-exclamation-circle"></i> ${item.name}</h4>
            <p>Stok tersisa: <span>${item.stock} ${item.unit}</span></p>
        </div>
    `).join('');
    
    const alertsElement = document.getElementById('stockAlerts');
    if (alertsElement) {
        alertsElement.innerHTML = alertsHTML;
    }
}

// Initialize Dashboard
function initDashboard() {
    console.log('Initializing dashboard...');
    renderStats();
    updateSalesChart();
    renderTopProducts();
    renderRecentOrders();
    renderStockAlerts();
}

// CRITICAL: Load dashboard saat halaman ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadDummyData);
} else {
    // DOM sudah ready, load langsung
    loadDummyData();
}

console.log('Dashboard script loaded');