<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12" lg="8">
                <b-card no-body>
                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <button class="btn btn-link p-0 me-2 text-muted" @click="router.back()">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                                <span class="fw-semibold fs-5">
                                    <i class="fas fa-exchange-alt text-warning me-2"></i>Initiate Transfer
                                </span>
                            </b-col>
                        </b-row>
                    </b-card-header>
                    <b-card-body>
                        <StatesComponent />
                        <form @submit.prevent="save()">
                            <b-row class="g-3">

                                <!-- FROM -->
                                <b-col cols="12">
                                    <h6 class="text-muted text-uppercase fw-semibold mb-0"
                                        style="font-size:11px;letter-spacing:.08em">
                                        From
                                    </h6>
                                </b-col>
                                <b-col md="6">
                                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                                    <select v-model="form.from_branch_id" class="form-select" required
                                        @change="onFromBranchChange()">
                                        <option value="">Select branch</option>
                                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                </b-col>
                                <b-col md="6">
                                    <label class="form-label">Store <span class="text-danger">*</span></label>
                                    <select v-model="form.from_store_id" class="form-select" required
                                        :disabled="!form.from_branch_id" @change="onFromStoreChange()">
                                        <option value="">Select store</option>
                                        <option v-for="s in fromStores" :key="s.id" :value="s.id">{{ s.name }}</option>
                                    </select>
                                </b-col>

                                <hr class="my-1" />

                                <!-- TO -->
                                <b-col cols="12">
                                    <h6 class="text-muted text-uppercase fw-semibold mb-0"
                                        style="font-size:11px;letter-spacing:.08em">
                                        To
                                    </h6>
                                </b-col>
                                <b-col md="6">
                                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                                    <select v-model="form.to_branch_id" class="form-select" required>
                                        <option value="">Select branch</option>
                                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                </b-col>
                                <b-col md="6">
                                    <label class="form-label">Store <span class="text-danger">*</span></label>
                                    <select v-model="form.to_store_id" class="form-select" required
                                        :disabled="!form.to_branch_id">
                                        <option value="">Select store</option>
                                        <option v-for="s in toStores" :key="s.id" :value="s.id"
                                            :disabled="s.id == form.from_store_id">
                                            {{ s.name }}
                                        </option>
                                    </select>
                                </b-col>

                                <hr class="my-1" />

                                <!-- SKU + Qty -->
                                <b-col md="8">
                                    <label class="form-label">SKU <span class="text-danger">*</span></label>
                                    <select v-model="form.sku_id" class="form-select" required
                                        :disabled="!form.from_store_id" @change="onSkuChange()">
                                        <option value="">Select SKU</option>
                                        <option v-for="s in fromStoreSkus" :key="s.sku_id" :value="s.sku_id"
                                            :disabled="s.quantity <= 0">
                                            {{ s.sku?.name }} — Stock: {{ s.quantity }} {{ s.sku?.unit }}
                                        </option>
                                    </select>
                                </b-col>
                                <b-col md="4">
                                    <label class="form-label">Available</label>
                                    <input type="text" class="form-control bg-light fw-bold" readonly
                                        :class="availableStock <= 0 ? 'text-danger' : 'text-success'"
                                        :value="selectedLedger ? `${availableStock} ${selectedSku?.unit ?? ''}` : '—'" />
                                </b-col>
                                <b-col md="4">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input v-model="form.quantity" type="number" class="form-control"
                                        :max="availableStock" min="0.0001" step="0.0001" required placeholder="0" />
                                    <small v-if="form.quantity > availableStock" class="text-danger">Exceeds available
                                        stock.</small>
                                </b-col>
                                <b-col md="4">
                                    <label class="form-label">Unit Cost (KES)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input v-model="form.unit_cost" type="number" class="form-control" min="0"
                                            step="0.01" placeholder="0.00" />
                                    </div>
                                </b-col>
                                <b-col md="4">
                                    <label class="form-label">Total (KES)</label>
                                    <input type="text" class="form-control bg-light fw-bold" readonly
                                        :value="formatCurrency(totalCost)" />
                                </b-col>
                                <b-col cols="12">
                                    <label class="form-label">Notes</label>
                                    <textarea v-model="form.notes" class="form-control" rows="2"
                                        placeholder="Reason for transfer..."></textarea>
                                </b-col>
                                <b-col cols="12">
                                    <div class="alert alert-warning py-2 mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Transfer will be <strong>pending approval</strong> by a Branch/Admin manager
                                        before stock is moved.
                                    </div>
                                </b-col>
                            </b-row>
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-3">
                                <button type="button" class="btn btn-secondary" @click="router.back()">Cancel</button>
                                <button type="submit" class="btn btn-warning"
                                    :disabled="apiState.saving || form.quantity > availableStock">
                                    <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                                    <i class="fas fa-exchange-alt me-1"></i> Initiate Transfer
                                </button>
                            </div>
                        </form>
                    </b-card-body>
                </b-card>
            </b-col>
        </b-row>
    </DefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import movementsApi from "@/api/movements/movementsApi";
import branchesApi from "@/api/branches/branchesApi";
import stockApi from "@/api/stock/stockApi";

const router = useRouter();
const apiState = useApiState();

const branches = ref([]);
const stores = ref([]);
const fromStoreSkus = ref([]);

const form = ref({
    from_branch_id: "", from_store_id: "",
    to_branch_id: "", to_store_id: "",
    sku_id: "", quantity: "", unit_cost: "", notes: "",
});

const fromStores = computed(() => stores.value.filter(s => s.branch_id == form.value.from_branch_id));
const toStores = computed(() => stores.value.filter(s => s.branch_id == form.value.to_branch_id));
const selectedLedger = computed(() => fromStoreSkus.value.find(s => s.sku_id == form.value.sku_id) ?? null);
const selectedSku = computed(() => selectedLedger.value?.sku ?? null);
const availableStock = computed(() => selectedLedger.value ? parseFloat(selectedLedger.value.quantity) : 0);
const totalCost = computed(() => {
    const q = parseFloat(form.value.quantity);
    const c = parseFloat(form.value.unit_cost);
    return (!isNaN(q) && !isNaN(c)) ? q * c : 0;
});

const onFromBranchChange = () => { form.value.from_store_id = ""; fromStoreSkus.value = []; form.value.sku_id = ""; };
const onFromStoreChange = async () => {
    form.value.sku_id = "";
    fromStoreSkus.value = [];
    if (!form.value.from_store_id) return;
    try {
        const { data } = await stockApi.getStoreStock(form.value.from_store_id);
        fromStoreSkus.value = Array.isArray(data) ? data : [];
    } catch (e) { console.error(e); }
};
const onSkuChange = () => {
    if (selectedSku.value) form.value.unit_cost = selectedSku.value.unit_cost;
};

const save = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await movementsApi.recordTransfer({
            from_branch_id: form.value.from_branch_id,
            from_store_id: form.value.from_store_id,
            to_branch_id: form.value.to_branch_id,
            to_store_id: form.value.to_store_id,
            sku_id: form.value.sku_id,
            quantity: form.value.quantity,
            unit_cost: form.value.unit_cost || 0,
            notes: form.value.notes,
        });
        apiState.setSuccess(true);
        apiState.setMessage("Transfer initiated. Awaiting approval.");
        setTimeout(() => router.push({ name: "stock.movement.pending" }), 1200);
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to initiate transfer.");
    } finally {
        apiState.setSaving(false);
    }
};

const formatCurrency = (v) => v != null ? Number(v).toLocaleString("en-KE", { minimumFractionDigits: 2 }) : "0.00";

onMounted(async () => {
    try {
        const [b, s] = await Promise.all([
            branchesApi.listBranches({ per_page: 100 }),
            branchesApi.listStores({ per_page: 100 }),
        ]);
        branches.value = b.data?.data ?? [];
        stores.value = s.data?.data ?? [];
    } catch (e) { console.error(e); }
});
</script>