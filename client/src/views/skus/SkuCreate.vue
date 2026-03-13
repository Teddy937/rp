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
                                <span class="fw-semibold fs-5">{{ isEdit ? 'Edit SKU' : 'Add New SKU' }}</span>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body>
                        <StatesComponent />

                        <div v-if="apiState.loading" class="text-center py-4">
                            <span class="spinner-grow spinner-grow-sm me-2"></span> Loading...
                        </div>

                        <form v-else @submit.prevent="save()">

                            <!-- Basic Info -->
                            <div class="mb-4">
                                <h6 class="text-muted text-uppercase fw-semibold mb-3"
                                    style="font-size:11px;letter-spacing:.08em">
                                    Basic Information
                                </h6>
                                <b-row class="g-3">
                                    <b-col md="8">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input v-model="form.name" type="text" class="form-control" required
                                            placeholder="e.g. Unga Pembe 2kg" />
                                    </b-col>
                                    <b-col md="4">
                                        <label class="form-label">Code</label>
                                        <input v-model="form.code" type="text" class="form-control" :disabled="isEdit"
                                            placeholder="Auto-generated" />
                                        <small v-if="isEdit" class="text-muted">Code cannot be changed after
                                            creation.</small>
                                    </b-col>
                                    <b-col md="4">
                                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                                        <select v-model="form.unit" class="form-select" required>
                                            <option value="">Select unit</option>
                                            <option value="piece">Piece</option>
                                            <option value="kg">Kilogram (kg)</option>
                                            <option value="g">Gram (g)</option>
                                            <option value="litre">Litre (L)</option>
                                            <option value="ml">Millilitre (ml)</option>
                                            <option value="pack">Pack</option>
                                            <option value="box">Box</option>
                                            <option value="carton">Carton</option>
                                            <option value="dozen">Dozen</option>
                                            <option value="bag">Bag</option>
                                        </select>
                                    </b-col>
                                    <b-col md="8">
                                        <label class="form-label">Description</label>
                                        <textarea v-model="form.description" class="form-control" rows="2"
                                            placeholder="Optional product description..."></textarea>
                                    </b-col>
                                </b-row>
                            </div>

                            <hr class="my-3" />

                            <!-- Pricing -->
                            <div class="mb-4">
                                <h6 class="text-muted text-uppercase fw-semibold mb-3"
                                    style="font-size:11px;letter-spacing:.08em">
                                    Pricing
                                </h6>
                                <b-row class="g-3">
                                    <b-col md="4">
                                        <label class="form-label">Unit Cost (KES) <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">KES</span>
                                            <input v-model="form.unit_cost" type="number" class="form-control" min="0"
                                                step="0.01" required placeholder="0.00" />
                                        </div>
                                    </b-col>
                                    <b-col md="4">
                                        <label class="form-label">Unit Price (KES) <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">KES</span>
                                            <input v-model="form.unit_price" type="number" class="form-control" min="0"
                                                step="0.01" required placeholder="0.00" />
                                        </div>
                                    </b-col>
                                    <b-col md="4">
                                        <label class="form-label">Margin</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">%</span>
                                            <input type="text" class="form-control bg-light" readonly
                                                :value="marginPercent" />
                                        </div>
                                        <small class="text-muted">Auto-calculated</small>
                                    </b-col>
                                </b-row>
                            </div>

                            <hr class="my-3" />

                            <!-- Stock Settings -->
                            <div class="mb-4">
                                <h6 class="text-muted text-uppercase fw-semibold mb-3"
                                    style="font-size:11px;letter-spacing:.08em">
                                    Stock Settings
                                </h6>
                                <b-row class="g-3">
                                    <b-col md="4">
                                        <label class="form-label">Reorder Level</label>
                                        <input v-model="form.reorder_level" type="number" class="form-control" min="0"
                                            placeholder="0" />
                                        <small class="text-muted">Alert when stock falls below this.</small>
                                    </b-col>
                                    <b-col md="4" class="d-flex align-items-end">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" v-model="form.is_active"
                                                id="skuActive" />
                                            <label class="form-check-label fw-medium" for="skuActive">
                                                Active
                                            </label>
                                        </div>
                                    </b-col>
                                </b-row>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                <button type="button" class="btn btn-secondary" @click="router.back()">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-primary" :disabled="apiState.saving">
                                    <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                                    {{ isEdit ? 'Update SKU' : 'Create SKU' }}
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
import skuApi from "@/api/skus/skuApi";

const route = useRoute();
const router = useRouter();
const apiState = useApiState();

// ── Edit vs Create ────────────────────────────────────────────────────────
const isEdit = computed(() => !!route.params.id);
const id = route.params.id;

// ── Form ──────────────────────────────────────────────────────────────────
const form = ref({
    name: "",
    code: "",
    unit: "",
    description: "",
    unit_cost: "",
    unit_price: "",
    reorder_level: 0,
    is_active: true,
});

// ── Auto margin ───────────────────────────────────────────────────────────
const marginPercent = computed(() => {
    const cost = parseFloat(form.value.unit_cost);
    const price = parseFloat(form.value.unit_price);
    if (!cost || !price || cost === 0) return "—";
    const margin = ((price - cost) / price) * 100;
    return margin.toFixed(1) + "%";
});

// ── Load existing SKU for edit ────────────────────────────────────────────
const fetchSku = async () => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const { data } = await skuApi.getSku(id);
        form.value = {
            name: data.name,
            code: data.code,
            unit: data.unit,
            description: data.description ?? "",
            unit_cost: data.unit_cost,
            unit_price: data.unit_price,
            reorder_level: data.reorder_level ?? 0,
            is_active: data.is_active,
        };
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load SKU.");
    } finally {
        apiState.setLoading(false);
    }
};

// ── Save ──────────────────────────────────────────────────────────────────
const save = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        if (isEdit.value) {
            await skuApi.updateSku(id, form.value);
        } else {
            await skuApi.createSku(form.value);
        }
        apiState.setSuccess(true);
        apiState.setMessage(isEdit.value ? "SKU updated successfully." : "SKU created successfully.");
        setTimeout(() => router.push({ name: "sku.view" }), 1200);
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Something went wrong.");
        if (e.errors) {
            const flat = Object.values(e.errors).flat();
            apiState.setErrors(flat);
        }
    } finally {
        apiState.setSaving(false);
    }
};

onMounted(() => {
    if (isEdit.value) fetchSku();
});
</script>