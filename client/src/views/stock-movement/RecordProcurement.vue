<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12" lg="7">
                <b-card no-body>
                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <button class="btn btn-link p-0 me-2 text-muted" @click="router.back()">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                                <span class="fw-semibold fs-5">
                                    <i class="fas fa-truck text-success me-2"></i>Record Procurement
                                </span>
                            </b-col>
                        </b-row>
                    </b-card-header>
                    <b-card-body>
                        <StatesComponent />
                        <form @submit.prevent="save()">

                            <b-row class="g-3">
                                <!-- Branch -->
                                <b-col md="6">
                                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                                    <select v-model="form.branch_id" class="form-select" required
                                        @change="onBranchChange()">
                                        <option value="">Select branch</option>
                                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                </b-col>

                                <!-- Store -->
                                <b-col md="6">
                                    <label class="form-label">Store <span class="text-danger">*</span></label>
                                    <select v-model="form.store_id" class="form-select" required
                                        :disabled="!form.branch_id">
                                        <option value="">Select store</option>
                                        <option v-for="s in filteredStores" :key="s.id" :value="s.id">{{ s.name }}
                                        </option>
                                    </select>
                                </b-col>

                                <!-- SKU -->
                                <b-col md="8">
                                    <label class="form-label">SKU <span class="text-danger">*</span></label>
                                    <select v-model="form.sku_id" class="form-select" required @change="onSkuChange()">
                                        <option value="">Select SKU</option>
                                        <option v-for="s in skus" :key="s.id" :value="s.id">
                                            {{ s.name }} ({{ s.code }})
                                        </option>
                                    </select>
                                </b-col>

                                <!-- Unit -->
                                <b-col md="4">
                                    <label class="form-label">Unit</label>
                                    <input type="text" class="form-control bg-light" readonly
                                        :value="selectedSku?.unit ?? '—'" />
                                </b-col>

                                <!-- Quantity -->
                                <b-col md="4">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input v-model="form.quantity" type="number" class="form-control" min="0.0001"
                                        step="0.0001" required placeholder="0" />
                                </b-col>

                                <!-- Unit Cost -->
                                <b-col md="4">
                                    <label class="form-label">Unit Cost (KES)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input v-model="form.unit_cost" type="number" class="form-control" min="0"
                                            step="0.01" placeholder="0.00" />
                                    </div>
                                    <small class="text-muted" v-if="selectedSku">
                                        SKU cost: {{ formatCurrency(selectedSku.unit_cost) }}
                                    </small>
                                </b-col>

                                <!-- Total -->
                                <b-col md="4">
                                    <label class="form-label">Total (KES)</label>
                                    <input type="text" class="form-control bg-light fw-bold" readonly
                                        :value="formatCurrency(totalCost)" />
                                </b-col>

                                <!-- Notes -->
                                <b-col cols="12">
                                    <label class="form-label">Notes</label>
                                    <textarea v-model="form.notes" class="form-control" rows="2"
                                        placeholder="Supplier, LPO number, batch info..."></textarea>
                                </b-col>
                            </b-row>

                            <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-3">
                                <button type="button" class="btn btn-secondary" @click="router.back()">Cancel</button>
                                <button type="submit" class="btn btn-success" :disabled="apiState.saving">
                                    <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                                    <i class="fas fa-truck me-1"></i> Record Procurement
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
import { useRoute, useRouter } from "vue-router";
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import movementsApi from "@/api/movements/movementsApi";
import branchesApi from "@/api/branches/branchesApi";
import skuApi from "@/api/skus/skuApi";

const route = useRoute();
const router = useRouter();
const apiState = useApiState();

const branches = ref([]);
const stores = ref([]);
const skus = ref([]);

const form = ref({
    branch_id: "",
    store_id: route.query.store_id ?? "",
    sku_id: route.query.sku_id ?? "",
    quantity: "",
    unit_cost: "",
    notes: "",
});

const filteredStores = computed(() =>
    stores.value.filter(s => s.branch_id == form.value.branch_id)
);

const selectedSku = computed(() =>
    skus.value.find(s => s.id == form.value.sku_id) ?? null
);

const totalCost = computed(() => {
    const q = parseFloat(form.value.quantity);
    const c = parseFloat(form.value.unit_cost);
    return (!isNaN(q) && !isNaN(c)) ? q * c : 0;
});

const onBranchChange = () => { form.value.store_id = ""; };
const onSkuChange = () => {
    if (selectedSku.value) form.value.unit_cost = selectedSku.value.unit_cost;
};

const fetchLookups = async () => {
    try {
        const [b, s, sk] = await Promise.all([
            branchesApi.listBranches({ per_page: 100 }),
            branchesApi.listStores({ per_page: 100 }),
            skuApi.listSkus({ per_page: 500, is_active: 1 }),
        ]);
        branches.value = b.data?.data ?? [];
        stores.value = s.data?.data ?? [];
        skus.value = sk.data?.data ?? [];

        // Pre-select branch when store_id is passed via query
        if (form.value.store_id) {
            const store = stores.value.find(s => s.id == form.value.store_id);
            if (store) form.value.branch_id = store.branch_id;
        }
        if (form.value.sku_id) onSkuChange();
    } catch (e) { console.error(e); }
};

const save = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        await movementsApi.recordProcurement({
            store_id: form.value.store_id,
            branch_id: form.value.branch_id,
            sku_id: form.value.sku_id,
            quantity: form.value.quantity,
            unit_cost: form.value.unit_cost || 0,
            notes: form.value.notes,
        });
        apiState.setSuccess(true);
        apiState.setMessage("Procurement recorded. Stock updated.");
        setTimeout(() => router.push({ name: "stock.movement.list" }), 1200);
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to record procurement.");
    } finally {
        apiState.setSaving(false);
    }
};

const formatCurrency = (v) => v != null ? Number(v).toLocaleString("en-KE", { minimumFractionDigits: 2 }) : "0.00";

onMounted(fetchLookups);
</script>