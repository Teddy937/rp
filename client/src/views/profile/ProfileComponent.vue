<template>
  <DefaultLayout>
    <div v-if="apiState.loading"><StatesComponent /></div>
    <div v-else>
      <b-row class="justify-content-center">
        <b-col cols="12">
          <b-card no-body>
            <b-card-body>
              <b-row>
                <b-col lg="4" class="align-self-center mb-3 mb-lg-0">
                  <div class="d-flex align-items-center flex-row flex-wrap">
                    <div class="position-relative me-3">
                      <img
                        v-if="info.employee.profile"
                        :src="info.employee.profile"
                        alt=""
                        height="120"
                        class="rounded-circle"
                      />
                      <span
                        v-else
                        class="thumb-xxl justify-content-center d-flex align-items-center bg-success-subtle text-success rounded-circle me-2"
                      >
                        {{ getInitials(info.employee.name) }}</span
                      >
                      <a
                        href="javascript:void(0)"
                        data-bs-toggle="modal"
                        data-bs-target="#profile"
                        class="thumb-md justify-content-center d-flex align-items-center bg-primary text-white rounded-circle position-absolute end-0 bottom-0 border border-3 border-card-bg"
                      >
                        <i class="fas fa-camera"></i>
                      </a>
                    </div>
                    <div class="">
                      <h5 class="fw-semibold fs-22 mb-1">
                        {{ info.employee.name }}
                      </h5>
                      <p class="mb-0 text-muted fw-medium">
                        {{ info.employee.details?.occupation.name }}
                      </p>
                    </div>
                  </div>
                </b-col>
                <b-col lg="4" class="ms-auto align-self-center">
                  <div class="d-flex justify-content-center">
                    <div
                      class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0"
                    >
                      <h5 class="fw-semibold fs-22 mb-1">75</h5>
                      <p class="text-muted mb-0 fw-medium">Today Logins</p>
                    </div>
                    <div
                      class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0"
                    >
                      <h5 class="fw-semibold fs-22 mb-1">680</h5>
                      <p class="text-muted mb-0 fw-medium">All time Logins</p>
                    </div>
                    <div
                      class="border-dashed rounded border-theme-color p-2 me-2 flex-grow-1 flex-basis-0"
                    >
                      <h5 class="fw-semibold fs-22 mb-1">
                        {{
                          info.employee.salary?.salary
                            ? formatNumber(info.employee.salary?.salary)
                            : "N/A"
                        }}/=
                      </h5>
                      <p class="text-muted mb-0 fw-medium">Salary</p>
                    </div>
                  </div>
                </b-col>
                <b-col lg="4" class="align-self-center">
                  <div class="d-flex w-100 justify-content-end">
                    <div></div>
                  </div>
                </b-col>
              </b-row>
            </b-card-body>
          </b-card>
        </b-col>
      </b-row>

      <b-row class="justify-content-center">
        <b-col md="4">
          <b-card no-body>
            <b-card-header>
              <b-row class="align-items-center">
                <div class="col">
                  <b-card-title>Personal Information</b-card-title>
                </div>
                <div class="col-auto"></div>
              </b-row>
            </b-card-header>
            <b-card-body class="pt-0">
              <ul class="list-unstyled mb-0">
                <li class="">
                  <i
                    class="las la-birthday-cake me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> Birth Date </b> :
                  {{ formatDate(info.employee.date_of_birth) }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-briefcase me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> Department </b> :
                  {{ info.employee.details?.department.name }}
                </li>
                <li class="mt-2">
                  <i
                    class="lab la-black-tie me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> Position </b> :
                  {{ info.employee.details?.occupation.name }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-phone me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> Phone </b> :{{ info.employee.phone_number }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-phone-volume me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> Alternative Phone </b> :
                  {{ info.employee.other_phone_number ?? "N/A" }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-mercury me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> Gender </b> :
                  {{ capitalizeFirstLetter(info.employee.gender) }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-envelope text-secondary fs-22 align-middle me-2"
                  ></i>
                  <b> Email </b> : {{ info.employee.email }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-id-card me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> National Id </b> : {{ info.employee.national_id }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-calendar me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> Employment Date </b> :
                  {{ formatDate(info.employee.details?.employment_date) }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-language me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> Engagement Type </b> :
                  {{ info.employee.details?.engagement_type }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-sort-numeric-down me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> KRA PIN </b> : {{ info.employee.details?.kra_pin }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-sort-numeric-up me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> SHIF Number </b> : {{ info.employee.details?.shif }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-list-ol me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> NSSF</b> : {{ info.employee.details?.nssf }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-signal me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> Status </b> :
                  {{ info.employee.approved ? "Approved" : "Pending Approval" }}
                </li>
                <li class="mt-2">
                  <i
                    class="las la-calendar-alt me-2 text-secondary fs-22 align-middle"
                  ></i>
                  <b> Date Created </b> :
                  {{ formatDate(info.employee.created_at, true) }}
                </li>
              </ul>
            </b-card-body>
          </b-card>
        </b-col>
        <b-col md="8">
          <b-tabs nav-class="mb-2">
            <b-tab
              title="Other Information"
              active
              class="p-1"
              title-link-class="fw-medium"
            >
              <div class="card">
                <div
                  class="card-header border-bottom py-2 d-flex justify-content-between"
                >
                  <h5 class="card-title">Contact Persons</h5>
                </div>
                <div class="card-body py-1 px-1">
                  <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered">
                      <thead class="bg-light">
                        <tr>
                          <th>#</th>
                          <th>Contact Person</th>
                          <th>Name</th>
                          <th>Phone</th>
                        </tr>
                      </thead>

                      <tbody>
                        <tr
                          v-for="(p, i) in info.employee.contact_persons"
                          :key="i"
                        >
                          <td>{{ i + 1 }}</td>
                          <td>{{ p.contact_person?.name }}</td>
                          <td>{{ p.name }}</td>
                          <td>{{ p.phone }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="card">
                <div
                  class="card-header border-bottom py-2 d-flex justify-content-between"
                >
                  <h5 class="card-title">Bank Information</h5>
                </div>
                <div class="card-body py-1 px-1">
                  <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered">
                      <thead class="bg-light">
                        <tr>
                          <th>Bank Name</th>
                          <th>Bank Code</th>
                          <th>Branch</th>
                          <th>Account Name</th>
                          <th>Account Number</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="info.employee.bank">
                          <td>{{ info.employee.bank?.bank_name }}</td>
                          <td>{{ info.employee.bank?.bank_code }}</td>
                          <td>{{ info.employee.bank?.branch }}</td>
                          <td>{{ info.employee.bank?.account_name }}</td>
                          <td>{{ info.employee.bank?.account_number }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="card">
                <div
                  class="card-header border-bottom py-2 d-flex justify-content-between"
                >
                  <h5 class="card-title">Salary Information</h5>
                </div>
                <div class="card-body py-1 px-1">
                  <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered">
                      <thead class="bg-light">
                        <tr>
                          <th>Salary Type</th>
                          <th>Salary Amount</th>
                          <th>Taxable</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="info.employee.salary">
                          <td>
                            {{
                              capitalizeFirstLetter(
                                info.employee.salary?.salary_type
                              )
                            }}
                            Salary
                          </td>
                          <td>
                            {{ formatNumber(info.employee.salary?.salary) }}/=
                          </td>
                          <td>
                            {{ info.employee.salary?.taxable ? "Yes" : "No" }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="card">
                <div
                  class="card-header border-bottom py-2 d-flex justify-content-between"
                >
                  <h5 class="card-title">Allowances</h5>
                </div>
                <div class="card-body py-1 px-1">
                  <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered">
                      <thead class="bg-light">
                        <tr>
                          <th>Allowance</th>
                          <th>Amount</th>
                          <th>Taxable</th>
                        </tr>
                      </thead>
                      <tbody v-if="info.employee.allowances?.length">
                        <tr v-for="a in info.employee.allowances" :key="a.id">
                          <td>{{ a.allowance?.name }}</td>
                          <td>{{ formatNumber(a.amount) }}/=</td>
                          <td>{{ a.taxable ? "YES" : "NO" }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="card">
                <div
                  class="card-header border-bottom py-2 d-flex justify-content-between"
                >
                  <h5 class="card-title">Deductions</h5>
                </div>
                <div class="card-body px-1 py-1">
                  <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered">
                      <thead class="bg-light">
                        <tr>
                          <th>Deduction</th>
                          <th>Amount</th>
                        </tr>
                      </thead>
                      <tbody v-if="info.employee.deductions?.length">
                        <tr v-for="d in info.employee.deductions" :key="d.id">
                          <td>{{ d.deduction?.name }}</td>
                          <td>{{ formatNumber(d.amount) }}/=</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="card">
                <div class="card-header border-bottom py-2">
                  <h5 class="card-title">Payslips</h5>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table
                      class="table table-sm table-striped table-bordered table-centered"
                    >
                      <thead class="bg-light">
                        <tr>
                          <th>#</th>
                          <th>Month</th>
                          <th>Gross Salary</th>
                          <th>Income tax</th>
                          <th>Taxable Income</th>
                          <th>Personal Relief</th>
                          <th>NSSF</th>
                          <th>PAYE</th>
                          <th>Pay after tax</th>
                          <th>SHIF</th>
                          <th>Net Pay</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(e, i) in info.employee.payrolls" :key="i">
                          <td>
                            {{ i + 1 }}
                          </td>
                          <td>{{ e.month }}</td>
                          <td>{{ formatNumber(e.gross_pay) }}/=</td>
                          <td>{{ formatNumber(e.income_tax) }}/=</td>
                          <td>{{ formatNumber(e.taxable_income) }}/=</td>
                          <td>{{ formatNumber(e.personal_relief) }}/=</td>
                          <td>{{ formatNumber(e.nssf) }}/=</td>
                          <td>{{ formatNumber(e.paye) }}/=</td>
                          <td>{{ formatNumber(e.pay_after_tax) }}/=</td>
                          <td>{{ formatNumber(e.shif) }}/=</td>
                          <td>{{ formatNumber(e.net_pay) }}/=</td>
                          <td>
                            <button
                              type="button"
                              @click="comingSoon()"
                              class="btn btn-sm btn-info"
                            >
                              <i class="fas fa-download"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </b-tab>
          </b-tabs>
        </b-col>
      </b-row>
      <div class="modal fade" id="profile">
        <div class="modal-dialog">
          <div class="modal-content">
            <form @submit.prevent="upload_profile()">
              <div class="modal-header">
                <h5 class="modal-title">
                  {{ info.employee.profile ? "Change" : "Upload" }} Profile Pic
                </h5>
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                ></button>
              </div>
              <div class="modal-body">
                <StatesComponent />
                <div class="mb-2">
                  <input
                    type="file"
                    ref="fileInput"
                    v-on:change="onFileChange"
                    required
                    class="form-control form-control-sm"
                  />
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-sm btn-warning">
                  <i class="fas fa-save"></i> Save
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
  <script setup lang="ts">
import { onMounted, reactive, watch, ref } from "vue";
import { currency } from "@/helpers/constants";
import StatesComponent from "@/states/StatesComponent.vue";
import { useApiState } from "@/stores/apiState";
import branchService from "@/api/branch-markets/branchMarketsApi.js";
import profileService from "@/api/profile/profileApi.js";
import Swal from "sweetalert2/dist/sweetalert2.js";
import "sweetalert2/src/sweetalert2.scss";
import {
  formatDate,
  getInitials,
  formatNumber,
  resetForm,
  capitalizeFirstLetter,
  comingSoon,
} from "@/helpers/helper";
import { getValidationErrors } from "@/helpers/customErrors.js";
import employeeService from "@/api/employees-and-payrolls/employeesPayrollApi.js";
import DefaultLayout from "@/layouts/DefaultLayout.vue";
import { useRoute } from "vue-router";
const route = useRoute();
const apiState = useApiState();
const formData = new FormData();
const fileInput = ref<HTMLInputElement | null>(null);
onMounted(async () => {
  await fetch();
  await fetch_data();
});

const info = reactive({
  employee: {} as any,
  branches: [] as any,
  sectors: [] as any,
  departments: [] as any,
  job_types: [] as any,
  contact_persons: [] as any,
  allowances: [] as any,
  deductions: [] as any,
});

const profile = reactive({
  user_id: route.params.id as string,
  file: "" as any,
});

const onFileChange = async (e: any) => {
  profile.file = e.target.files[0];
};

const employee = reactive({
  id: route.params.id,
  first_name: "" as string | null,
  middle_name: "" as string | null,
  last_name: "" as string | null,
  scope: "employee",
  gender: "" as string | null,
  marital_status: "" as string | null,
  national_id: "" as string | null,
  phone_number: "" as string | null,
  email: "" as string | null,
  sector_id: null as number | null,
  branch_id: null as number | null,
  date_of_birth: "" as string | null,
  address: "" as string | null,
  details: {
    job_type_id: null as number | null,
    employment_date: "" as string | null,
    engagement_type: "" as string | null,
    kra_pin: "" as string | null,
    department_id: null as number | null,
    shif: "" as string | null,
    nhif: "" as string | null,
    nssf: "" as string | null,
  },
  contact_person: {
    id: null as number | any,
    user_id: route.params.id,
    contact_person_type_id: null as null | number,
    name: "" as string | null,
    phone: "" as string | null,
  },
  salary: {
    user_id: route.params.id,
    id: null as number | any,
    salary: null as number | null,
    type: "gross" as string | null,
    taxable: true as boolean | any,
  },
  bank: {
    user_id: route.params.id,
    id: null as number | any,
    bank_name: "" as string | null,
    bank_code: null as number | null,
    branch: "" as string | null,
    account_number: "" as string | null,
    account_name: "" as string | null,
  },
});

const fetch_data = async () => {
  try {
    const persons = await employeeService.fetchContactPersons();
    const sector = await branchService.fetchSectors(0);
    const department = await employeeService.fetchDepartments();
    const job_type = await employeeService.fetchJobTypes();
    const allowances = await employeeService.fetchAllowances();
    const deductions = await employeeService.fetchDeductions();
    info.allowances = allowances.data;
    info.contact_persons = persons.data;
    info.departments = department.data;
    info.sectors = sector.data;
    info.job_types = job_type.data;
    info.deductions = deductions.data;
  } catch (error) {
    console.log(error);
  }
};
const fetch = async () => {
  try {
    apiState.setLoading(true);
    const { data } = await employeeService.fetchEmployeeDetail(route.params.id);
    info.employee = data;
    employee.sector_id = data.sector_id;
    employee.first_name = data.first_name;
    employee.middle_name = data.middle_name;
    employee.last_name = data.last_name;
    employee.email = data.email;
    employee.phone_number = data.phone_number;
    employee.gender = data.gender;
    employee.branch_id = data.branch_id;
    employee.date_of_birth = data.date_of_birth;
    employee.address = data.address;
    employee.national_id = data.national_id;
    // employee
    // details
    if (Object.keys(data.details).length > 0) {
      Object.keys(employee.details).forEach((key) => {
        if (key in data.details) {
          // @ts-ignore - we trust the structure of `data` here
          employee.details[key] = data.details[key];
        }
      });
    }

    // bank
    if (Object.keys(data.bank).length > 0) {
      Object.keys(employee.bank).forEach((key) => {
        if (key in data.bank) {
          // @ts-ignore - we trust the structure of `data` here
          employee.bank[key] = data.bank[key];
        }
      });
    }
    // salary
    if (Object.keys(data.salary).length > 0) {
      Object.keys(employee.salary).forEach((key) => {
        if (key in data.salary) {
          // @ts-ignore - we trust the structure of `data` here
          employee.salary[key] = data.salary[key];
        }
      });
    }
    apiState.setLoading(false);
  } catch (err: any) {
    apiState.setLoading(false);
  } finally {
  }
};

const upload_profile = async () => {
  try {
    formData.append("user_id", profile.user_id);
    formData.append("file", profile.file);
    apiState.setErrors([]);
    apiState.setError(false);
    apiState.setSaving(true);
    const response = await profileService.uploadProfile(formData);
    resetForm(profile);
    profile.user_id = route.params.id as string;
    if (fileInput.value) {
      fileInput.value.value = "";
    }
    apiState.setSaving(false);
    apiState.setSuccess(true);
    apiState.setMessage(
      response.message || "Employee profile uploaded successfully!"
    );
    $("#profile").modal("hide");
    await fetch();
  } catch (err: any) {
    if (typeof err.errors == "object") {
      const errors = await getValidationErrors(err.errors);
      apiState.setErrors(errors);
    }
    apiState.setSaving(false);
    apiState.setError(true);
    apiState.setMessage(err.message);
  } finally {
  }
};
</script>