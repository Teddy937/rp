<template>
  <AuthLayout>
    <b-col lg="4" class="mx-auto">
      <b-card no-body>
        <b-card-body class="p-0 bg-black auth-header-box rounded-top">
          <div class="text-center p-3">
            <router-link to="/" class="logo logo-admin">
              <img :src="logoSm" height="50" alt="logo" class="auth-logo" />
            </router-link>
            <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">Verify OTP</h4>
            <p class="text-muted fw-medium mb-0">
              Enter OTP sent to your email or phone to continue
            </p>
          </div>
        </b-card-body>
        <b-card-body class="pt-0">
          <b-form class="my-2" @submit.prevent="verify_otp()">
            <StatesComponent />
            <b-form-group class="mb-1 text-center" label="Enter OTP" label-for="otp">
              <div class="code-container">
                <input type="number" class="code" min="0" max="9" v-model="otp_data.otp1" required />
                <input type="number" class="code" min="0" max="9" v-model="otp_data.otp2" required />
                <input type="number" class="code" min="0" max="9" v-model="otp_data.otp3" required />
                <input type="number" class="code" min="0" max="9" v-model="otp_data.otp4" required />
              </div>
            </b-form-group>

            <b-form-group class="mb-1 row">
              <b-col cols="12">
                <div class="d-grid mt-2">
                  <b-button variant="primary" :disabled="!isFormValid" type="submit">Continue <i
                      class="fas fa-sign-in-alt ms-1"></i></b-button>
                </div>
              </b-col>
            </b-form-group>
            <small class="text-center mt-2">Resend code in {{ timeLeft }} seconds
              <strong v-if="timeLeft <= 0" @click.prevent="resendOtp()">Resend Code</strong></small>
          </b-form>
        </b-card-body>
      </b-card>
    </b-col>
  </AuthLayout>
</template>
<script setup lang="ts">
import { reactive, watch, onMounted, computed, onUnmounted, ref } from "vue";
import logoSm from "@/assets/images/logo-white.svg";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import authService from "@/api/auth/authApi.js";
import { decryptStr, encryptStr, resetForm } from "@/helpers/helper";
import router from "@/router";
import { getValidationErrors } from "@/helpers/customErrors.js";
import AuthLayout from "@/layouts/AuthLayout.vue";
import { useRoute } from "vue-router";
const timeLeft = ref<number>(10);
const apiState = useApiState();
const route = useRoute();
let timer: ReturnType<typeof setInterval> | null = null;
onMounted(async () => {
  startCountdown();
  const codes: NodeListOf<HTMLInputElement> =
    document.querySelectorAll(".code");

  codes.forEach((code, index) => {
    code.addEventListener("keydown", (e: KeyboardEvent) => {
      const key = e.key;

      if (/^[0-9]$/.test(key)) {
        code.value = "";

        // Focus next input if it exists
        if (codes[index + 1]) {
          setTimeout(() => codes[index + 1].focus(), 10);
        }
      } else if (key === "Backspace") {
        // Focus previous input if it exists
        if (codes[index - 1]) {
          setTimeout(() => codes[index - 1].focus(), 10);
        }
      }
    });
  });
});
const otp_data = reactive({
  otp1: null as number | null,
  otp2: null as number | null,
  otp3: null as number | null,
  otp4: null as number | null,
});

const combinedOtp = computed(() => {
  return `${otp_data.otp1 ?? ""}${otp_data.otp2 ?? ""}${otp_data.otp3 ?? ""}${otp_data.otp4 ?? ""
    }`;
});

const form_data = reactive({
  otp: null as number | null,
  user_id: route.params.node,
});

const isFormValid = computed(() => {
  return (
    otp_data.otp1 !== null &&
    otp_data.otp2 !== null &&
    otp_data.otp3 !== null &&
    otp_data.otp4 !== null
  );
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});

const startCountdown = (): void => {
  timeLeft.value = 30;
  if (timer) clearInterval(timer);

  timer = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--;
    } else {
      clearInterval(timer!);
    }
  }, 1000);
};

const verify_otp = async () => {
  form_data.otp = combinedOtp.value as unknown as number;
  apiState.resetState();
  apiState.setSaving(true);
  try {
    const response = await authService.verifyOTPApi(form_data);
    resetForm(otp_data);
    apiState.setSaving(false);
    apiState.setSuccess(true);
    apiState.setMessage(response.message);
    setTimeout(async () => {
      apiState.setSuccess(false);
      apiState.setMessage("");
      const node = response.data
      router.push({
        name: "auth.new-password",
        params: { node },
      });
    }, 1500);
  } catch (error: any) {
    if (typeof error.errors == "object") {
      const errors = await getValidationErrors(error.errors);
      apiState.setErrors(errors);
    }
    apiState.setSaving(false);
    apiState.setError(true);
    apiState.setMessage(error.message || "OTP verification failed");
    setTimeout(() => {
      apiState.setError(false);
      apiState.setMessage("");
    }, 4500);
  } finally {
  }
};

const resendOtp = async () => {
  otp_data.otp1 = null
  otp_data.otp2 = null
  otp_data.otp3 = null
  otp_data.otp4 = null
  // Restart countdown
  startCountdown();
  form_data.otp = combinedOtp.value as unknown as number;
  apiState.resetState();
  apiState.setSaving(true);
  try {
    const response = await authService.resendOTPApi({ user_id: form_data.user_id });
    apiState.setSaving(false);
    apiState.setSuccess(true);
    apiState.setMessage(response.message);
  } catch (error: any) {
    if (typeof error.errors == "object") {
      const errors = await getValidationErrors(error.errors);
      apiState.setErrors(errors);
    }
    apiState.setSaving(false);
    apiState.setError(true);
    apiState.setMessage(error.message || "OTP verification failed");
    setTimeout(() => {
      apiState.setError(false);
      apiState.setMessage("");
    }, 4500);
  } finally {
    setTimeout(() => {
      apiState.setSuccess(false);
      apiState.setMessage("");
    }, 1500);
  }
};
</script>


<style scoped>
.code-container {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 20px 0;
}

.code {
  caret-color: transparent;
  background-color: rgba(255, 255, 255, 0.6);
  border-radius: 10px;
  border: 1px solid #22c55e;
  font-size: 30px;
  font-family: "Lato", sans-serif;
  width: 55px;
  height: 60px;
  margin: 10px;
  text-align: center;
  font-weight: 300;
  transition: 1s;
}

strong {
  color: #50b5ff;
  cursor: pointer;
}

.code::-webkit-outer-spin-button,
.code::-webkit-inner-spin-button {
  -webkit-appearance: none;
}

.btn_otp {
  font-family: "Lato", sans-serif;
  min-width: 400px;
  display: inline-block;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  user-select: none;
  cursor: pointer;
  border: 1px solid transparent;
  margin: 0px 0px 20px 0px;
  padding: 0.65rem 0.75rem;
  font-size: 1rem;
  line-height: 1.5;
  border-radius: 10px;
  text-transform: uppercase;
  letter-spacing: 0.7;
}

.btn-primary_otp {
  color: #fff;
}

@media screen and (max-width: 600px) {
  .code-container {
    flex-wrap: nowrap;
  }

  .code {
    font-size: 24px;
    height: 50px;
    max-width: 50px;
    margin: 5px;
  }

  .btn_otp {
    min-width: 10px;
    width: 100%;
  }
}

.code:valid {
  border-color: #9861c2;
  box-shadow: 0 10px 10px -5px rgba(0, 0, 0, 0.25);
}
</style>
