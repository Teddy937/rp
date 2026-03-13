<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">
                <b-card no-body>

                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <b-card-title class="mb-0">SKU Management</b-card-title>
                            </b-col>
                            <b-col cols="auto">
                                <button class="btn btn-primary btn-sm" @click="router.push({ name: 'sku.create' })">
                                    <i class="fas fa-plus me-1"></i> Add SKU
                                </button>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body class="pt-2">
                        <StatesComponent />

                        <!-- Filters -->
                        <b-row class="mb-3 g-2">
                            <b-col md="4">
                                <input v-model="search" type="text" class="form-control" placeholder="Search SKUs..."
                                    @input="debounceSearch()" />
                            </b-col>
                            <b-col md="3">
                                <select v-model="filterActive" class="form-select" @change="fetchSkus()">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </b-col>
                        </b-row>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Unit</th>
                                        <th>Cost (KES)</th>
                                        <th>Price (KES)</th>
                                        <th>Reorder Level</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!skus.data?.length && !apiState.loading">
                                        <td colspan="10" class="text-center py-4 text-muted">No SKUs found.</td>
                                    </tr>
                                    <tr v-for="(sku, i) in skus.data" :key="sku.id">
                                        <td>{{ skus.current_page ? (skus.current_page - 1) * skus.per_page + i + 1 : i +
                                            1 }}</td>
                                        <td class="fw-medium">{{ sku.name }}</td>
                                        <td><span class="badge bg-secondary">{{ sku.code }}</span></td>
                                        <td>{{ sku.unit }}</td>
                                        <td>{{ formatCurrency(sku.unit_cost) }}</td>
                                        <td>{{ formatCurrency(sku.unit_price) }}</td>
                                        <td>
                                            <span
                                                :class="sku.reorder_level > 0 ? 'badge bg-warning text-dark' : 'badge bg-light text-muted'">
                                                {{ sku.reorder_level ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <span :class="sku.is_active ? 'badge bg-success' : 'badge bg-danger'">
                                                {{ sku.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(sku.created_at) }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-outline-warning btn-sm me-1"
                                                @click="router.push({ name: 'sku.edit', params: { id: sku.id } })">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" @click="confirmDelete(sku)">
                                                <i class="fas fa-trash"></i>
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
                                        Page {{ skus.current_page }} of {{ skus.last_page }}
                                    </p>
                                </div>
                                <div class="col-sm-6">
                                    <div class="float-sm-end">
                                        <Bootstrap5Pagination :data="skus" :limit="8"
                                            @pagination-change-page="fetchSkus" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </b-card-body>
                </b-card>
            </b-col>
        </b-row>

        <!-- Delete Confirm Modal -->
        <div class="modal fade" id="deleteSkuModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete SKU</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <StatesComponent />
                        <p>Delete <strong>{{ deleteTarget?.name }}</strong>?<br />
                            <small class="text-muted">This will affect stock records. Cannot be undone.</small>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" :disabled="apiState.saving" @click="doDelete()">
                            <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                            Delete
                        </button>
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
import skuApi from "@/api/skus/skuApi";

const router = useRouter();
const apiState = useApiState();

// ── State ─────────────────────────────────────────────────────────────────
const skus = ref({});
const search = ref("");
const filterActive = ref("");
const deleteTarget = ref(null);
let searchTimer = null;

// ── Fetch ─────────────────────────────────────────────────────────────────
const fetchSkus = async (page = 1) => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const params = { page };
        if (search.value) params.search = search.value;
        if (filterActive.value !== "") params.is_active = filterActive.value;

        const { data } = await skuApi.listSkus(params);
        skus.value = data;
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load SKUs.");
    } finally {
        apiState.setLoading(false);
    }
};

const debounceSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchSkus(1), 400);
};

// ── Delete ────────────────────────────────────────────────────────────────
const confirmDelete = (sku) => {
    deleteTarget.value = sku;
    apiState.resetState();
    $("#deleteSkuModal").modal("show");
};

const doDelete = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await skuApi.deleteSku(deleteTarget.value.id);
        apiState.setSuccess(true);
        apiState.setMessage("SKU deleted successfully.");
        $("#deleteSkuModal").modal("hide");
        await fetchSkus(skus.value.current_page ?? 1);
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to delete SKU.");
    } finally {
        apiState.setSaving(false);
    }
};

// ── Helpers ───────────────────────────────────────────────────────────────
const formatDate = (d) => d ? new Date(d).toLocaleDateString("en-KE") : "—";
const formatCurrency = (v) => v != null ? Number(v).toLocaleString("en-KE", { minimumFractionDigits: 2 }) : "—";

onMounted(() => fetchSkus());
</script>