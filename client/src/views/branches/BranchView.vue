<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">
                <b-card no-body>

                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <b-card-title class="mb-0">Branches</b-card-title>
                            </b-col>
                            <b-col cols="auto">
                                <button v-if="hasPermission('Can create branches')" class="btn btn-primary btn-sm"
                                    @click="openCreate()">
                                    <i class="fas fa-plus me-1"></i> Add Branch
                                </button>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body class="pt-2">
                        <StatesComponent />

                        <!-- Filters -->
                        <b-row class="mb-3 g-2">
                            <b-col md="4">
                                <input v-model="search" type="text" class="form-control"
                                    placeholder="Search branches..." @input="debounceSearch()" />
                            </b-col>
                            <b-col md="3">
                                <select v-model="filterActive" class="form-select" @change="fetchBranches()">
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
                                        <th>Location</th>
                                        <th>Phone</th>
                                        <th>Stores</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!branches.data?.length && !apiState.loading">
                                        <td colspan="9" class="text-center py-4 text-muted">No branches found.</td>
                                    </tr>
                                    <tr v-for="(branch, i) in branches.data" :key="branch.id">
                                        <td>{{ pagination ? (pagination.current_page - 1) * pagination.per_page + i + 1
                                            : i + 1 }}</td>
                                        <td class="fw-medium">{{ branch.name }}</td>
                                        <td><span class="badge bg-secondary">{{ branch.code }}</span></td>
                                        <td>{{ branch.location ?? '—' }}</td>
                                        <td>{{ branch.phone ?? '—' }}</td>
                                        <td>{{ branch.stores_count ?? 0 }}</td>
                                        <td>
                                            <span :class="branch.is_active ? 'badge bg-success' : 'badge bg-danger'">
                                                {{ branch.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(branch.created_at) }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-outline-info btn-sm me-1"
                                                @click="viewDetail(branch.id)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button v-if="hasPermission('Can update branches')"
                                                class="btn btn-outline-warning btn-sm me-1" @click="openEdit(branch)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button v-if="hasPermission('Can delete branches')"
                                                class="btn btn-outline-danger btn-sm" @click="confirmDelete(branch)">
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
                                            Page {{ branches.current_page }} of
                                            {{ branches.last_page }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="float-sm-end">
                                        <Bootstrap5Pagination :data="branches" :limit="8"
                                            @pagination-change-page="fetchBranches" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </b-card-body>
                </b-card>
            </b-col>
        </b-row>

        <!-- Create / Edit Modal -->
        <div class="modal fade" id="branchModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editTarget ? 'Edit Branch' : 'Add Branch' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="save()">
                        <div class="modal-body">
                            <StatesComponent />
                            <div class="mb-2">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input v-model="form.name" type="text" class="form-control" required
                                    placeholder="Branch name" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Code</label>
                                <input v-model="form.code" type="text" class="form-control" :disabled="!!editTarget"
                                    placeholder="e.g. BRA" />
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
                                        id="branchActive" />
                                    <label class="form-check-label" for="branchActive">Active</label>
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
        <div class="modal fade" id="deleteBranchModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Branch</h5>
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
const branches = ref({});
const pagination = ref(null);
const page = ref(1);
const search = ref("");
const filterActive = ref("");
let searchTimer = null;

// ── Modal state ───────────────────────────────────────────────────────────
const editTarget = ref(null);
const deleteTarget = ref(null);
const form = ref({ name: "", code: "", location: "", phone: "", is_active: true });

// ── Fetch ─────────────────────────────────────────────────────────────────
const fetchBranches = async () => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const params = { page: page.value };
        if (search.value) params.search = search.value;
        if (filterActive.value !== "") params.is_active = filterActive.value;

        const { data } = await branchesApi.listBranches(params);
        branches.value = data;
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load branches.");
    } finally {
        apiState.setLoading(false);
    }
};

const debounceSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { page.value = 1; fetchBranches(); }, 400);
};

// ── Create / Edit ─────────────────────────────────────────────────────────
const openCreate = () => {
    editTarget.value = null;
    form.value = { name: "", code: "", location: "", phone: "", is_active: true };
    apiState.resetState();
    $("#branchModal").modal("show");
};

const openEdit = (branch) => {
    editTarget.value = branch;
    form.value = {
        name: branch.name,
        code: branch.code,
        location: branch.location ?? "",
        phone: branch.phone ?? "",
        is_active: branch.is_active,
    };
    apiState.resetState();
    $("#branchModal").modal("show");
};

const save = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        if (editTarget.value) {
            await branchesApi.updateBranch(editTarget.value.id, form.value);
        } else {
            await branchesApi.createBranch(form.value);
        }
        apiState.setSuccess(true);
        apiState.setMessage(editTarget.value ? "Branch updated successfully." : "Branch created successfully.");
        $("#branchModal").modal("hide");
        await fetchBranches();
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Something went wrong.");
    } finally {
        apiState.setSaving(false);
    }
};

// ── Delete ────────────────────────────────────────────────────────────────
const confirmDelete = (branch) => {
    deleteTarget.value = branch;
    apiState.resetState();
    $("#deleteBranchModal").modal("show");
};

const doDelete = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await branchesApi.deleteBranch(deleteTarget.value.id);
        apiState.setSuccess(true);
        apiState.setMessage("Branch deleted successfully.");
        $("#deleteBranchModal").modal("hide");
        await fetchBranches();
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to delete branch.");
    } finally {
        apiState.setSaving(false);
    }
};

// ── Helpers ───────────────────────────────────────────────────────────────
const viewDetail = (id) => router.push({ name: "branches.details", params: { id } });
const formatDate = (d) => d ? new Date(d).toLocaleDateString("en-KE") : "—";

onMounted(fetchBranches);
</script>