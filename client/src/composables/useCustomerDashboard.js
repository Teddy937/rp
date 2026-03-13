// composables/useCustomerDashboard.js
import { ref, computed } from "vue";
import http from "@/api/http";
import axios from "axios";

/**
 * Central composable for all Customer Dashboard API calls.
 * Keeps state, loading flags, and error handling in one place.
 */
export function useCustomerDashboard() {
  // ── State ──────────────────────────────────────────────────────────────────
  const loading = ref({
    summary: false,
    distribution: false,
    growth: false,
    defaultTrend: false,
    weekly: false,
    radar: false,
    table: false,
    list: false,
  });

  const errors = ref({});

  const summary = ref(null);
  const distribution = ref([]);
  const growth = ref({ labels: [], series: [] });
  const defaultTrend = ref({ labels: [], rates: [] });
  const weekly = ref([]);
  const radar = ref([]);
  const tableData = ref({ rows: [], grand_total: 0 });
  const customerList = ref(null); // paginated

  // ── Drill / scope state ────────────────────────────────────────────────────
  const drillPath = ref([
    { level: "organisation", label: "Organisation", id: null, param: null },
  ]);

  const currentScope = computed(() => {
    const last = drillPath.value[drillPath.value.length - 1];
    return {
      level: last.level,
      label: last.label,
      sector_id: drillPath.value.find((d) => d.level === "sector")?.id ?? null,
      branch_id: drillPath.value.find((d) => d.level === "branch")?.id ?? null,
      sub_branch_id:
        drillPath.value.find((d) => d.level === "sub_branch")?.id ?? null,
      sub_market_id:
        drillPath.value.find((d) => d.level === "sub_market")?.id ?? null,
    };
  });

  // Which group_by does the distribution/table show given current scope?
  const currentGroupBy = computed(() => {
    const level = currentScope.value.level;
    if (level === "organisation") return "sector";
    if (level === "sector") return "branch";
    if (level === "branch") return "sub_branch";
    if (level === "sub_branch") return "sub_market";
    return null; // at sub_market level → show customer list
  });

  const isLeafLevel = computed(() => currentScope.value.level === "sub_market");

  // ── Filters ────────────────────────────────────────────────────────────────
  const filters = ref({
    period: "all", // all | year | quarter | month | last_30
    months: 12,
    growthGroupBy: "sector",
  });

  // ── API helpers ────────────────────────────────────────────────────────────
  const scopeParams = () => {
    const s = currentScope.value;
    const p = {};
    if (s.sector_id) p.sector_id = s.sector_id;
    if (s.branch_id) p.branch_id = s.branch_id;
    if (s.sub_branch_id) p.sub_branch_id = s.sub_branch_id;
    if (s.sub_market_id) p.sub_market_id = s.sub_market_id;
    return p;
  };

  async function call(key, fn) {
    loading.value[key] = true;
    errors.value[key] = null;
    try {
      await fn();
    } catch (e) {
      errors.value[key] = e?.response?.data?.message ?? e.message;
      console.error(`[Dashboard] ${key} error:`, e);
    } finally {
      loading.value[key] = false;
    }
  }

  // ── Fetch functions ────────────────────────────────────────────────────────
  async function fetchSummary() {
    await call("summary", async () => {
      const { data } = await http().get("/dashboard/customers/summary", {
        params: { ...scopeParams(), period: filters.value.period },
      });
      summary.value = data.data;
    });
  }

  async function fetchDistribution() {
    if (!currentGroupBy.value) return;
    await call("distribution", async () => {
      const { data } = await http().get("/dashboard/customers/distribution", {
        params: {
          ...scopeParams(),
          group_by: currentGroupBy.value,
          period: filters.value.period,
        },
      });
      distribution.value = data.data;
    });
  }

  async function fetchGrowth() {
    await call("growth", async () => {
      const { data } = await http().get("/dashboard/customers/growth", {
        params: {
          ...scopeParams(),
          months: filters.value.months,
          group_by:
            currentScope.value.level === "organisation"
              ? "sector"
              : currentGroupBy.value ?? "total",
        },
      });
      growth.value = data.data;
    });
  }

  async function fetchDefaultTrend() {
    await call("defaultTrend", async () => {
      const { data } = await http().get("/dashboard/customers/default-trend", {
        params: { ...scopeParams(), months: filters.value.months },
      });
      defaultTrend.value = data.data;
    });
  }

  async function fetchWeekly() {
    await call("weekly", async () => {
      const { data } = await http().get("/dashboard/customers/weekly", {
        params: scopeParams(),
      });
      weekly.value = data.data;
    });
  }

  async function fetchRadar() {
    await call("radar", async () => {
      const { data } = await http().get("/dashboard/customers/radar", {
        params: scopeParams(),
      });
      radar.value = data.data;
    });
  }

  async function fetchTable() {
    if (!currentGroupBy.value) return;
    await call("table", async () => {
      const { data } = await http().get("/dashboard/customers/table", {
        params: {
          ...scopeParams(),
          group_by: currentGroupBy.value,
          period: filters.value.period,
        },
      });
      tableData.value = data.data;
    });
  }

  async function fetchCustomerList(extra = {}) {
    await call("list", async () => {
      const { data } = await http().get("/dashboard/customers/list", {
        params: { ...scopeParams(), ...extra },
      });
      customerList.value = data.data;
    });
  }

  // ── Fetch ALL for current scope ────────────────────────────────────────────
  async function fetchAll() {
    await Promise.all([
      fetchSummary(),
      fetchDistribution(),
      fetchGrowth(),
      fetchDefaultTrend(),
      fetchWeekly(),
      fetchRadar(),
      isLeafLevel.value ? fetchCustomerList() : fetchTable(),
    ]);
  }

  // ── Drill down / up ────────────────────────────────────────────────────────
  const LEVEL_ORDER = [
    "organisation",
    "sector",
    "branch",
    "sub_branch",
    "sub_market",
  ];

  function drillDown(row) {
    // row = { id, label, drills_into: 'branch'|'sub_branch'|'sub_market'|null }
    if (!row.drills_into) return;

    drillPath.value.push({
      level: row.drills_into,
      label: row.label,
      id: row.id,
      param: row.drill_param,
    });
    fetchAll();
  }

  function drillUpTo(index) {
    drillPath.value = drillPath.value.slice(0, index + 1);
    fetchAll();
  }

  function resetDrill() {
    drillPath.value = [
      { level: "organisation", label: "Organisation", id: null, param: null },
    ];
    fetchAll();
  }

  // ── Period filter ──────────────────────────────────────────────────────────
  function setPeriod(period) {
    filters.value.period = period;
    fetchAll();
  }

  function setMonths(months) {
    filters.value.months = months;
    fetchGrowth();
    fetchDefaultTrend();
  }

  return {
    // state
    loading,
    errors,
    summary,
    distribution,
    growth,
    defaultTrend,
    weekly,
    radar,
    tableData,
    customerList,
    drillPath,
    currentScope,
    currentGroupBy,
    isLeafLevel,
    filters,
    // actions
    fetchAll,
    fetchSummary,
    fetchDistribution,
    fetchGrowth,
    fetchDefaultTrend,
    fetchWeekly,
    fetchRadar,
    fetchTable,
    fetchCustomerList,
    drillDown,
    drillUpTo,
    resetDrill,
    setPeriod,
    setMonths,
  };
}
