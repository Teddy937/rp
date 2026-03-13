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
                                <span class="fw-semibold fs-5">{{ isEdit ? 'Edit User' : 'Add New User' }}</span>
                            </b-col>
                        </b-row>
                    </b-card-header>
                    <b-card-body>
                        <StatesComponent />

                        <div v-if="apiState.loading" class="text-center py-4">
                            <span class="spinner-grow spinner-grow-sm me-2"></span> Loading...
                        </div>

                        <form v-else @submit.prevent="save()">

                            <!-- Identity -->
                            <h6 class="text-muted text-uppercase fw-semibold mb-3"
                                style="font-size:11px;letter-spacing:.08em">
                                Identity
                            </h6>
                            <b-row class="g-3 mb-4">
                                <b-col md="6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input v-model="form.name" type="text" class="form-control" required />
                                </b-col>
                                <b-col md="6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input v-model="form.email" type="email" class="form-control" required />
                                </b-col>
                                <b-col md="4">
                                    <label class="form-label">Phone</label>
                                    <input v-model="form.phone" type="text" class="form-control"
                                        placeholder="+254..." />
                                </b-col>
                                <b-col md="4">
                                    <label class="form-label">National ID</label>
                                    <input v-model="form.national_id" type="text" class="form-control" />
                                </b-col>
                                <b-col md="4">
                                    <label class="form-label">Gender</label>
                                    <select v-model="form.gender" class="form-select">
                                        <option value="">Select</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </b-col>
                                <b-col md="4">
                                    <label class="form-label">Date of Birth</label>
                                    <input v-model="form.date_of_birth" type="date" class="form-control" />
                                </b-col>
                                <b-col md="8">
                                    <label class="form-label">Address</label>
                                    <input v-model="form.address" type="text" class="form-control" />
                                </b-col>
                            </b-row>

                            <hr class="my-3" />

                            <!-- Role & Assignment -->
                            <h6 class="text-muted text-uppercase fw-semibold mb-3"
                                style="font-size:11px;letter-spacing:.08em">
                                Role & Assignment
                            </h6>
                            <b-row class="g-3 mb-4">
                                <b-col md="4">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select v-model="form.role" class="form-select" required @change="onRoleChange()">
                                        <option value="">Select role</option>
                                        <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                                    </select>
                                </b-col>
                                <b-col md="4" v-if="needsBranch">
                                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                                    <select v-model="form.branch_id" class="form-select" :required="needsBranch"
                                        @change="form.store_id = ''">
                                        <option value="">Select branch</option>
                                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                </b-col>
                                <b-col md="4" v-if="needsStore">
                                    <label class="form-label">Store <span class="text-danger">*</span></label>
                                    <select v-model="form.store_id" class="form-select" :required="needsStore"
                                        :disabled="!form.branch_id">
                                        <option value="">Select store</option>
                                        <option v-for="s in filteredStores" :key="s.id" :value="s.id">{{ s.name }}
                                        </option>
                                    </select>
                                </b-col>
                                <b-col md="4" v-if="isEdit">
                                    <label class="form-label">Account Status</label>
                                    <select v-model="form.account_status" class="form-select">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </b-col>
                            </b-row>

                            <hr v-if="!isEdit" class="my-3" />

                            <!-- Password (create only) -->
                            <template v-if="!isEdit">
                                <h6 class="text-muted text-uppercase fw-semibold mb-3"
                                    style="font-size:11px;letter-spacing:.08em">
                                    Password
                                </h6>
                                <b-row class="g-3 mb-4">
                                    <b-col md="6">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <input v-model="form.password" type="password" class="form-control" required
                                            minlength="8" />
                                    </b-col>
                                    <b-col md="6">
                                        <label class="form-label">Confirm Password <span
                                                class="text-danger">*</span></label>
                                        <input v-model="form.password_confirmation" type="password" class="form-control"
                                            required />
                                    </b-col>
                                </b-row>
                            </template>

                            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                <button type="button" class="btn btn-secondary" @click="router.back()">Cancel</button>
                                <button type="submit" class="btn btn-primary" :disabled="apiState.saving">
                                    <span v-if="apiState.saving" class="spinner-grow spinner-grow-sm me-1"></span>
                                    {{ isEdit ? 'Update User' : 'Create User' }}
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
import usersApi from "@/api/users/usersApi";
import branchesApi from "@/api/branches/branchesApi";

const route = useRoute();
const router = useRouter();
const apiState = useApiState();

const isEdit = computed(() => !!route.params.id);
const id = route.params.id;

const roles = ref([]);
const branches = ref([]);
const stores = ref([]);

const form = ref({
    name: "", email: "", phone: "", national_id: "",
    gender: "", date_of_birth: "", address: "",
    role: "", branch_id: "", store_id: "",
    account_status: "active",
    password: "", password_confirmation: "",
});

const filteredStores = computed(() => stores.value.filter(s => s.branch_id == form.value.branch_id));
const needsBranch = computed(() => ['Branch Manager', 'Store Manager'].includes(form.value.role));
const needsStore = computed(() => form.value.role === 'Store Manager');

const onRoleChange = () => {
    form.value.branch_id = "";
    form.value.store_id = "";
};

const fetchLookups = async () => {
    try {
        const [r, b, s] = await Promise.all([
            usersApi.getRoles(),
            branchesApi.listBranches({ per_page: 100 }),
            branchesApi.listStores({ per_page: 100 }),
        ]);
        roles.value = Array.isArray(r.data) ? r.data : [];
        branches.value = b.data?.data ?? [];
        stores.value = s.data?.data ?? [];
    } catch (e) { console.error(e); }
};

const fetchUser = async () => {
    apiState.resetState();
    apiState.setLoading(true);
    try {
        const { data } = await usersApi.getUser(id);
        form.value = {
            name: data.name,
            email: data.email,
            phone: data.phone ?? "",
            national_id: data.national_id ?? "",
            gender: data.gender ?? "",
            date_of_birth: data.date_of_birth ?? "",
            address: data.address ?? "",
            role: data.roles?.[0] ?? "",
            branch_id: data.branch_id ?? "",
            store_id: data.store_id ?? "",
            account_status: data.account_status,
            password: "", password_confirmation: "",
        };
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Failed to load user.");
    } finally {
        apiState.setLoading(false);
    }
};

const save = async () => {
    apiState.resetState();
    apiState.setSaving(true);
    try {
        const payload = { ...form.value };
        if (isEdit.value) {
            delete payload.password;
            delete payload.password_confirmation;
            await usersApi.updateUser(id, payload);
        } else {
            await usersApi.createUser(payload);
        }
        apiState.setSuccess(true);
        apiState.setMessage(isEdit.value ? "User updated successfully." : "User created successfully.");
        setTimeout(() => router.push({ name: "users.list" }), 1200);
    } catch (e) {
        apiState.setError(true);
        apiState.setMessage(e.message ?? "Something went wrong.");
        if (e.errors) apiState.setErrors(Object.values(e.errors).flat());
    } finally {
        apiState.setSaving(false);
    }
};

onMounted(async () => {
    await fetchLookups();
    if (isEdit.value) fetchUser();
});
</script>