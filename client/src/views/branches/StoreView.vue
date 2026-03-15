<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">
                <b-card no-body>

                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <b-card-title class="mb-0">Stores</b-card-title>
                            </b-col>
                            <b-col cols="auto">
                                <button v-if="hasPermission('Can manage stores')" class="btn btn-primary btn-sm"
                                    @click="openCreate()">
                                    <i class="fas fa-plus me-1"></i> Add Store
                                </button>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body class="pt-2">
                        <StatesComponent />

                        <!-- Filters -->
                        <b-row class="mb-3 g-2">
                            <b-col md="4">
                                <input v-model="search" type="text" class="form-control" placeholder="Search stores..."
                                    @input="debounceSearch()" />
                            </b-col>
                            <b-col md="3">
                                <select v-model="filterBranch" class="form-select" @change="fetchStores()">
                                    <option value="">All Branches</option>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                            </b-col>
                            <b-col md="2">
                                <select v-model="filterActive" class="form-select" @change="fetchStores()">
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
                                        <th>Branch</th>
                                        <th>Location</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!stores.data?.length && !apiState.loading">
                                        <td colspan="9" class="text-center py-4 text-muted">No stores found.</td>
                                    </tr>
                                    <tr v-for="(store, i) in stores.data" :key="store.id">
                                        <td>{{ stores.current_page ? (stores.current_page - 1) * stores.per_page + i + 1
                                            : i + 1 }}</td>
                                        <td class="fw-medium">{{ store.name }}</td>
                                        <td><span class="badge bg-secondary">{{ store.code }}</span></td>
                                        <td>{{ store.branch?.name ?? '—' }}</td>
                                        <td>{{ store.location ?? '—' }}</td>
                                        <td>{{ store.phone ?? '—' }}</td>
                                        <td>
                                            <span :class="store.is_active ? 'badge bg-success' : 'badge bg-danger'">
                                                {{ store.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(store.created_at) }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-outline-info btn-sm me-1"
                                                @click="router.push({ name: 'store.details', params: { id: store.id } })">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning btn-sm me-1"
                                                @click="openEdit(store)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" @click="confirmDelete(store)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="col-md-10 m-auto">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div>
                                        <p class="mb-sm-0">
                                            Page {{ stores.current_page }} of
                                            {{ stores.last_page }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="float-sm-end">
                                        <Bootstrap5Pagination :data="stores" :limit="8"
                                            @pagination-change-page="fetchStores" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </b-card-body>
                </b-card>
            </b-col>
        </b-row>

        <!-- Create / Edit Modal -->
        <div class="modal fade" id="storeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editTarget ? 'Edit Store' : 'Add Store' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="save()">
                        <div class="modal-body">
                            <StatesComponent />
                            <div class="mb-2">
                                <label class="form-label">Branch <span class="text-danger">*</span></label>
                                <select v-model="form.branch_id" class="form-select" required>
                                    <option value="">Select branch</option>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input v-model="form.name" type="text" class="form-control" required
                                    placeholder="Store name" />
                            </div>
                            <div class="mb-2" v-if="!editTarget">
                                <label class="form-label">Code</label>
                                <input v-model="form.code" type="text" class="form-control" placeholder="e.g. STA1" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Location</label>
                                <input v-model="form.location" type="text" class="form-control"
                                    placeholder="City / Town" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Phone</label>
                                <input v-model="form.phone" type="text" class="form-control" placeholder="+254..." />
                            </div>
                            <div class="mb-2" v-if="editTarget">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" v-model="form.is_active"
                                        id="storeActiveFlag" />
                                    <label class="form-check-label" for="storeActiveFlag">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="apiState.saving">
                                <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                                {{ editTarget ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirm Modal -->
        <div class="modal fade" id="deleteStoreModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Store</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <StatesComponent />
                        <p>Delete <strong>{{ deleteTarget?.name }}</strong>? This cannot be undone.</p>
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
import branchesApi from "@/api/branches/branchesApi";
import { hasPermission } from "@/helpers/permissions";


const router = useRouter();
const apiState = useApiState();

// ── State ─────────────────────────────────────────────────────────────────
const stores = ref({});
const branches = ref([]);  // flat list for dropdowns only
const page = ref(1);
const search = ref("");
const filterBranch = ref("");
const filterActive = ref("");
let searchTimer = null;

// ── Modal state ───────────────────────────────────────────────────────────
const editTarget = ref(null);
const deleteTarget = ref(null);
const form = ref({ branch_id: "", name: "", code: "", location: "", phone: "", is_active: true });

// ── Fetch ─────────────────────────────────────────────────────────────────
const fetchStores = async (p = page.value) => {
    page.value = p;
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const params = { page: page.value };
        if (search.value) params.search = search.value;
        if (filterBranch.value) params.branch_id = filterBranch.value;
        if (filterActive.value !== "") params.is_active = filterActive.value;

        const { data } = await branchesApi.listStores(params);
        stores.value = data;
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load stores.");
    } finally {
        apiState.setLoading(false);
    }
};

const fetchBranchList = async () => {
    try {
        const { data } = await branchesApi.listBranches({ per_page: 100 });
        branches.value = data.data ?? [];
    } catch (e) { console.error(e); }
};

const debounceSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { page.value = 1; fetchStores(); }, 400);
};

// ── Create / Edit ─────────────────────────────────────────────────────────
const openCreate = () => {
    editTarget.value = null;
    form.value = { branch_id: filterBranch.value || "", name: "", code: "", location: "", phone: "", is_active: true };
    apiState.resetState();
    $("#storeModal").modal("show");
};

const openEdit = (store) => {
    editTarget.value = store;
    form.value = {
        branch_id: store.branch_id,
        name: store.name,
        location: store.location ?? "",
        phone: store.phone ?? "",
        is_active: store.is_active,
    };
    apiState.resetState();
    $("#storeModal").modal("show");
};

const save = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        if (editTarget.value) {
            await branchesApi.updateStore(editTarget.value.id, form.value);
        } else {
            await branchesApi.createStore(form.value);
        }
        apiState.setSuccess(true);
        apiState.setMessage(editTarget.value ? "Store updated successfully." : "Store created successfully.");
        $("#storeModal").modal("hide");
        await fetchStores();
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Something went wrong.");
    } finally {
        apiState.setSaving(false);
    }
};

// ── Delete ────────────────────────────────────────────────────────────────
const confirmDelete = (store) => {
    deleteTarget.value = store;
    apiState.resetState();
    $("#deleteStoreModal").modal("show");
};

const doDelete = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await branchesApi.deleteStore(deleteTarget.value.id);
        apiState.setSuccess(true);
        apiState.setMessage("Store deleted successfully.");
        $("#deleteStoreModal").modal("hide");
        await fetchStores();
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to delete store.");
    } finally {
        apiState.setSaving(false);
    }
};

// ── Helpers ───────────────────────────────────────────────────────────────
const formatDate = (d) => d ? new Date(d).toLocaleDateString("en-KE") : "—";

onMounted(() => { fetchBranchList(); fetchStores(); });
</script>