const setTitle = (title: string) => {
  return title
    ? `${title} | Retail Pay Admin Portal`
    : "Retail Pay Admin Portal";
};

const authRoutes = [
  {
    path: "/auth/success",
    name: "auth.success",
    meta: {
      title: setTitle("Onboard"),
    },
    component: () => import("@/views/auth/success.vue"),
  },
  {
    path: "/auth/forgot-pass/success",
    name: "auth.forgot.success",
    meta: {
      title: setTitle("Onboard"),
    },
    component: () => import("@/views/auth/forgot-pass-success.vue"),
  },
  {
    path: "/auth/sign-in",
    name: "auth.sign-in",
    meta: {
      title: setTitle("Sign In"),
    },
    component: () => import("@/views/auth/login.vue"),
  },

  {
    path: "/auth/reset-pass",
    name: "auth.reset-pass",
    meta: {
      title: setTitle("Reset Password"),
    },
    component: () => import("@/views/auth/reset-pass.vue"),
  },
  {
    path: "/auth/signin-otp/:node",
    name: "auth.sign-otp",
    meta: {
      title: setTitle("Sign In OTP"),
    },
    component: () => import("@/views/auth/login-otp.vue"),
  },
  {
    path: "/auth/verify-otp/:node",
    name: "auth.verify-otp",
    meta: {
      title: setTitle("Verify OTP"),
    },
    component: () => import("@/views/auth/verify-otp.vue"),
  },
  {
    path: "/auth/new-password/:node",
    name: "auth.new-password",
    meta: {
      title: setTitle("New Password"),
    },
    component: () => import("@/views/auth/new-password.vue"),
  },
];

const errorRoutes = [
  {
    path: "/auth/error-404",
    name: "error.404",
    meta: {
      title: setTitle("Error 404"),
    },
    component: () => import("@/views/auth/error-404.vue"),
  },
  {
    path: "/auth/error-500",
    name: "error.500",
    meta: {
      title: setTitle("Error 500"),
    },
    component: () => import("@/views/auth/error-500.vue"),
  },
  {
    path: "/auth/error-403",
    name: "error.403",
    meta: {
      title: setTitle("Error 403"),
      authRequired: true,
    },
    component: () => import("@/views/auth/error-403.vue"),
  },
  {
    path: "/:catchAll(.*)",
    redirect: "/auth/error-404",
  },
];

const dashboardRoutes = [
  {
    path: "/",
    name: "sso.dashboard",
    meta: {
      title: setTitle("Dashboard"),
      authRequired: true,
    },
    component: () => import("@/views/dashboard/Dashboard.vue"),
  },
];

const profileRoutes = [
  {
    path: "/profile/:id",
    name: "profile.view",
    meta: {
      title: setTitle("View Profile"),
      authRequired: true,
    },
    component: () => import("@/views/profile/ProfileComponent.vue"),
  },
];
const branchRoutes = [
  {
    path: "/branches/list",
    name: "branches.view",
    meta: {
      title: setTitle("View branches"),
      authRequired: true,
    },
    component: () => import("@/views/branches/BranchView.vue"),
  },
  {
    path: "/branches/:id/details",
    name: "branches.details",
    meta: {
      title: setTitle("View branch details"),
      authRequired: true,
    },
    component: () => import("@/views/branches/BranchDetailView.vue"),
  },
  {
    path: "/store/list",
    name: "store.view",
    meta: {
      title: setTitle("View store list"),
      authRequired: true,
    },
    component: () => import("@/views/branches/StoreView.vue"),
  },
  {
    path: "/store/:id/details",
    name: "store.details",
    meta: {
      title: setTitle("View store details"),
      authRequired: true,
    },
    component: () => import("@/views/branches/StoreDetailView.vue"),
  },
];

const logsRoutes = [
  {
    path: "/logs/list",
    name: "logs.view",
    meta: {
      title: setTitle("Logs list"),
      authRequired: true,
    },
    component: () => import("@/views/logs/AuditLogs.vue"),
  },
];

const skuRoutes = [
  {
    path: "/sku/list",
    name: "sku.view",
    meta: {
      title: setTitle("Sku list"),
      authRequired: true,
    },
    component: () => import("@/views/skus/SkuView.vue"),
  },
  {
    path: "/sku/create",
    name: "sku.create",
    meta: {
      title: setTitle("Create SKU"),
      authRequired: true,
    },
    component: () => import("@/views/skus/SkuCreate.vue"),
  },
  {
    path: "/sku/:id/edit",
    name: "sku.edit",
    meta: {
      title: setTitle("Edit SKU"),
      authRequired: true,
    },
    component: () => import("@/views/skus/SkuCreate.vue"),
  },
];

const stockRoutes = [
  {
    path: "/stock/lists",
    name: "stock.lists",
    meta: {
      title: setTitle("Stock ledger list"),
      authRequired: true,
    },
    component: () => import("@/views/stock-ledger/StoreStockView.vue"),
  },
  {
    path: "/stock/alerts",
    name: "stock.alerts",
    meta: {
      title: setTitle("Stock alerts"),
      authRequired: true,
    },
    component: () => import("@/views/stock-ledger/StockAlerts.vue"),
  },
];

const stockMovementRoutes = [
  {
    path: "/stock-movement/initiate-transfer",
    name: "stock.initiate.transfer",
    meta: {
      title: setTitle("Stock initiate transfer"),
      authRequired: true,
    },
    component: () => import("@/views/stock-movement/InitiateTransfer.vue"),
  },
  {
    path: "/stock-movement/:id/details",
    name: "stock.movement.details",
    meta: {
      title: setTitle("Stock movement details"),
      authRequired: true,
    },
    component: () => import("@/views/stock-movement/MovementDetail.vue"),
  },
  {
    path: "/stock-movement/list",
    name: "stock.movement.list",
    meta: {
      title: setTitle("Stock movement list"),
      authRequired: true,
    },
    component: () => import("@/views/stock-movement/MovementList.vue"),
  },
  {
    path: "/stock-movement/pending-approvals",
    name: "stock.movement.pending.approvals",
    meta: {
      title: setTitle("Stock movement approvals"),
      authRequired: true,
    },
    component: () => import("@/views/stock-movement/PendingApprovals.vue"),
  },
  {
    path: "/stock-movement/record-adjustment",
    name: "stock.movement.record.adjustment",
    meta: {
      title: setTitle("Stock movement record adjustment"),
      authRequired: true,
    },
    component: () => import("@/views/stock-movement/RecordAdjustment.vue"),
  },
  {
    path: "/stock-movement/record-sale",
    name: "stock.movement.record.sale",
    meta: {
      title: setTitle("Stock movement record sale"),
      authRequired: true,
    },
    component: () => import("@/views/stock-movement/RecordSale.vue"),
  },
  {
    path: "/stock-movement/record-procurement",
    name: "stock.movement.record.procurement",
    meta: {
      title: setTitle("Stock movement record procurement"),
      authRequired: true,
    },
    component: () => import("@/views/stock-movement/RecordProcurement.vue"),
  },
];

const usersRoutes = [
  {
    path: "/user/list",
    name: "user.list",
    meta: {
      title: setTitle("Users List"),
      authRequired: true,
    },
    component: () => import("@/views/users/UserList.vue"),
  },
  {
    path: "/user/creation",
    name: "users.create",
    meta: {
      title: setTitle("User creation"),
      authRequired: true,
    },
    component: () => import("@/views/users/UserCreation.vue"),
  },
  {
    path: "/user/:id/edit",
    name: "users.edit",
    meta: {
      title: setTitle("User edit"),
      authRequired: true,
    },
    component: () => import("@/views/users/UserCreation.vue"),
  },
];

export const allRoute = [
  ...authRoutes,
  ...errorRoutes,
  ...dashboardRoutes,
  ...profileRoutes,
  ...branchRoutes,
  ...logsRoutes,
  ...skuRoutes,
  ...stockRoutes,
  ...stockMovementRoutes,
  ...usersRoutes,
];
