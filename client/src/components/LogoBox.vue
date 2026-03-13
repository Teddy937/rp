<template>
  <router-link to="/" class="logo">
    <span>
      <img :src="logoSm" alt="logo-small" class="logo-sm" />{{ " " }}
    </span>
    <!--<span class="">
      <img :src="logoLight" alt="logo-large" class="logo-sm" />
      <img :src="logoDark" alt="logo-large" class="logo-sm" />
    </span>-->
  </router-link>
</template>
<script setup lang="ts">
import logoSm from "@/assets/images/logo.svg";
import logoLight from "@/assets/images/logo-white.svg";
import logoDark from "@/assets/images/logo-white.svg";
</script>
