<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">
                <b-card no-body>

                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <button class="btn btn-link p-0 me-2 text-muted" @click="router.back()">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                                <span class="fw-semibold fs-5">Pending Transfers</span>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body class="pt-2">
                        <StatesComponent />

                        <div v-if="!pending.length && !apiState.loading" class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                            <p class="mb-0">No pending transfers.</p>
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Reference</th>
                                        <th>SKU</th>
                                        <th>From Store</th>
                                        <th>To Store</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Total (KES)</th>
                                        <th>Requested By</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(m, i) in pending" :key="m.id">
                                        <td>{{ i + 1 }}</td>
                                        <td><span class="font-monospace small fw-medium">{{ m.reference_no }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ m.sku?.name }}</div>
                                            <small class="text-muted">{{ m.sku?.code }}</small>
                                        </td>
                                        <td>{{ m.from_store?.name ?? '—' }}</td>
                                        <td>{{ m.to_store?.name ?? '—' }}</td>
                                        <td class="text-end fw-medium">{{ m.quantity }}</td>
                                        <td class="text-end">{{ formatCurrency(m.total_cost) }}</td>
                                        <td>{{ m.created_by?.name ?? '—' }}</td>
                                        <td>{{ formatDate(m.created_at) }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-success btn-sm me-1" @click="confirmApprove(m)">
                                                <i class="fas fa-check me-1"></i> Approve
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" @click="openReject(m)">
                                                <i class="fas fa-times me-1"></i> Reject
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </b-card-body>
                </b-card>
            </b-col>
        </b-row>

        <!-- Approve Confirm Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Transfer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <StatesComponent />
                        <p>Approve transfer <strong>{{ actionTarget?.reference_no }}</strong>?</p>
                        <p class="text-muted small mb-0">
                            {{ actionTarget?.quantity }} {{ actionTarget?.sku?.unit }} of
                            <strong>{{ actionTarget?.sku?.name }}</strong> from
                            {{ actionTarget?.from_store?.name }} → {{ actionTarget?.to_store?.name }}
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-success" :disabled="apiState.saving" @click="doApprove()">
                            <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                            Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Transfer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <StatesComponent />
                        <p class="text-muted small">
                            Rejecting <strong>{{ actionTarget?.reference_no }}</strong> —
                            {{ actionTarget?.quantity }} {{ actionTarget?.sku?.unit }} of
                            {{ actionTarget?.sku?.name }}
                        </p>
                        <div class="mb-2">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea v-model="rejectReason" class="form-control" rows="3"
                                placeholder="Why is this transfer being rejected?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" :disabled="apiState.saving || !rejectReason.trim()"
                            @click="doReject()">
                            <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                            Reject
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
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import movementsApi from "@/api/movements/movementsApi";

const router = useRouter();
const apiState = useApiState();

const pending = ref([]);
const actionTarget = ref(null);
const rejectReason = ref("");

const fetchPending = async () => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const { data } = await movementsApi.getPendingTransfers();
        pending.value = Array.isArray(data) ? data : [];
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load pending transfers.");
    } finally {
        apiState.setLoading(false);
    }
};

const confirmApprove = (m) => {
    actionTarget.value = m;
    apiState.resetState();
    $("#approveModal").modal("show");
};

const doApprove = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await movementsApi.approveTransfer(actionTarget.value.id);
        apiState.setSuccess(true);
        apiState.setMessage("Transfer approved successfully.");
        $("#approveModal").modal("hide");
        await fetchPending();
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to approve transfer.");
    } finally {
        apiState.setSaving(false);
    }
};

const openReject = (m) => {
    actionTarget.value = m;
    rejectReason.value = "";
    apiState.resetState();
    $("#rejectModal").modal("show");
};

const doReject = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await movementsApi.rejectTransfer(actionTarget.value.id, rejectReason.value);
        apiState.setSuccess(true);
        apiState.setMessage("Transfer rejected.");
        $("#rejectModal").modal("hide");
        await fetchPending();
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to reject transfer.");
    } finally {
        apiState.setSaving(false);
    }
};

const formatDate = (d) => d ? new Date(d).toLocaleString("en-KE") : "—";
const formatCurrency = (v) => v != null ? Number(v).toLocaleString("en-KE", { minimumFractionDigits: 2 }) : "—";

onMounted(fetchPending);
</script>