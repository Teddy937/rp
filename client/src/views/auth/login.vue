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
              Let's Get Started
            </h4>
            <p class="text-muted fw-medium mb-0">
              Sign in to continue to Retail pay Portal.
            </p>
          </div>
        </b-card-body>
        <b-card-body class="pt-0">
          <b-form class="my-4" @submit.prevent="save()">
            <StatesComponent />
            <b-form-group class="mb-2" label="Email" label-for="email">
              <b-form-input type="email" placeholder="Enter email" id="email" required v-model="credentials.email" />
            </b-form-group>

            <b-form-group class="mb-2" label="Password" label-for="userpassword">
              <b-form-input type="password" placeholder="Enter password" id="userpassword"
                v-model="credentials.password" />
            </b-form-group>

            <div class="form-group row mt-3">
              <b-col sm="6">
                <div class="form-switch-success">
                  <b-form-checkbox switch>Remember me</b-form-checkbox>
                </div>
              </b-col>
            </div>

            <b-form-group class="mb-0 row">
              <b-col cols="12">
                <div class="d-grid mt-3">
                  <b-button variant="primary" :disabled="!isFormValid" type="submit">Log In <i
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
import authService from "@/api/auth/authApi.js";
import { getDeviceInfo } from "@/helpers/helper";
import { setSession } from "@/api/http";
import router from "@/router";
import { getValidationErrors } from "@/helpers/customErrors.js";
import { useRoute, useRouter } from "vue-router";
const credentials = reactive({
  email: "",
  password: "",
});

const navigate = useRouter();
const route = useRoute();
const query = route.query;
const apiState = useApiState();


const isFormValid = computed(() => {
  return credentials.email.trim() !== "" && credentials.password.trim() !== "";
});

const save = async () => {
  apiState.resetState();
  apiState.setSaving(true);
  let data = {
    email: credentials.email,
    password: credentials.password,
  };

  try {
    const response = await authService.loginApi(data);
    apiState.setSaving(false);
    apiState.setSuccess(true);
    apiState.setMessage(response.message);
    const node = response.data.hex;
    await router.push({
      name: "auth.sign-otp",
      params: { node },
    });
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
    }, 3500);
  }
};
const redirectUser = async () => {
  if (query.redirectedFrom) {
    return await router.push(`${query.redirectedFrom}`);
  }
  return await router.push("/");
};
</script>
