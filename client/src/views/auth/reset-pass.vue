<template>
  <AuthLayout>
    <b-col lg="4" class="mx-auto">
      <b-card no-body>
        <b-card-body class="p-0 bg-black auth-header-box rounded-top">
          <div class="text-center p-3">
            <router-link to="/" class="logo logo-admin">
              <img :src="logoSm" height="50" alt="logo" class="auth-logo" />
            </router-link>
            <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">
              Reset Password
            </h4>
            <p class="text-muted fw-medium mb-0">
              Enter your Email and instructions will be sent to you!
            </p>
          </div>
        </b-card-body>
        <b-card-body class="pt-0">
          <b-form class="my-4" @submit.prevent="forgot_pass()">
            <StatesComponent />
            <b-form-group class="mb-2" label="Email" label-for="userEmail">
              <b-form-input type="email" required v-model="form_data.email" class="mb-2"
                placeholder="Enter Email Address" id="userEmail" />
            </b-form-group>

            <b-form-group class="mb-0 row">
              <b-col cols="12">
                <div class="d-grid mt-3">
                  <b-button variant="primary" type="submit">Reset <i class="fas fa-sign-in-alt ms-1"></i></b-button>
                </div>
              </b-col>
            </b-form-group>
          </b-form>
          <div class="text-center mb-2">
            <p class="text-muted">
              Remember It ?
              <router-link :to="{ name: 'auth.sign-in' }" class="text-primary ms-2">Sign in here
              </router-link>
            </p>
          </div>
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
import authService from "@/api/auth/authApi.js";
import { encryptStr } from "@/helpers/helper";
import router from "@/router";
import { getValidationErrors } from "@/helpers/customErrors.js";
import { useRoute, useRouter } from "vue-router";
const apiState = useApiState();
const route = useRoute();

const form_data = reactive({
  email: "" as string,
});

const forgot_pass = async () => {
  try {
    apiState.setSaving(true);
    const response = await authService.forgotPasswordOTPApi(form_data);
    apiState.setSaving(false);
    apiState.setSuccess(true);
    apiState.setMessage(response.message);
    const node = response.data
    console.log('response', response.code)
    setTimeout(async () => {
      switch (response.code) {
        case 202:
          router.push({
            name: "auth.questions.answer",
            params: { id: node },
          });
          break;

        default:
          router.push({
            name: "auth.forgot.success",
          });
          break;
      }

    }, 1500);
  } catch (error: any) {
    if (typeof error.errors == "object") {
      const errors = await getValidationErrors(error.errors);
      apiState.setErrors(errors);
    }
    apiState.setSaving(false);
    apiState.setError(true);
    apiState.setMessage(error.message || "Login failed");
    setTimeout(() => {
      apiState.setError(false);
      apiState.setMessage("");
    }, 4500);
  } finally {
  }
};
</script>
