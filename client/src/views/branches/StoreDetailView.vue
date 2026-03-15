<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">

                <div v-if="apiState.loading" class="text-center py-5">
                    <span class="spinner-grow spinner-grow-sm me-2"></span> Loading store...
                </div>

                <template v-else-if="store">

                    <b-card no-body class="mb-3">
                        <b-card-header class="border-bottom py-2">
                            <b-row class="align-items-center">
                                <b-col>
                                    <button class="btn btn-link p-0 me-2 text-muted" @click="router.back()">
                                        <i class="fas fa-arrow-left"></i>
                                    </button>
                                    <span class="fw-semibold fs-5">{{ store.name }}</span>
                                    <span :class="store.is_active ? 'badge bg-success ms-2' : 'badge bg-danger ms-2'">
                                        {{ store.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </b-col>
                                <b-col cols="auto" class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary btn-sm"
                                        @click="router.push({ name: 'branches.details', params: { id: store.branch_id } })">
                                        <i class="fas fa-building me-1"></i> View Branch
                                    </button>
                                    <button v-if="hasPermission('Can manage stores')"
                                        class="btn btn-outline-warning btn-sm" @click="openEdit()">
                                        <i class="fas fa-edit me-1"></i> Edit Store
                                    </button>
                                </b-col>
                            </b-row>
                        </b-card-header>
                        <b-card-body>
                            <StatesComponent />
                            <b-row class="g-3">
                                <b-col md="3">
                                    <div class="text-muted small">Code</div>
                                    <span class="badge bg-secondary fs-6">{{ store.code }}</span>
                                </b-col>
                                <b-col md="3">
                                    <div class="text-muted small">Branch</div>
                                    <div class="fw-medium">{{ store.branch?.name ?? '—' }}</div>
                                </b-col>
                                <b-col md="3">
                                    <div class="text-muted small">Location</div>
                                    <div class="fw-medium">{{ store.location ?? '—' }}</div>
                                </b-col>
                                <b-col md="3">
                                    <div class="text-muted small">Phone</div>
                                    <div class="fw-medium">{{ store.phone ?? '—' }}</div>
                                </b-col>
                            </b-row>
                        </b-card-body>
                    </b-card>

                    <!-- Quick action cards -->
                    <b-row class="g-3">
                        <b-col md="4">
                            <b-card class="text-center border-0 shadow-sm h-100" style="cursor:pointer"
                                @click="router.push({ name: 'stock.lists', query: { store_id: store.id } })">
                                <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                <div class="fw-medium">Stock Ledger</div>
                                <small class="text-muted">Current inventory levels</small>
                            </b-card>
                        </b-col>
                        <b-col md="4">
                            <b-card class="text-center border-0 shadow-sm h-100" style="cursor:pointer"
                                @click="router.push({ name: 'stock.movement.list', query: { store_id: store.id } })">
                                <i class="fas fa-exchange-alt fa-2x text-warning mb-2"></i>
                                <div class="fw-medium">Stock Movements</div>
                                <small class="text-muted">Sales, transfers, adjustments</small>
                            </b-card>
                        </b-col>
                        <b-col md="4">
                            <b-card class="text-center border-0 shadow-sm h-100" style="cursor:pointer"
                                @click="router.push({ name: 'stock.alerts', query: { store_id: store.id } })">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                <div class="fw-medium">Low Stock Alerts</div>
                                <small class="text-muted">Items below threshold</small>
                            </b-card>
                        </b-col>
                    </b-row>

                </template>

                <div v-else-if="!apiState.loading" class="text-center py-5 text-muted">Store not found.</div>

            </b-col>
        </b-row>

        <!-- Edit Store Modal -->
        <div class="modal fade" id="editStoreModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Store</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="save()">
                        <div class="modal-body">
                            <StatesComponent />
                            <div class="mb-2">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input v-model="form.name" type="text" class="form-control" required />
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Location</label>
                                <input v-model="form.location" type="text" class="form-control" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Phone</label>
                                <input v-model="form.phone" type="text" class="form-control" />
                            </div>
                            <div class="mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" v-model="form.is_active"
                                        id="storeDetailActive" />
                                    <label class="form-check-label" for="storeDetailActive">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="apiState.saving">
                                <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </DefaultLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import branchesApi from "@/api/branches/branchesApi";
import { hasPermission } from "@/helpers/permissions";

const route = useRoute();
const router = useRouter();
const apiState = useApiState();
const id = route.params.id;

const store = ref(null);
const form = ref({ name: "", location: "", phone: "", is_active: true });

const fetchStore = async () => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const res = await branchesApi.getStore(id);
        store.value = res.data;
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load store.");
    } finally {
        apiState.setLoading(false);
    }
};

const openEdit = () => {
    form.value = {
        name: store.value.name,
        location: store.value.location ?? "",
        phone: store.value.phone ?? "",
        is_active: store.value.is_active,
    };
    apiState.resetState();
    $("#editStoreModal").modal("show");
};

const save = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await branchesApi.updateStore(id, form.value);
        apiState.setSuccess(true);
        apiState.setMessage("Store updated successfully.");
        $("#editStoreModal").modal("hide");
        await fetchStore();
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Something went wrong.");
    } finally {
        apiState.setSaving(false);
    }
};

onMounted(fetchStore);
</script>