<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">
                <b-card no-body>

                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <b-card-title class="mb-0">Users</b-card-title>
                            </b-col>
                            <b-col cols="auto">
                                <button class="btn btn-primary btn-sm" @click="router.push({ name: 'users.create' })">
                                    <i class="fas fa-plus me-1"></i> Add User
                                </button>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body class="pt-2">
                        <StatesComponent />

                        <!-- Filters -->
                        <b-row class="mb-3 g-2">
                            <b-col md="4">
                                <input v-model="filters.search" type="text" class="form-control"
                                    placeholder="Name, email or phone..." @input="debounceSearch()" />
                            </b-col>
                            <b-col md="2">
                                <select v-model="filters.role" class="form-select" @change="fetchUsers()">
                                    <option value="">All Roles</option>
                                    <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                                </select>
                            </b-col>
                            <b-col md="2">
                                <select v-model="filters.account_status" class="form-select" @change="fetchUsers()">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="pending">Pending</option>
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
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Branch / Store</th>
                                        <th>Status</th>
                                        <th>Online</th>
                                        <th>Last Login</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!users.data?.length && !apiState.loading">
                                        <td colspan="10" class="text-center py-4 text-muted">No users found.</td>
                                    </tr>
                                    <tr v-for="(user, i) in users.data" :key="user.id">
                                        <td>{{ users.current_page ? (users.current_page - 1) * users.per_page + i + 1 :
                                            i + 1 }}</td>
                                        <td>
                                            <div class="fw-medium">{{ user.name }}</div>
                                        </td>
                                        <td>{{ user.email }}</td>
                                        <td>{{ user.phone ?? '—' }}</td>
                                        <td>
                                            <span v-for="r in user.roles" :key="r" :class="roleBadge(r)">{{ r.name
                                            }}</span>
                                        </td>
                                        <td>
                                            <div v-if="user.branch">
                                                <small class="text-muted">Branch:</small> {{ user.branch.name }}
                                            </div>
                                            <div v-if="user.store">
                                                <small class="text-muted">Store:</small> {{ user.store.name }}
                                            </div>
                                            <span v-if="!user.branch && !user.store" class="text-muted">—</span>
                                        </td>
                                        <td>
                                            <span :class="statusBadge(user.account_status)">
                                                {{ user.account_status }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                :class="user.is_online ? 'badge bg-success' : 'badge bg-light text-muted'">
                                                {{ user.is_online ? 'Online' : 'Offline' }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(user.last_login_at) }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-outline-warning btn-sm me-1"
                                                @click="router.push({ name: 'users.edit', params: { id: user.id } })">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-info btn-sm me-1"
                                                @click="openResetPassword(user)">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary btn-sm me-1"
                                                @click="openStatusModal(user)">
                                                <i class="fas fa-user-cog"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" @click="confirmDelete(user)">
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
                                    <p class="mb-sm-0">Page {{ users.current_page }} of {{ users.last_page }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <div class="float-sm-end">
                                        <Bootstrap5Pagination :data="users" :limit="8"
                                            @pagination-change-page="fetchUsers" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </b-card-body>
                </b-card>
            </b-col>
        </b-row>

        <!-- Reset Password Modal -->
        <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reset Password — {{ actionTarget?.name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="doResetPassword()">
                        <div class="modal-body">
                            <StatesComponent />
                            <div class="mb-2">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
                                <input v-model="passwordForm.password" type="password" class="form-control" required
                                    minlength="8" />
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input v-model="passwordForm.password_confirmation" type="password" class="form-control"
                                    required />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info" :disabled="apiState.saving">
                                <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                                Reset Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Status Modal -->
        <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <StatesComponent />
                        <p class="text-muted small mb-2">{{ actionTarget?.name }}</p>
                        <select v-model="newStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" :disabled="apiState.saving" @click="doToggleStatus()">
                            <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                            Update
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <StatesComponent />
                        <p>Delete <strong>{{ actionTarget?.name }}</strong>? This cannot be undone.</p>
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
import usersApi from "@/api/users/usersApi";

const router = useRouter();
const apiState = useApiState();

const users = ref({});
const roles = ref([]);
let searchTimer = null;

const filters = ref({ search: "", role: "", account_status: "" });

const actionTarget = ref(null);
const newStatus = ref("active");
const passwordForm = ref({ password: "", password_confirmation: "" });

// ── Fetch ─────────────────────────────────────────────────────────────────
const fetchUsers = async (page = 1) => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const params = { page };
        Object.entries(filters.value).forEach(([k, v]) => { if (v) params[k] = v; });
        const { data } = await usersApi.listUsers(params);
        users.value = data;
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load users.");
    } finally {
        apiState.setLoading(false);
    }
};

const fetchRoles = async () => {
    try {
        const { data } = await usersApi.getRoles();
        roles.value = Array.isArray(data) ? data : [];
    } catch (e) { console.error(e); }
};

const debounceSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchUsers(1), 400);
};

// ── Reset Password ────────────────────────────────────────────────────────
const openResetPassword = (user) => {
    actionTarget.value = user;
    passwordForm.value = { password: "", password_confirmation: "" };
    apiState.resetState();
    $("#resetPasswordModal").modal("show");
};

const doResetPassword = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await usersApi.resetPassword(actionTarget.value.id, passwordForm.value);
        apiState.setSuccess(true);
        apiState.setMessage("Password reset successfully.");
        $("#resetPasswordModal").modal("hide");
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to reset password.");
    } finally {
        apiState.setSaving(false);
    }
};

// ── Status ────────────────────────────────────────────────────────────────
const openStatusModal = (user) => {
    actionTarget.value = user;
    newStatus.value = user.account_status;
    apiState.resetState();
    $("#statusModal").modal("show");
};

const doToggleStatus = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await usersApi.toggleStatus(actionTarget.value.id, newStatus.value);
        apiState.setSuccess(true);
        apiState.setMessage("Status updated successfully.");
        $("#statusModal").modal("hide");
        await fetchUsers(users.value.current_page ?? 1);
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to update status.");
    } finally {
        apiState.setSaving(false);
    }
};

// ── Delete ────────────────────────────────────────────────────────────────
const confirmDelete = (user) => {
    actionTarget.value = user;
    apiState.resetState();
    $("#deleteUserModal").modal("show");
};

const doDelete = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await usersApi.deleteUser(actionTarget.value.id);
        apiState.setSuccess(true);
        apiState.setMessage("User deleted successfully.");
        $("#deleteUserModal").modal("hide");
        await fetchUsers(users.value.current_page ?? 1);
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to delete user.");
    } finally {
        apiState.setSaving(false);
    }
};

// ── Helpers ───────────────────────────────────────────────────────────────
const roleBadge = (r) => ({
    'Administrator': 'badge bg-danger me-1',
    'Branch Manager': 'badge bg-warning text-dark me-1',
    'Store Manager': 'badge bg-info text-dark me-1',
}[r] ?? 'badge bg-secondary me-1');

const statusBadge = (s) => ({
    active: 'badge bg-success',
    inactive: 'badge bg-secondary',
    suspended: 'badge bg-danger',
    pending: 'badge bg-warning text-dark',
}[s] ?? 'badge bg-light');

const formatDate = (d) => d ? new Date(d).toLocaleString("en-KE") : "—";

onMounted(() => { fetchRoles(); fetchUsers(); });
</script>