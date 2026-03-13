<template>
    <DefaultLayout>
        <b-row class="justify-content-center">
            <b-col cols="12">
                <b-card no-body>
                    <b-card-header class="border-bottom py-2">
                        <b-row class="align-items-center">
                            <b-col>
                                <b-card-title>Dashboard</b-card-title>
                            </b-col>
                        </b-row>
                    </b-card-header>
                    <b-card-body class="pt-2"> </b-card-body>
                </b-card>
            </b-col>
        </b-row>
    </DefaultLayout>
</template>
<script setup>
import { onMounted } from "vue";

import DefaultLayout from "@/layouts/DefaultLayout.vue";

onMounted(() => { });
</script>