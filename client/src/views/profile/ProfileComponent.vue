<template>
  <DefaultLayout>
    <b-row class="justify-content-center">
      <b-col cols="12">

        <!-- Header Card -->
        <b-card no-body>
          <b-card-body>
            <StatesComponent />
            <div v-if="!apiState.loading" class="d-flex align-items-center gap-3">

              <!-- Avatar -->
              <div class="flex-shrink-0">
                <span
                  class="thumb-xxl d-flex justify-content-center align-items-center bg-primary-subtle text-primary rounded-circle fw-bold fs-22">
                  {{ getInitials(user.name) }}
                </span>
              </div>

              <!-- Name + Role + Status -->
              <div class="flex-grow-1">
                <h5 class="fw-semibold mb-1">{{ user.name }}</h5>
                <div class="d-flex gap-2 flex-wrap">
                  <span v-for="r in user.roles" :key="r" :class="roleBadge(r)">{{ r }}</span>
                  <span :class="statusBadge(user.account_status)">{{ user.account_status }}</span>
                  <span :class="user.is_online ? 'badge bg-success' : 'badge bg-light text-muted border'">
                    {{ user.is_online ? 'Online' : 'Offline' }}
                  </span>
                </div>
              </div>

              <!-- Stats -->
              <div class="d-none d-md-flex gap-3 text-center">
                <div class="border rounded p-2 px-3">
                  <div class="fw-semibold fs-18">{{ user.today_logins_count ?? 0 }}</div>
                  <div class="text-muted small">Today Logins</div>
                </div>
                <div class="border rounded p-2 px-3">
                  <div class="fw-semibold fs-18">{{ user.all_time_logins_count ?? 0 }}</div>
                  <div class="text-muted small">All Time Logins</div>
                </div>
              </div>

              <!-- Actions -->
              <div class="ms-auto d-flex gap-2">
                <button class="btn btn-outline-warning btn-sm"
                  @click="router.push({ name: 'users.edit', params: { id: user.id } })">
                  <i class="fas fa-edit me-1"></i> Edit
                </button>
                <button class="btn btn-outline-secondary btn-sm" @click="router.back()">
                  <i class="fas fa-arrow-left me-1"></i> Back
                </button>
              </div>

            </div>
          </b-card-body>
        </b-card>

      </b-col>
    </b-row>

    <!-- Details -->
    <b-row class="justify-content-center mt-3" v-if="!apiState.loading && user.id">
      <b-col md="4">
        <b-card no-body>
          <b-card-header class="border-bottom py-2">
            <b-card-title class="mb-0">Personal Information</b-card-title>
          </b-card-header>
          <b-card-body>
            <ul class="list-unstyled mb-0">
              <li class="py-1">
                <i class="las la-envelope text-secondary fs-18 align-middle me-2"></i>
                <b>Email</b>: {{ user.email }}
              </li>
              <li class="py-1">
                <i class="las la-phone text-secondary fs-18 align-middle me-2"></i>
                <b>Phone</b>: {{ user.phone ?? '—' }}
              </li>
              <li class="py-1">
                <i class="las la-id-card text-secondary fs-18 align-middle me-2"></i>
                <b>National ID</b>: {{ user.national_id ?? '—' }}
              </li>
              <li class="py-1">
                <i class="las la-birthday-cake text-secondary fs-18 align-middle me-2"></i>
                <b>Date of Birth</b>: {{ user.date_of_birth ?? '—' }}
              </li>
              <li class="py-1">
                <i class="las la-mercury text-secondary fs-18 align-middle me-2"></i>
                <b>Gender</b>: {{ user.gender ? capitalizeFirst(user.gender) : '—' }}
              </li>
              <li class="py-1">
                <i class="las la-map-marker text-secondary fs-18 align-middle me-2"></i>
                <b>Address</b>: {{ user.address ?? '—' }}
              </li>
              <li class="py-1">
                <i class="las la-calendar text-secondary fs-18 align-middle me-2"></i>
                <b>Member Since</b>: {{ formatDate(user.created_at) }}
              </li>
              <li class="py-1">
                <i class="las la-clock text-secondary fs-18 align-middle me-2"></i>
                <b>Last Login</b>: {{ formatDate(user.last_login_at) }}
              </li>
            </ul>
          </b-card-body>
        </b-card>
      </b-col>

      <b-col md="8">
        <b-card no-body>
          <b-card-header class="border-bottom py-2">
            <b-card-title class="mb-0">Assignment</b-card-title>
          </b-card-header>
          <b-card-body>
            <b-row class="g-3">
              <b-col md="6">
                <div class="border rounded p-3">
                  <div class="text-muted small mb-1">Branch</div>
                  <div class="fw-semibold">{{ user.branch?.name ?? '—' }}</div>
                </div>
              </b-col>
              <b-col md="6">
                <div class="border rounded p-3">
                  <div class="text-muted small mb-1">Store</div>
                  <div class="fw-semibold">{{ user.store?.name ?? '—' }}</div>
                </div>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>

        <b-card no-body class="mt-3">
          <b-card-header class="border-bottom py-2">
            <b-card-title class="mb-0">Account Security</b-card-title>
          </b-card-header>
          <b-card-body>
            <div class="table-responsive">
              <table class="table table-sm table-borderless mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted" style="width:50%">Account Status</td>
                    <td><span :class="statusBadge(user.account_status)">{{ user.account_status }}</span></td>
                  </tr>
                  <tr>
                    <td class="text-muted">Failed Login Attempts</td>
                    <td>
                      <span :class="user.failed_login_attempts > 0 ? 'text-danger fw-semibold' : ''">
                        {{ user.failed_login_attempts ?? 0 }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted">Locked Until</td>
                    <td>{{ user.locked_until ? formatDate(user.locked_until) : '—' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Password Last Changed</td>
                    <td>{{ user.password_changed_at ? formatDate(user.password_changed_at) : '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
  </DefaultLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import usersApi from "@/api/users/usersApi";

const route = useRoute();
const router = useRouter();
const apiState = useApiState();

const user = ref({});

const fetchUser = async () => {
  apiState.resetState();
  apiState.setLoading(true);
  try {
    const { data } = await usersApi.getUser(route.params.id);
    user.value = data;
  } catch (e) {
    apiState.setError(true);
    apiState.setMessage(e.message ?? "Failed to load user.");
  } finally {
    apiState.setLoading(false);
  }
};

const getInitials = (name) => {
  if (!name) return "?";
  return name.split(" ").slice(0, 2).map(n => n[0].toUpperCase()).join("");
};

const capitalizeFirst = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : '';

const formatDate = (d) => d ? new Date(d).toLocaleString("en-KE", { dateStyle: "medium", timeStyle: "short" }) : "—";

const roleBadge = (r) => ({
  'Administrator': 'badge bg-danger me-1',
  'Branch Manager': 'badge bg-warning text-dark me-1',
  'Store Manager': 'badge bg-info text-dark me-1',
}[r] ?? 'badge bg-secondary me-1');

const statusBadge = (s) => ({
  active: 'badge bg-success',
  inactive: 'badge bg-secondary',
  suspended: 'badge bg-danger',
  pending: 'badge bg-warning text-dark',
}[s] ?? 'badge bg-light');

onMounted(fetchUser);
</script>