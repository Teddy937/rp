<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">
                <b-card no-body>

                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <b-card-title class="mb-0">Audit Logs</b-card-title>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body class="pt-2">
                        <StatesComponent />

                        <!-- Filters -->
                        <b-row class="mb-3 g-2">
                            <b-col md="3">
                                <input v-model="filters.search" type="text" class="form-control"
                                    placeholder="User, description, IP..." @input="debounceSearch()" />
                            </b-col>
                            <b-col md="2">
                                <select v-model="filters.action" class="form-select" @change="fetchLogs()">
                                    <option value="">All Actions</option>
                                    <option value="created">Created</option>
                                    <option value="updated">Updated</option>
                                    <option value="deleted">Deleted</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="login">Login</option>
                                    <option value="logout">Logout</option>
                                </select>
                            </b-col>
                            <b-col md="2">
                                <select v-model="filters.model_type" class="form-select" @change="fetchLogs()">
                                    <option value="">All Models</option>
                                    <option value="User">User</option>
                                    <option value="Branch">Branch</option>
                                    <option value="Store">Store</option>
                                    <option value="Sku">SKU</option>
                                    <option value="StockMovement">Stock Movement</option>
                                </select>
                            </b-col>
                            <b-col md="2">
                                <input v-model="filters.date_from" type="date" class="form-control"
                                    @change="fetchLogs()" />
                            </b-col>
                            <b-col md="2">
                                <input v-model="filters.date_to" type="date" class="form-control"
                                    @change="fetchLogs()" />
                            </b-col>
                        </b-row>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Model</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                        <th>Date</th>
                                        <th class="text-end">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!logs.data?.length && !apiState.loading">
                                        <td colspan="8" class="text-center py-4 text-muted">No audit logs found.</td>
                                    </tr>
                                    <tr v-for="(log, i) in logs.data" :key="log.id">
                                        <td>{{ logs.current_page ? (logs.current_page - 1) * logs.per_page + i + 1 : i +
                                            1 }}</td>
                                        <td>
                                            <div class="fw-medium">{{ log.user?.name ?? 'System' }}</div>
                                            <small class="text-muted">{{ log.user?.email }}</small>
                                        </td>
                                        <td><span :class="actionBadge(log.action)">{{ log.action }}</span></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ log.model_type }}</span>
                                            <small class="text-muted ms-1">#{{ log.model_id }}</small>
                                        </td>
                                        <td>{{ log.description ?? '—' }}</td>
                                        <td><small class="font-monospace">{{ log.ip_address ?? '—' }}</small></td>
                                        <td>{{ formatDate(log.created_at) }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-outline-secondary btn-sm" @click="viewDetails(log)"
                                                :disabled="!log.old_values && !log.new_values">
                                                <i class="fas fa-code"></i>
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
                                    <p class="mb-sm-0">Page {{ logs.current_page }} of {{ logs.last_page }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <div class="float-sm-end">
                                        <Bootstrap5Pagination :data="logs" :limit="8"
                                            @pagination-change-page="fetchLogs" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </b-card-body>
                </b-card>
            </b-col>
        </b-row>

        <!-- Diff Modal -->
        <div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" v-if="selected">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span :class="actionBadge(selected.action)">{{ selected.action }}</span>
                            <span class="ms-2 text-muted fw-normal">{{ selected.model_type }} #{{ selected.model_id
                                }}</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <b-row class="g-3">
                            <b-col md="6" v-if="selected.old_values">
                                <div class="text-muted small fw-semibold mb-1">BEFORE</div>
                                <pre class="bg-light rounded p-2 small mb-0"
                                    style="max-height:300px;overflow:auto">{{ JSON.stringify(selected.old_values, null, 2) }}</pre>
                            </b-col>
                            <b-col md="6" v-if="selected.new_values">
                                <div class="text-muted small fw-semibold mb-1">AFTER</div>
                                <pre class="bg-light rounded p-2 small mb-0"
                                    style="max-height:300px;overflow:auto">{{ JSON.stringify(selected.new_values, null, 2) }}</pre>
                            </b-col>
                        </b-row>
                        <div class="mt-3 pt-2 border-top d-flex gap-4">
                            <div>
                                <span class="text-muted small">By:</span>
                                <strong class="ms-1">{{ selected.user?.name ?? 'System' }}</strong>
                            </div>
                            <div>
                                <span class="text-muted small">IP:</span>
                                <code class="ms-1">{{ selected.ip_address ?? '—' }}</code>
                            </div>
                            <div>
                                <span class="text-muted small">At:</span>
                                <span class="ms-1">{{ formatDate(selected.created_at) }}</span>
                            </div>
                        </div>
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
import { Bootstrap5Pagination } from "laravel-vue-pagination";
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import usersApi from "@/api/users/usersApi";

const apiState = useApiState();

const logs = ref({});
const selected = ref(null);
let searchTimer = null;

const filters = ref({
    search: "", action: "", model_type: "", date_from: "", date_to: "",
});

const fetchLogs = async (page = 1) => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const params = { page };
        Object.entries(filters.value).forEach(([k, v]) => { if (v) params[k] = v; });
        const { data } = await usersApi.listAuditLogs(params);
        logs.value = data;
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load audit logs.");
    } finally {
        apiState.setLoading(false);
    }
};

const debounceSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchLogs(1), 400);
};

const viewDetails = (log) => {
    selected.value = log;
    $("#logDetailModal").modal("show");
};

const actionBadge = (a) => ({
    created: 'badge bg-success',
    updated: 'badge bg-warning text-dark',
    deleted: 'badge bg-danger',
    approved: 'badge bg-info text-dark',
    rejected: 'badge bg-secondary',
    login: 'badge bg-primary',
    logout: 'badge bg-light text-muted border',
}[a] ?? 'badge bg-light text-muted');

const formatDate = (d) => d ? new Date(d).toLocaleString("en-KE") : "—";

onMounted(fetchLogs);
</script>