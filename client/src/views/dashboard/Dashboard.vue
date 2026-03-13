<template>
    <DefaultLayout>

        <!-- Header -->
        <b-row class="align-items-center mb-3">
            <b-col>
                <h5 class="mb-0 fw-semibold">Dashboard</h5>
                <small class="text-muted">{{ greeting }}</small>
            </b-col>
            <b-col cols="auto">
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="fas fa-calendar-alt me-1"></i>{{ today }}
                </span>
            </b-col>
        </b-row>

        <StatesComponent />

        <div v-show="!apiState.loading">

            <!-- ── Row 1: Summary Cards ──────────────────────────────────── -->
            <b-row class="g-3 mb-4">
                <b-col sm="6" xl="3" v-for="card in summaryCards" :key="card.label">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                :class="card.bg" style="width:52px;height:52px">
                                <i :class="`${card.icon} fs-18`"></i>
                            </div>
                            <div>
                                <div class="fs-22 fw-bold lh-1">{{ card.value }}</div>
                                <div class="text-muted small mt-1">{{ card.label }}</div>
                            </div>
                        </div>
                        <div v-if="card.link" class="card-footer border-0 py-1" :class="card.footerBg">
                            <router-link :to="card.link" :class="`small fw-medium ${card.linkClass}`">
                                {{ card.linkLabel }} <i class="fas fa-arrow-right ms-1"></i>
                            </router-link>
                        </div>
                    </div>
                </b-col>
            </b-row>

            <!-- ── Row 2: Line Chart + Doughnut ─────────────────────────── -->
            <b-row class="g-3 mb-4">
                <b-col lg="8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom py-2">
                            <h6 class="mb-0 fw-semibold">Stock Movements — Last 30 Days</h6>
                        </div>
                        <div class="card-body">
                            <canvas ref="lineChartRef" height="110"></canvas>
                        </div>
                    </div>
                </b-col>
                <b-col lg="4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom py-2">
                            <h6 class="mb-0 fw-semibold">Movement Types — This Month</h6>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <canvas ref="donutChartRef" height="180"></canvas>
                            <div class="mt-3 w-100">
                                <div v-for="(item, key) in TYPE_META" :key="key"
                                    class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="rounded-circle d-inline-block"
                                            :style="`width:10px;height:10px;background:${item.color}`"></span>
                                        <small class="text-muted">{{ item.label }}</small>
                                    </div>
                                    <small class="fw-semibold">{{ breakdown[key] ?? 0 }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </b-col>
            </b-row>

            <!-- ── Row 3: Bar chart (top SKUs) + Status pie ──────────────── -->
            <b-row class="g-3 mb-4">
                <b-col lg="8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom py-2">
                            <h6 class="mb-0 fw-semibold">Top 10 SKUs by Sales Quantity — This Month</h6>
                        </div>
                        <div class="card-body">
                            <canvas ref="barChartRef" height="110"></canvas>
                        </div>
                    </div>
                </b-col>
                <b-col lg="4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom py-2">
                            <h6 class="mb-0 fw-semibold">Movements by Status</h6>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <canvas ref="statusPieRef" height="180"></canvas>
                            <div class="mt-3 w-100">
                                <div v-for="(item, key) in STATUS_META" :key="key"
                                    class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="rounded-circle d-inline-block"
                                            :style="`width:10px;height:10px;background:${item.color}`"></span>
                                        <small class="text-muted">{{ item.label }}</small>
                                    </div>
                                    <small class="fw-semibold">{{ movementsByStatus[key] ?? 0 }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </b-col>
            </b-row>

            <!-- ── Row 4: Daily Sales Qty (area) + Stock per Store (bar) ── -->
            <b-row class="g-3 mb-4">
                <b-col lg="6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom py-2">
                            <h6 class="mb-0 fw-semibold">Daily Sales Units — Last 14 Days</h6>
                        </div>
                        <div class="card-body">
                            <canvas ref="areaChartRef" height="130"></canvas>
                        </div>
                    </div>
                </b-col>
                <b-col lg="6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom py-2">
                            <h6 class="mb-0 fw-semibold">Total Stock on Hand per Store</h6>
                        </div>
                        <div class="card-body">
                            <canvas ref="storeBarRef" height="130"></canvas>
                        </div>
                    </div>
                </b-col>
            </b-row>

            <!-- ── Row 5: Low Stock Table + Recent Movements ─────────────── -->
            <b-row class="g-3">
                <b-col lg="5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold">Top Low Stock SKUs</h6>
                            <router-link :to="{ name: 'stock.alerts' }" class="btn btn-outline-danger btn-sm">
                                View All
                            </router-link>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>SKU</th>
                                            <th>Store</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-center">Reorder</th>
                                            <th class="text-center">Shortage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!lowStockItems.length">
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                All stock levels healthy
                                            </td>
                                        </tr>
                                        <tr v-for="item in lowStockItems" :key="item.sku_code + item.store_name">
                                            <td>
                                                <div class="fw-medium small">{{ item.sku_name }}</div>
                                                <small class="text-muted">{{ item.sku_code }}</small>
                                            </td>
                                            <td><small>{{ item.store_name }}</small></td>
                                            <td class="text-center">
                                                <span
                                                    :class="item.quantity <= 0 ? 'badge bg-danger' : 'badge bg-warning text-dark'">
                                                    {{ item.quantity }}
                                                </span>
                                            </td>
                                            <td class="text-center text-muted small">{{ item.reorder_level }}</td>
                                            <td class="text-center">
                                                <span class="text-danger small fw-semibold">-{{ item.shortage }} {{
                                                    item.unit }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </b-col>

                <b-col lg="7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold">Recent Movements</h6>
                            <router-link :to="{ name: 'stock.movement.list' }" class="btn btn-outline-primary btn-sm">
                                View All
                            </router-link>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ref</th>
                                            <th>SKU</th>
                                            <th>Type</th>
                                            <th class="text-center">Qty</th>
                                            <th>Status</th>
                                            <th>By</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!recentMovements.length">
                                            <td colspan="7" class="text-center py-4 text-muted">No movements yet.</td>
                                        </tr>
                                        <tr v-for="m in recentMovements" :key="m.id">
                                            <td><small class="font-monospace text-muted">{{ m.reference_no }}</small>
                                            </td>
                                            <td><small>{{ m.sku_name }}</small></td>
                                            <td><span :class="typeBadge(m.type)">{{ typeLabel(m.type) }}</span></td>
                                            <td class="text-center"><small>{{ m.quantity }}</small></td>
                                            <td><span :class="statusBadge(m.status)">{{ m.status }}</span></td>
                                            <td><small class="text-muted">{{ m.created_by }}</small></td>
                                            <td><small class="text-muted">{{ formatDate(m.created_at) }}</small></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </b-col>
            </b-row>

        </div>
    </DefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from "vue";
import Chart from "chart.js/auto";
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import dashboardApi from "@/api/dashboard/dashboardApi";

const apiState = useApiState();

// ── Data refs ──────────────────────────────────────────────────────────────
const summary = ref({});
const breakdown = ref({});
const movementsTime = ref([]);
const dailySalesQty = ref([]);
const stockPerStore = ref([]);
const movementsByStatus = ref({});
const topSkus = ref([]);
const lowStockItems = ref([]);
const recentMovements = ref([]);

// ── Chart canvas refs ──────────────────────────────────────────────────────
const lineChartRef = ref(null);
const donutChartRef = ref(null);
const barChartRef = ref(null);
const statusPieRef = ref(null);
const areaChartRef = ref(null);
const storeBarRef = ref(null);

let charts = [];

// ── Meta ───────────────────────────────────────────────────────────────────
const TYPE_META = {
    sale: { label: "Sales", color: "#0d6efd" },
    procurement: { label: "Procurement", color: "#198754" },
    transfer_in: { label: "Transfer In", color: "#0dcaf0" },
    transfer_out: { label: "Transfer Out", color: "#ffc107" },
    adjustment_in: { label: "Adjustment In", color: "#6f42c1" },
    adjustment_out: { label: "Adjustment Out", color: "#dc3545" },
};

const STATUS_META = {
    completed: { label: "Completed", color: "#198754" },
    pending: { label: "Pending", color: "#ffc107" },
    rejected: { label: "Rejected", color: "#dc3545" },
    cancelled: { label: "Cancelled", color: "#6c757d" },
};

// ── Summary cards config ───────────────────────────────────────────────────
const summaryCards = computed(() => [
    { label: "Sales Today", value: summary.value.sales_today, icon: "fas fa-shopping-cart text-primary", bg: "bg-primary-subtle" },
    { label: "Sales This Month", value: summary.value.sales_this_month, icon: "fas fa-chart-line text-success", bg: "bg-success-subtle" },
    {
        label: "Pending Transfers", value: summary.value.pending_transfers, icon: "fas fa-exchange-alt text-warning", bg: "bg-warning-subtle",
        link: { name: "stock.movement.pending.approvals" }, linkLabel: "Review now", footerBg: "bg-warning-subtle", linkClass: "text-warning"
    },
    {
        label: "Low Stock SKUs", value: summary.value.low_stock, icon: "fas fa-exclamation-triangle text-danger", bg: "bg-danger-subtle",
        link: { name: "stock.alerts" }, linkLabel: "View alerts", footerBg: "bg-danger-subtle", linkClass: "text-danger"
    },
    { label: "Total Active SKUs", value: summary.value.total_skus, icon: "fas fa-boxes text-info", bg: "bg-info-subtle" },
    { label: "Out of Stock", value: summary.value.out_of_stock, icon: "fas fa-ban text-secondary", bg: "bg-secondary-subtle" },
    { label: "Procurements (Month)", value: summary.value.procurement_month, icon: "fas fa-truck text-success", bg: "bg-success-subtle" },
    { label: "Active Users", value: summary.value.total_users, icon: "fas fa-users text-primary", bg: "bg-primary-subtle" },
    { label: "Branches", value: summary.value.total_branches, icon: "fas fa-building text-info", bg: "bg-info-subtle" },
    { label: "Stores", value: summary.value.total_stores, icon: "fas fa-store text-warning", bg: "bg-warning-subtle" },
]);

// ── Helpers ────────────────────────────────────────────────────────────────
const typeBadge = (t) => ({ sale: "badge bg-primary", procurement: "badge bg-success", transfer_in: "badge bg-info text-dark", transfer_out: "badge bg-warning text-dark", adjustment_in: "badge bg-purple", adjustment_out: "badge bg-danger" }[t] ?? "badge bg-secondary");
const typeLabel = (t) => ({ sale: "Sale", procurement: "Procurement", transfer_in: "Xfer In", transfer_out: "Xfer Out", adjustment_in: "Adj In", adjustment_out: "Adj Out" }[t] ?? t);
const statusBadge = (s) => ({ completed: "badge bg-success", pending: "badge bg-warning text-dark", rejected: "badge bg-danger", cancelled: "badge bg-secondary" }[s] ?? "badge bg-light text-muted");
const formatDate = (d) => d ? new Date(d).toLocaleDateString("en-KE", { day: "2-digit", month: "short", hour: "2-digit", minute: "2-digit" }) : "—";

const makeChart = (canvas, config) => {
    if (!canvas) return null;
    const c = new Chart(canvas, config);
    charts.push(c);
    return c;
};

// ── Chart builders ─────────────────────────────────────────────────────────
const buildLineChart = () => {
    makeChart(lineChartRef.value, {
        type: "line",
        data: {
            labels: movementsTime.value.map(r => r.date),
            datasets: [
                { label: "Sales", data: movementsTime.value.map(r => r.sales), borderColor: "#0d6efd", backgroundColor: "rgba(13,110,253,0.07)", tension: 0.4, fill: true, pointRadius: 2 },
                { label: "Procurement", data: movementsTime.value.map(r => r.procurement), borderColor: "#198754", backgroundColor: "rgba(25,135,84,0.07)", tension: 0.4, fill: true, pointRadius: 2 },
                { label: "Transfers", data: movementsTime.value.map(r => r.transfers), borderColor: "#ffc107", backgroundColor: "rgba(255,193,7,0.07)", tension: 0.4, fill: true, pointRadius: 2 },
                { label: "Adjustments", data: movementsTime.value.map(r => r.adjustments), borderColor: "#6f42c1", backgroundColor: "rgba(111,66,193,0.07)", tension: 0.4, fill: true, pointRadius: 2 },
            ],
        },
        options: { responsive: true, plugins: { legend: { position: "top" } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { ticks: { maxTicksLimit: 10 } } } },
    });
};

const buildDonutChart = () => {
    const keys = Object.keys(TYPE_META);
    makeChart(donutChartRef.value, {
        type: "doughnut",
        data: {
            labels: keys.map(k => TYPE_META[k].label),
            datasets: [{ data: keys.map(k => breakdown.value[k] ?? 0), backgroundColor: keys.map(k => TYPE_META[k].color), borderWidth: 2 }],
        },
        options: { responsive: true, cutout: "65%", plugins: { legend: { display: false } } },
    });
};

const buildBarChart = () => {
    makeChart(barChartRef.value, {
        type: "bar",
        data: {
            labels: topSkus.value.map(s => s.name),
            datasets: [{ label: "Units Sold", data: topSkus.value.map(s => s.total_qty), backgroundColor: "#0d6efd", borderRadius: 4 }],
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { ticks: { maxRotation: 30 } } } },
    });
};

const buildStatusPie = () => {
    const keys = Object.keys(STATUS_META);
    makeChart(statusPieRef.value, {
        type: "pie",
        data: {
            labels: keys.map(k => STATUS_META[k].label),
            datasets: [{ data: keys.map(k => movementsByStatus.value[k] ?? 0), backgroundColor: keys.map(k => STATUS_META[k].color), borderWidth: 2 }],
        },
        options: { responsive: true, plugins: { legend: { display: false } } },
    });
};

const buildAreaChart = () => {
    makeChart(areaChartRef.value, {
        type: "line",
        data: {
            labels: dailySalesQty.value.map(r => r.date),
            datasets: [{ label: "Units Sold", data: dailySalesQty.value.map(r => r.total_qty), borderColor: "#198754", backgroundColor: "rgba(25,135,84,0.12)", tension: 0.4, fill: true, pointRadius: 3 }],
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
    });
};

const buildStoreBar = () => {
    makeChart(storeBarRef.value, {
        type: "bar",
        data: {
            labels: stockPerStore.value.map(s => s.store),
            datasets: [{ label: "Total Units on Hand", data: stockPerStore.value.map(s => s.total_qty), backgroundColor: ["#0d6efd", "#198754", "#ffc107", "#0dcaf0", "#dc3545"], borderRadius: 4 }],
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
    });
};

// ── Fetch ──────────────────────────────────────────────────────────────────
const fetchDashboard = async () => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const { data } = await dashboardApi.getDashboard();
        summary.value = data.summary;
        breakdown.value = data.movement_breakdown;
        movementsTime.value = data.movements_over_time;
        dailySalesQty.value = data.daily_sales_qty;
        stockPerStore.value = data.stock_per_store;
        movementsByStatus.value = data.movements_by_status;
        topSkus.value = data.top_skus;
        lowStockItems.value = data.low_stock_items;
        recentMovements.value = data.recent_movements;

        await nextTick();
        await nextTick(); // double tick — ensures v-show has rendered canvases
        buildLineChart();
        buildDonutChart();
        buildBarChart();
        buildStatusPie();
        buildAreaChart();
        buildStoreBar();
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load dashboard.");
    } finally {
        apiState.setLoading(false);
    }
};

// ── Greeting ───────────────────────────────────────────────────────────────
const greeting = computed(() => {
    const h = new Date().getHours();
    return h < 12 ? "Good morning" : h < 17 ? "Good afternoon" : "Good evening";
});
const today = new Date().toLocaleDateString("en-KE", { weekday: "long", year: "numeric", month: "long", day: "numeric" });

// Destroy all charts on unmount to prevent memory leaks
onBeforeUnmount(() => charts.forEach(c => c.destroy()));

onMounted(fetchDashboard);
</script>