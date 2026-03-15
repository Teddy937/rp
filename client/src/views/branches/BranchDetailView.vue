<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">

                <div v-if="apiState.loading" class="text-center py-5">
                    <span class="spinner-grow spinner-grow-sm me-2"></span> Loading branch...
                </div>

                <template v-else-if="branch">

                    <!-- Header card -->
                    <b-card no-body class="mb-3">
                        <b-card-header class="border-bottom py-2">
                            <b-row class="align-items-center">
                                <b-col>
                                    <button class="btn btn-link p-0 me-2 text-muted" @click="router.back()">
                                        <i class="fas fa-arrow-left"></i>
                                    </button>
                                    <span class="fw-semibold fs-5">{{ branch.name }}</span>
                                    <span :class="branch.is_active ? 'badge bg-success ms-2' : 'badge bg-danger ms-2'">
                                        {{ branch.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </b-col>
                                <b-col cols="auto">
                                    <button class="btn btn-outline-warning btn-sm" @click="openEditBranch()">
                                        <i class="fas fa-edit me-1"></i> Edit Branch
                                    </button>
                                </b-col>
                            </b-row>
                        </b-card-header>
                        <b-card-body>
                            <StatesComponent />
                            <b-row class="g-3">
                                <b-col md="3">
                                    <div class="text-muted small">Code</div>
                                    <span class="badge bg-secondary fs-6">{{ branch.code }}</span>
                                </b-col>
                                <b-col md="3">
                                    <div class="text-muted small">Location</div>
                                    <div class="fw-medium">{{ branch.location ?? '—' }}</div>
                                </b-col>
                                <b-col md="3">
                                    <div class="text-muted small">Phone</div>
                                    <div class="fw-medium">{{ branch.phone ?? '—' }}</div>
                                </b-col>
                                <b-col md="3">
                                    <div class="text-muted small">Created</div>
                                    <div class="fw-medium">{{ formatDate(branch.created_at) }}</div>
                                </b-col>
                            </b-row>
                        </b-card-body>
                    </b-card>

                    <!-- Stores table -->
                    <b-card no-body>
                        <b-card-header class="border-bottom py-2">
                            <b-row class="align-items-center">
                                <b-col>
                                    <b-card-title class="mb-0">
                                        Stores
                                        <span class="badge bg-primary ms-1">{{ branch.stores?.length ?? 0 }}</span>
                                    </b-card-title>
                                </b-col>
                                <b-col cols="auto">
                                    <button class="btn btn-primary btn-sm" @click="openCreateStore()">
                                        <i class="fas fa-plus me-1"></i> Add Store
                                    </button>
                                </b-col>
                            </b-row>
                        </b-card-header>
                        <b-card-body class="p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Location</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!branch.stores?.length">
                                            <td colspan="7" class="text-center py-4 text-muted">No stores yet.</td>
                                        </tr>
                                        <tr v-for="(store, i) in branch.stores" :key="store.id">
                                            <td>{{ i + 1 }}</td>
                                            <td class="fw-medium">{{ store.name }}</td>
                                            <td><span class="badge bg-secondary">{{ store.code }}</span></td>
                                            <td>{{ store.location ?? '—' }}</td>
                                            <td>{{ store.phone ?? '—' }}</td>
                                            <td>
                                                <span :class="store.is_active ? 'badge bg-success' : 'badge bg-danger'">
                                                    {{ store.is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-outline-info btn-sm me-1"
                                                    @click="router.push({ name: 'store.details', params: { id: store.id } })">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-warning btn-sm"
                                                    @click="openEditStore(store)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </b-card-body>
                    </b-card>

                </template>

                <div v-else-if="!apiState.loading" class="text-center py-5 text-muted">Branch not found.</div>

            </b-col>
        </b-row>

        <!-- Edit Branch Modal -->
        <div class="modal fade" id="editBranchModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Branch</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="saveBranch()">
                        <div class="modal-body">
                            <StatesComponent />
                            <div class="mb-2">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input v-model="branchForm.name" type="text" class="form-control" required />
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Location</label>
                                <input v-model="branchForm.location" type="text" class="form-control" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Phone</label>
                                <input v-model="branchForm.phone" type="text" class="form-control" />
                            </div>
                            <div class="mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" v-model="branchForm.is_active"
                                        id="bDetailActive" />
                                    <label class="form-check-label" for="bDetailActive">Active</label>
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

        <!-- Create / Edit Store Modal -->
        <div class="modal fade" id="storeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editStore ? 'Edit Store' : 'Add Store' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="saveStore()">
                        <div class="modal-body">
                            <StatesComponent />
                            <div class="mb-2">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input v-model="storeForm.name" type="text" class="form-control" required />
                            </div>
                            <div class="mb-2" v-if="!editStore">
                                <label class="form-label">Code</label>
                                <input v-model="storeForm.code" type="text" class="form-control"
                                    placeholder="e.g. STA1" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Location</label>
                                <input v-model="storeForm.location" type="text" class="form-control" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Phone</label>
                                <input v-model="storeForm.phone" type="text" class="form-control" />
                            </div>
                            <div class="mb-2" v-if="editStore">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" v-model="storeForm.is_active"
                                        id="storeActive" />
                                    <label class="form-check-label" for="storeActive">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="apiState.saving">
                                <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                                {{ editStore ? 'Update' : 'Create' }}
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

const branch = ref(null);
const editStore = ref(null);

const branchForm = ref({ name: "", location: "", phone: "", is_active: true });
const storeForm = ref({ name: "", code: "", location: "", phone: "", is_active: true });

// ── Fetch ─────────────────────────────────────────────────────────────────
const fetchBranch = async () => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const res = await branchesApi.getBranch(id);
        branch.value = res.data;
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load branch.");
    } finally {
        apiState.setLoading(false);
    }
};

// ── Edit branch ───────────────────────────────────────────────────────────
const openEditBranch = () => {
    branchForm.value = {
        name: branch.value.name,
        location: branch.value.location ?? "",
        phone: branch.value.phone ?? "",
        is_active: branch.value.is_active,
    };
    apiState.resetState();
    $("#editBranchModal").modal("show");
};

const saveBranch = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await branchesApi.updateBranch(id, branchForm.value);
        apiState.setSuccess(true);
        apiState.setMessage("Branch updated successfully.");
        $("#editBranchModal").modal("hide");
        await fetchBranch();
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Something went wrong.");
    } finally {
        apiState.setSaving(false);
    }
};

// ── Store ─────────────────────────────────────────────────────────────────
const openCreateStore = () => {
    editStore.value = null;
    storeForm.value = { name: "", code: "", location: "", phone: "", is_active: true };
    apiState.resetState();
    $("#storeModal").modal("show");
};

const openEditStore = (store) => {
    editStore.value = store;
    storeForm.value = {
        name: store.name,
        location: store.location ?? "",
        phone: store.phone ?? "",
        is_active: store.is_active,
    };
    apiState.resetState();
    $("#storeModal").modal("show");
};

const saveStore = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        if (editStore.value) {
            await branchesApi.updateStore(editStore.value.id, storeForm.value);
        } else {
            await branchesApi.createStore({ ...storeForm.value, branch_id: id });
        }
        apiState.setSuccess(true);
        apiState.setMessage(editStore.value ? "Store updated." : "Store created.");
        $("#storeModal").modal("hide");
        await fetchBranch();
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Something went wrong.");
    } finally {
        apiState.setSaving(false);
    }
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString("en-KE") : "—";

onMounted(fetchBranch);
</script>