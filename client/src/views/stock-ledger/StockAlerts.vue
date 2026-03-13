<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">
                <b-card no-body>

                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <b-card-title class="mb-0">
                                    Low Stock Alerts
                                    <span v-if="selectedStore" class="text-muted fw-normal fs-6 ms-2">
                                        — {{ selectedStore.name }}
                                    </span>
                                </b-card-title>
                            </b-col>
                            <b-col cols="auto" class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm"
                                    @click="router.push({ name: 'stock.lists', query: { store_id: selectedStoreId } })">
                                    <i class="fas fa-list me-1"></i> Full Ledger
                                </button>
                                <button class="btn btn-outline-success btn-sm"
                                    @click="router.push({ name: 'stock.movement.record.procurement' })"
                                    :disabled="!selectedStoreId">
                                    <i class="fas fa-plus me-1"></i> Restock
                                </button>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body class="pt-2">
                        <StatesComponent />

                        <!-- Store selector -->
                        <b-row class="mb-3 g-2">
                            <b-col md="4">
                                <label class="form-label mb-1 small text-muted">Select Store</label>
                                <select v-model="selectedStoreId" class="form-select" @change="fetchAlerts()">
                                    <option value="">— Choose a store —</option>
                                    <option v-for="s in stores" :key="s.id" :value="s.id">
                                        {{ s.name }} ({{ s.branch?.name ?? s.code }})
                                    </option>
                                </select>
                            </b-col>
                        </b-row>

                        <!-- Placeholder -->
                        <div v-if="!selectedStoreId" class="text-center py-5 text-muted">
                            <i class="fas fa-bell fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Select a store to view low stock alerts.</p>
                        </div>

                        <template v-else>

                            <!-- All clear -->
                            <div v-if="!alerts.length && !apiState.loading" class="text-center py-5 text-success">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <p class="mb-0 fw-medium">All stock levels are healthy!</p>
                                <small class="text-muted">No items are below their reorder level.</small>
                            </div>

                            <!-- Alerts table -->
                            <div v-else class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>SKU Name</th>
                                            <th>Code</th>
                                            <th>Unit</th>
                                            <th class="text-center">Current Qty</th>
                                            <th class="text-center">Reorder Level</th>
                                            <th class="text-center">Shortage</th>
                                            <th class="text-center">Status</th>
                                            <th>Last Updated</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, i) in alerts" :key="item.id"
                                            :class="item.quantity <= 0 ? 'table-danger' : 'table-warning'">
                                            <td>{{ i + 1 }}</td>
                                            <td class="fw-medium">{{ item.sku?.name }}</td>
                                            <td><span class="badge bg-secondary">{{ item.sku?.code }}</span></td>
                                            <td>{{ item.sku?.unit }}</td>
                                            <td class="text-center">
                                                <span class="fw-bold"
                                                    :class="item.quantity <= 0 ? 'text-danger' : 'text-warning'">
                                                    {{ item.quantity }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ item.sku?.reorder_level ?? 0 }}</td>
                                            <td class="text-center">
                                                <span class="text-danger fw-medium">
                                                    {{ Math.max(0, (item.sku?.reorder_level ?? 0) - item.quantity) }}
                                                    {{ item.sku?.unit }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span v-if="item.quantity <= 0" class="badge bg-danger">Out of
                                                    Stock</span>
                                                <span v-else class="badge bg-warning text-dark">Low Stock</span>
                                            </td>
                                            <td>{{ formatDate(item.updated_at) }}</td>
                                            <td class="text-end">
                                                <button class="btn btn-success btn-sm"
                                                    @click="router.push({ name: 'stock.movement.record.procurement', query: { sku_id: item.sku_id, store_id: selectedStoreId } })">
                                                    <i class="fas fa-plus me-1"></i> Restock
                                                </button>
                                            </td>
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

const stores = ref([]);
const alerts = ref([]);
const selectedStoreId = ref(route.query.store_id ?? "");
const selectedStore = computed(() => stores.value.find(s => s.id == selectedStoreId.value) ?? null);

const fetchStores = async () => {
    try {
        const { data } = await branchesApi.listStores({ per_page: 100 });
        stores.value = data.data ?? [];
        if (!selectedStoreId.value && stores.value.length === 1) {
            selectedStoreId.value = stores.value[0].id;
        }
        if (selectedStoreId.value) fetchAlerts();
    } catch (e) { console.error(e); }
};

const fetchAlerts = async () => {
    if (!selectedStoreId.value) return;
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const { data } = await stockApi.getLowStock(selectedStoreId.value);
        alerts.value = Array.isArray(data) ? data : [];
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load alerts.");
    } finally {
        apiState.setLoading(false);
    }
};

const formatDate = (d) => d ? new Date(d).toLocaleString("en-KE") : "—";

onMounted(fetchStores);
</script>