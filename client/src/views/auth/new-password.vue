<template>
  <AuthLayout>
    <b-col lg="4" class="mx-auto">
      <b-card no-body>
        <b-card-body class="p-0 bg-black auth-header-box rounded-top">
          <div class="text-center p-3">
            <router-link to="/" class="logo logo-admin">
              <img :src="logoSm" height="50" alt="logo" class="auth-logo" />
            </router-link>
            <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">New Password</h4>
            <p class="text-muted fw-medium mb-0">
              Set your new password and continue.
            </p>
          </div>
        </b-card-body>
        <b-card-body class="pt-0">
          <b-form class="my-4" @submit.prevent="set_password()">
            <StatesComponent />

            <div class="mb-2">
              <label for="">Password</label>
              <input type="password" v-model="credentials.password" required class="form-control form-control-sm" />
            </div>
            <div class="mb-2">
              <label for="">Confirm Password</label>
              <input type="password" v-model="credentials.password_confirmation" required
                class="form-control form-control-sm" />
            </div>

            <b-form-group class="mb-0 row">
              <b-col cols="12">
                <div class="d-grid mt-3">
                  <b-button variant="primary" :disabled="!isFormValid" type="submit">Submit <i
                      class="fas fa-sign-in-alt ms-1"></i></b-button>
                </div>
              </b-col>
            </b-form-group>
          </b-form>
        </b-card-body>
      </b-card>
    </b-col>
  </AuthLayout>
</template>
<script setup lang="ts">
import { ref, reactive, computed } from "vue";
import StatesComponent from "@/states/StatesComponent.vue";
import logoSm from "@/assets/images/logo-white.svg";
import AuthLayout from "@/layouts/AuthLayout.vue";
import { useApiState } from "@/stores/apiState";
import { resetForm, decryptStr } from "@/helpers/helper";
import authService from "@/api/auth/authApi.js";
import router from "@/router";
import { getValidationErrors } from "@/helpers/customErrors.js";
import { useRoute } from "vue-router";
const route = useRoute();
const apiState = useApiState();
const credentials = reactive({
  user_id: route.params.node,
  password: "",
  password_confirmation: "",
});

const isFormValid = computed(() => {
  return (
    credentials.user_id &&
    credentials.password.trim() !== "" &&
    credentials.password_confirmation.trim()
  );
});

const set_password = async () => {
  let data = {
    user_id: credentials.user_id,
    password: credentials.password.trim(),
    password_confirmation: credentials.password_confirmation.trim(),
  };
  try {
    apiState.resetState();
    apiState.setSaving(true);
    apiState.setErrors([]);
    const response = await authService.setNewPasswordApi(data);
    apiState.setSaving(false);
    apiState.setSuccess(true);
    apiState.setMessage(response.message);
    setTimeout(async () => {
      apiState.setSuccess(false);
      apiState.setMessage("");
      router.push({
        name: "auth.sign-in",
      });
    }, 1500);
  } catch (error: any) {
    if (typeof error.errors == "object") {
      const errors = await getValidationErrors(error.errors);
      apiState.setErrors(errors);
    }
    apiState.setSaving(false);
    apiState.setError(true);
    apiState.setMessage(error.message || "Something went wrong!");
    setTimeout(() => {
      apiState.setErrors([]);
      apiState.setError(false);
      apiState.setMessage("");
    }, 6500);
  }
};
</script>
