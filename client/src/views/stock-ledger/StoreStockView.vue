<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">
                <b-card no-body>

                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <b-card-title class="mb-0">
                                    Stock Ledger
                                    <span v-if="selectedStore" class="text-muted fw-normal fs-6 ms-2">
                                        — {{ selectedStore.name }}
                                    </span>
                                </b-card-title>
                            </b-col>
                            <b-col cols="auto" class="d-flex gap-2">
                                <button class="btn btn-outline-warning btn-sm"
                                    @click="router.push({ name: 'stock.alerts', query: { store_id: selectedStoreId } })"
                                    :disabled="!selectedStoreId">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Low Stock
                                    <span v-if="lowStockCount > 0" class="badge bg-danger ms-1">{{ lowStockCount
                                        }}</span>
                                </button>
                                <button class="btn btn-outline-success btn-sm"
                                    @click="router.push({ name: 'stock.movement.record.procurement' })"
                                    :disabled="!selectedStoreId">
                                    <i class="fas fa-plus me-1"></i> Add Stock
                                </button>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body class="pt-2">
                        <StatesComponent />

                        <!-- Store selector + search -->
                        <b-row class="mb-3 g-2">
                            <b-col md="4">
                                <label class="form-label mb-1 small text-muted">Select Store</label>
                                <select v-model="selectedStoreId" class="form-select" @change="onStoreChange()">
                                    <option value="">— Choose a store —</option>
                                    <option v-for="s in stores" :key="s.id" :value="s.id">
                                        {{ s.name }} ({{ s.branch?.name ?? s.code }})
                                    </option>
                                </select>
                            </b-col>
                            <b-col md="4">
                                <label class="form-label mb-1 small text-muted">Search SKU</label>
                                <input v-model="search" type="text" class="form-control" placeholder="Name or code..."
                                    :disabled="!selectedStoreId" @input="debounceSearch()" />
                            </b-col>
                            <b-col md="2">
                                <label class="form-label mb-1 small text-muted">Filter</label>
                                <select v-model="filterLow" class="form-select" :disabled="!selectedStoreId"
                                    @change="applyFilter()">
                                    <option value="">All SKUs</option>
                                    <option value="low">Low Stock Only</option>
                                    <option value="out">Out of Stock</option>
                                </select>
                            </b-col>
                        </b-row>

                        <!-- Placeholder when no store selected -->
                        <div v-if="!selectedStoreId" class="text-center py-5 text-muted">
                            <i class="fas fa-store fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Select a store above to view its stock levels.</p>
                        </div>

                        <template v-else>

                            <!-- Summary cards -->
                            <b-row class="g-3 mb-4">
                                <b-col md="3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Total SKUs</div>
                                        <div class="fw-bold fs-4 text-primary">{{ summary.total }}</div>
                                    </div>
                                </b-col>
                                <b-col md="3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">In Stock</div>
                                        <div class="fw-bold fs-4 text-success">{{ summary.inStock }}</div>
                                    </div>
                                </b-col>
                                <b-col md="3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Low Stock</div>
                                        <div class="fw-bold fs-4 text-warning">{{ summary.low }}</div>
                                    </div>
                                </b-col>
                                <b-col md="3">
                                    <div class="border rounded p-3 text-center">
                                        <div class="text-muted small">Out of Stock</div>
                                        <div class="fw-bold fs-4 text-danger">{{ summary.out }}</div>
                                    </div>
                                </b-col>
                            </b-row>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>SKU Name</th>
                                            <th>Code</th>
                                            <th>Unit</th>
                                            <th>Unit Price (KES)</th>
                                            <th>Reorder Level</th>
                                            <th class="text-center">Current Qty</th>
                                            <th class="text-center">Status</th>
                                            <th>Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!filteredStock.length && !apiState.loading">
                                            <td colspan="9" class="text-center py-4 text-muted">
                                                No stock records found.
                                            </td>
                                        </tr>
                                        <tr v-for="(item, i) in filteredStock" :key="item.id"
                                            :class="{ 'table-danger': item.quantity <= 0, 'table-warning': item.is_low_stock && item.quantity > 0 }">
                                            <td>{{ i + 1 }}</td>
                                            <td class="fw-medium">{{ item.sku?.name }}</td>
                                            <td><span class="badge bg-secondary">{{ item.sku?.code }}</span></td>
                                            <td>{{ item.sku?.unit }}</td>
                                            <td>{{ formatCurrency(item.sku?.unit_price) }}</td>
                                            <td>{{ item.sku?.reorder_level ?? 0 }}</td>
                                            <td class="text-center">
                                                <span class="fw-bold fs-5"
                                                    :class="item.quantity <= 0 ? 'text-danger' : item.is_low_stock ? 'text-warning' : 'text-success'">
                                                    {{ item.quantity }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span v-if="item.quantity <= 0" class="badge bg-danger">Out of
                                                    Stock</span>
                                                <span v-else-if="item.is_low_stock" class="badge bg-warning text-dark">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
                                                </span>
                                                <span v-else class="badge bg-success">OK</span>
                                            </td>
                                            <td>{{ formatDate(item.updated_at) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </template>
                    </b-card-body>
                </b-card>
            </b-col>
        </b-row>
    </DefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import stockApi from "@/api/stock/stockApi";
import branchesApi from "@/api/branches/branchesApi";

const route = useRoute();
const router = useRouter();
const apiState = useApiState();

// ── State ─────────────────────────────────────────────────────────────────
const stores = ref([]);
const selectedStoreId = ref(route.query.store_id ?? "");
const selectedStore = computed(() => stores.value.find(s => s.id == selectedStoreId.value) ?? null);
const stock = ref([]);
const search = ref("");
const filterLow = ref("");
let searchTimer = null;

// ── Summary computed ──────────────────────────────────────────────────────
const summary = computed(() => ({
    total: stock.value.length,
    inStock: stock.value.filter(i => i.quantity > 0 && !i.is_low_stock).length,
    low: stock.value.filter(i => i.is_low_stock && i.quantity > 0).length,
    out: stock.value.filter(i => i.quantity <= 0).length,
}));

const lowStockCount = computed(() => summary.value.low + summary.value.out);

// ── Filtered table data ───────────────────────────────────────────────────
const filteredStock = computed(() => {
    let items = stock.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        items = items.filter(i =>
            i.sku?.name?.toLowerCase().includes(q) ||
            i.sku?.code?.toLowerCase().includes(q)
        );
    }
    if (filterLow.value === "low") items = items.filter(i => i.is_low_stock && i.quantity > 0);
    if (filterLow.value === "out") items = items.filter(i => i.quantity <= 0);
    return items;
});

// ── Fetch stores ──────────────────────────────────────────────────────────
const fetchStores = async () => {
    try {
        const { data } = await branchesApi.listStores({ per_page: 100 });
        stores.value = data.data ?? [];
        // Auto-select if query param passed or only one store
        if (!selectedStoreId.value && stores.value.length === 1) {
            selectedStoreId.value = stores.value[0].id;
        }
        if (selectedStoreId.value) fetchStock();
    } catch (e) { console.error(e); }
};

// ── Fetch stock ───────────────────────────────────────────────────────────
const fetchStock = async () => {
    if (!selectedStoreId.value) return;
    apiState.resetState();
    apiState.setLoading(true);
    try {
        // Load all stock for the store — search/filter is handled client-side
        const { data } = await stockApi.getStoreStock(selectedStoreId.value);
        stock.value = Array.isArray(data) ? data : [];
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load stock.");
    } finally {
        apiState.setLoading(false);
    }
};

const onStoreChange = () => {
    search.value = "";
    filterLow.value = "";
    stock.value = [];
    fetchStock();
};

const debounceSearch = () => {
    // Search is client-side computed — no API call needed
    clearTimeout(searchTimer);
};

const applyFilter = () => { }; // filtering is purely computed, no refetch needed

// ── Helpers ───────────────────────────────────────────────────────────────
const formatDate = (d) => d ? new Date(d).toLocaleString("en-KE") : "—";
const formatCurrency = (v) => v != null ? Number(v).toLocaleString("en-KE", { minimumFractionDigits: 2 }) : "—";

onMounted(fetchStores);
</script>