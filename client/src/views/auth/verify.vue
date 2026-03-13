<template>
    <AuthLayout>
        <b-col lg="8" class="mx-auto">
            <b-card no-body>
                <b-card-body class="p-0 bg-black auth-header-box rounded-top">
                    <div class="text-center p-3">
                        <router-link to="/" class="logo logo-admin">
                            <img :src="logoSm" height="50" alt="logo" class="auth-logo" />
                        </router-link>
                        <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">
                            Disbursement Attempt Verification
                        </h4>

                    </div>
                </b-card-body>
                <b-card-body class="pt-0">
                    <div class="row my-2">
                        <div class="col-md-12">
                            <h5 class="text-center">Type: {{ request.customer_id ? 'Loan Disbursement' :
                                'Petty Cash Disbursement' }}</h5>
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <div class="border py-2 px-2 rounded-1">
                                <h5 class="text-center">Initiator</h5>
                                <hr class="my-1 py-1">
                                <div>
                                    <h6>Name: {{ creator.name }}</h6>
                                    <h6>Email: {{ creator.email }}</h6>
                                    <h6>Phone: {{ creator.phone_number }}</h6>
                                    <h6>National ID: {{ creator.national_id }}</h6>
                                    <h6>Amount involved KES: {{ formatMoney(request.amount) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border py-2 px-2 rounded-1">
                                <h5 class="text-center">Receiver</h5>
                                <hr class="my-1 py-1">
                                <div>
                                    <h6>Name: {{ recipient.name }}</h6>
                                    <h6>Email: {{ recipient.email }}</h6>
                                    <h6>Phone: {{ recipient.phone_number }}</h6>
                                    <h6>National ID: {{ recipient.national_id }}</h6>
                                    <h6>Last Contact Change: {{ recipient.last_contact_changed_at }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <form @submit.prevent="submit_approval()">
                                <div class="border rounded-1 py-2 px-2">
                                    <StatesComponent />
                                    <div class="mb-2">
                                        <label for="">OTP</label>
                                        <input type="number" v-model="form_data.otp" required
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="mb-2">
                                        <label for="">Decision</label>
                                        <select v-model="form_data.decision" required id=""
                                            class="form-control form-control-sm">
                                            <option value="">Select</option>
                                            <option value="approved">Approve</option>
                                            <option value="declined">Decline</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label for="">Comments</label>
                                        <textarea v-model="form_data.comments" rows="3" id=""
                                            class="form-control form-control-sm"></textarea>
                                    </div>
                                    <div class="mb-2" v-if="request.approval_status == 'pending'">
                                        <button type="submit" class="btn btn-sm btn-warning float-end">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </b-card-body>
            </b-card>
        </b-col>
    </AuthLayout>
</template>
<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import StatesComponent from "@/states/StatesComponent.vue";
import logoSm from "@/assets/images/logo-white.svg";
import AuthLayout from "@/layouts/AuthLayout.vue";
import { useApiState } from "@/stores/apiState";
import authService from "@/api/auth/authApi.js";
import { resetForm, formatMoney } from "@/helpers/helper";
import router from "@/router";
import { getValidationErrors } from "@/helpers/customErrors.js";
import { useRoute, useRouter } from "vue-router";
const apiState = useApiState();
const route = useRoute();
const request = ref({})
const creator = ref({})
const recipient = ref({})

onMounted(async () => {
    await fetch()
})

const form_data = reactive({
    id: "",
    otp: "",
    decision: "",
    comments: "",
})


const fetch = async () => {
    try {
        const { data } = await authService.fetchRequestApi(route.params.id);
        request.value = data.request;
        form_data.id = data.request.id;
        form_data.comments = data.request.comments;
        form_data.decision = data.request.approval_status == 'pending' ? "" : data.approval_status;
        creator.value = data.request.creator;
        recipient.value = data.receiver
    } catch (error) {
        apiState.setSaving(false);
        apiState.setError(true);
        apiState.setMessage(error.message || "Something went wrong");
    }
}


const submit_approval = async () => {
    try {
        const response = await authService.postRequestApi(form_data);
        resetForm(form_data)
        apiState.setSaving(false);
        apiState.setSuccess(true);
        apiState.setMessage(response.message);
    } catch (error) {
        if (typeof error.errors == "object") {
            const errors = await getValidationErrors(error.errors);
            apiState.setErrors(errors);
        }
        apiState.setSaving(false);
        apiState.setError(true);
        apiState.setMessage(error.message || "Something went wrong");

    } finally {
    }
};
</script>
