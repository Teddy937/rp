<template>
    <AuthLayout>
        <b-col lg="4" class="mx-auto mt-4">
            <b-card no-body>
                <b-card-body class="p-0 bg-black auth-header-box rounded-top">
                    <div class="text-center p-3">
                        <router-link to="/" class="logo logo-admin">
                            <img :src="logoSm" height="50" alt="logo" class="auth-logo" />
                        </router-link>
                        <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">Success!</h4>
                    </div>
                </b-card-body>
                <b-card-body class="pt-4">
                    <div class="alert alert-info">
                        <p>Please use the OTP sent to your email and phone to set your password!</p>
                    </div>
                    <div class="d-flex justify-content-center">
                        <img src="/download.png" alt="">
                    </div>
                </b-card-body>
            </b-card>
        </b-col>
    </AuthLayout>
</template>
<script setup>
import logoSm from "@/assets/images/logo-white.svg";
</script>
