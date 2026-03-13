<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">
                <b-card no-body>

                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <b-card-title class="mb-0">Stock Movements</b-card-title>
                            </b-col>
                            <b-col cols="auto" class="d-flex gap-2">
                                <button class="btn btn-outline-warning btn-sm"
                                    @click="router.push({ name: 'stock.movement.pending' })">
                                    <i class="fas fa-clock me-1"></i> Pending
                                    <span v-if="pendingCount > 0" class="badge bg-danger ms-1">{{ pendingCount }}</span>
                                </button>
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-plus me-1"></i> Record
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button class="dropdown-item"
                                                @click="router.push({ name: 'stock.movement.record.procurement' })">
                                                <i class="fas fa-truck me-2 text-success"></i> Procurement
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item"
                                                @click="router.push({ name: 'stock.movement.record.sale' })">
                                                <i class="fas fa-shopping-cart me-2 text-primary"></i> Sale
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item"
                                                @click="router.push({ name: 'stock.movement.record.transfer' })">
                                                <i class="fas fa-exchange-alt me-2 text-warning"></i> Transfer
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item"
                                                @click="router.push({ name: 'stock.movement.record.adjustment' })">
                                                <i class="fas fa-sliders-h me-2 text-secondary"></i> Adjustment
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body class="pt-2">
                        <StatesComponent />

                        <!-- Filters -->
                        <b-row class="mb-3 g-2">
                            <b-col md="3">
                                <input v-model="filters.reference_no" type="text" class="form-control"
                                    placeholder="Reference no..." @input="debounceSearch()" />
                            </b-col>
                            <b-col md="2">
                                <select v-model="filters.type" class="form-select" @change="fetchMovements()">
                                    <option value="">All Types</option>
                                    <option value="procurement">Procurement</option>
                                    <option value="sale">Sale</option>
                                    <option value="transfer_out">Transfer Out</option>
                                    <option value="transfer_in">Transfer In</option>
                                    <option value="adjustment_in">Adjustment In</option>
                                    <option value="adjustment_out">Adjustment Out</option>
                                </select>
                            </b-col>
                            <b-col md="2">
                                <select v-model="filters.status" class="form-select" @change="fetchMovements()">
                                    <option value="">All Status</option>
                                    <option value="completed">Completed</option>
                                    <option value="pending">Pending</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </b-col>
                            <b-col md="2">
                                <input v-model="filters.date_from" type="date" class="form-control"
                                    @change="fetchMovements()" />
                            </b-col>
                            <b-col md="2">
                                <input v-model="filters.date_to" type="date" class="form-control"
                                    @change="fetchMovements()" />
                            </b-col>
                        </b-row>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Reference</th>
                                        <th>Type</th>
                                        <th>SKU</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Total (KES)</th>
                                        <th>Status</th>
                                        <th>By</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!movements.data?.length && !apiState.loading">
                                        <td colspan="12" class="text-center py-4 text-muted">No movements found.</td>
                                    </tr>
                                    <tr v-for="(m, i) in movements.data" :key="m.id">
                                        <td>{{ movements.current_page ? (movements.current_page - 1) *
                                            movements.per_page + i + 1 : i + 1 }}</td>
                                        <td>
                                            <span class="font-monospace small fw-medium">{{ m.reference_no }}</span>
                                        </td>
                                        <td>
                                            <span :class="typeBadge(m.type)">{{ typeLabel(m.type) }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ m.sku?.name }}</div>
                                            <small class="text-muted">{{ m.sku?.code }}</small>
                                        </td>
                                        <td>{{ m.from_store?.name ?? '—' }}</td>
                                        <td>{{ m.to_store?.name ?? '—' }}</td>
                                        <td class="text-end fw-medium">{{ m.quantity }}</td>
                                        <td class="text-end">{{ formatCurrency(m.total_cost) }}</td>
                                        <td>
                                            <span :class="statusBadge(m.status)">{{ m.status }}</span>
                                        </td>
                                        <td>{{ m.created_by?.name ?? '—' }}</td>
                                        <td>{{ formatDate(m.created_at) }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-outline-info btn-sm" @click="viewMovement(m)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="col-md-10 m-auto mt-3">
                            <div class="row">
                                <div class="col-sm-6">
                                    <p class="mb-sm-0">
                                        Page {{ movements.current_page }} of {{ movements.last_page }}
                                    </p>
                                </div>
                                <div class="col-sm-6">
                                    <div class="float-sm-end">
                                        <Bootstrap5Pagination :data="movements" :limit="8"
                                            @pagination-change-page="fetchMovements" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </b-card-body>
                </b-card>
            </b-col>
        </b-row>

        <!-- Detail Modal -->
        <div class="modal fade" id="movementDetailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" v-if="selected">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Movement — <span class="font-monospace">{{ selected.reference_no }}</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <b-row class="g-3">
                            <b-col md="4">
                                <div class="text-muted small">Type</div>
                                <span :class="typeBadge(selected.type)">{{ typeLabel(selected.type) }}</span>
                            </b-col>
                            <b-col md="4">
                                <div class="text-muted small">Status</div>
                                <span :class="statusBadge(selected.status)">{{ selected.status }}</span>
                            </b-col>
                            <b-col md="4">
                                <div class="text-muted small">Date</div>
                                <div class="fw-medium">{{ formatDate(selected.created_at) }}</div>
                            </b-col>
                            <b-col md="6">
                                <div class="text-muted small">SKU</div>
                                <div class="fw-medium">{{ selected.sku?.name }}</div>
                                <small class="text-muted">{{ selected.sku?.code }} · {{ selected.sku?.unit }}</small>
                            </b-col>
                            <b-col md="3">
                                <div class="text-muted small">Quantity</div>
                                <div class="fw-bold fs-5">{{ selected.quantity }}</div>
                            </b-col>
                            <b-col md="3">
                                <div class="text-muted small">Total Cost</div>
                                <div class="fw-bold fs-5">{{ formatCurrency(selected.total_cost) }}</div>
                            </b-col>
                            <b-col md="6" v-if="selected.from_store">
                                <div class="text-muted small">From Store</div>
                                <div class="fw-medium">{{ selected.from_store?.name }}</div>
                            </b-col>
                            <b-col md="6" v-if="selected.to_store">
                                <div class="text-muted small">To Store</div>
                                <div class="fw-medium">{{ selected.to_store?.name }}</div>
                            </b-col>
                            <b-col md="6">
                                <div class="text-muted small">Recorded By</div>
                                <div class="fw-medium">{{ selected.created_by?.name ?? '—' }}</div>
                            </b-col>
                            <b-col md="6" v-if="selected.approved_by">
                                <div class="text-muted small">Approved By</div>
                                <div class="fw-medium">{{ selected.approved_by?.name }}</div>
                            </b-col>
                            <b-col cols="12" v-if="selected.notes">
                                <div class="text-muted small">Notes</div>
                                <div>{{ selected.notes }}</div>
                            </b-col>
                            <b-col cols="12" v-if="selected.rejection_reason">
                                <div class="text-muted small">Rejection Reason</div>
                                <div class="text-danger">{{ selected.rejection_reason }}</div>
                            </b-col>
                        </b-row>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </DefaultLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { Bootstrap5Pagination } from "laravel-vue-pagination";
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import movementsApi from "@/api/movements/movementsApi";

const router = useRouter();
const apiState = useApiState();

const movements = ref({});
const pendingCount = ref(0);
const selected = ref(null);
let searchTimer = null;

const filters = ref({
    reference_no: "",
    type: "",
    status: "",
    date_from: "",
    date_to: "",
});

// ── Fetch ─────────────────────────────────────────────────────────────────
const fetchMovements = async (page = 1) => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const params = { page };
        Object.entries(filters.value).forEach(([k, v]) => { if (v) params[k] = v; });

        const { data } = await movementsApi.listMovements(params);
        movements.value = data;
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load movements.");
    } finally {
        apiState.setLoading(false);
    }
};

const fetchPendingCount = async () => {
    try {
        const { data } = await movementsApi.getPendingTransfers();
        pendingCount.value = Array.isArray(data) ? data.length : 0;
    } catch (e) { console.error(e); }
};

const debounceSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchMovements(1), 400);
};

// ── View detail ───────────────────────────────────────────────────────────
const viewMovement = (m) => {
    selected.value = m;
    $("#movementDetailModal").modal("show");
};

// ── Helpers ───────────────────────────────────────────────────────────────
const typeLabel = (t) => ({
    procurement: 'Procurement',
    sale: 'Sale',
    transfer_out: 'Transfer Out',
    transfer_in: 'Transfer In',
    adjustment_in: 'Adjustment In',
    adjustment_out: 'Adjustment Out',
}[t] ?? t);

const typeBadge = (t) => ({
    procurement: 'badge bg-success',
    sale: 'badge bg-primary',
    transfer_out: 'badge bg-warning text-dark',
    transfer_in: 'badge bg-info text-dark',
    adjustment_in: 'badge bg-secondary',
    adjustment_out: 'badge bg-dark',
}[t] ?? 'badge bg-light text-muted');

const statusBadge = (s) => ({
    completed: 'badge bg-success',
    pending: 'badge bg-warning text-dark',
    rejected: 'badge bg-danger',
    cancelled: 'badge bg-secondary',
}[s] ?? 'badge bg-light');

const formatDate = (d) => d ? new Date(d).toLocaleString("en-KE") : "—";
const formatCurrency = (v) => v != null ? Number(v).toLocaleString("en-KE", { minimumFractionDigits: 2 }) : "—";

onMounted(() => { fetchMovements(); fetchPendingCount(); });
</script>