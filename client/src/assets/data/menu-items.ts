import { hasPermissionInModules } from "@/helpers/permissions";
import type { MenuItemType } from "@/types/menu";

export const MENU_ITEMS: MenuItemType[] = [
  // ── Dashboard ─────────────────────────────────────────────────────────────
  {
    key: "dashboard-basic",
    icon: "iconoir-home-alt",
    label: "Dashboard",
    route: { name: "sso.dashboard" },
  },

  // ── Branches & Stores ─────────────────────────────────────────────────────
  {
    key: "branches-and-stores",
    icon: "iconoir-building",
    label: "Branches & Stores",
    children: [
      {
        key: "branches-list",
        label: "Branches",
        route: { name: "branches.view" },
        parentKey: "branches-and-stores",
        permission: "Can view branches", // just for demo
      },
      {
        key: "store-list",
        label: "Stores",
        route: { name: "store.view" },
        parentKey: "branches-and-stores",
        permission: "Can view stores",
      },
    ],
  },

  // ── SKU Management ────────────────────────────────────────────────────────
  {
    key: "sku-management",
    icon: "iconoir-box",
    label: "SKU Management",
    children: [
      {
        key: "sku-list",
        label: "All SKUs",
        route: { name: "sku.view" },
        parentKey: "sku-management",
        permission: "",
      },
      {
        key: "sku-create",
        label: "Add New SKU",
        route: { name: "sku.create" },
        parentKey: "sku-management",
        permission: "",
      },
    ],
  },

  // ── Stock Ledger ──────────────────────────────────────────────────────────
  {
    key: "stock-ledger",
    icon: "iconoir-reports",
    label: "Stock Ledger",
    children: [
      {
        key: "stock-list",
        label: "Store Stock",
        route: { name: "stock.lists" },
        parentKey: "stock-ledger",
        permission: "",
      },
      {
        key: "stock-alerts",
        label: "Low Stock Alerts",
        route: { name: "stock.alerts" },
        parentKey: "stock-ledger",
        permission: "",
      },
    ],
  },

  // ── Stock Movements ───────────────────────────────────────────────────────
  {
    key: "stock-movements",
    icon: "iconoir-arrow-separate",
    label: "Stock Movements",
    children: [
      {
        key: "movement-list",
        label: "All Movements",
        route: { name: "stock.movement.list" },
        parentKey: "stock-movements",
      },
      {
        key: "movement-pending-approvals",
        label: "Pending Approvals",
        route: { name: "stock.movement.pending.approvals" },
        parentKey: "stock-movements",
      },
      {
        key: "movement-record-sale",
        label: "Record Sale",
        route: { name: "stock.movement.record.sale" },
        parentKey: "stock-movements",
      },
      {
        key: "movement-initiate-transfer",
        label: "Initiate Transfer",
        route: { name: "stock.initiate.transfer" },
        parentKey: "stock-movements",
      },
      {
        key: "movement-record-adjustment",
        label: "Record Adjustment",
        route: { name: "stock.movement.record.adjustment" },
        parentKey: "stock-movements",
      },
      {
        key: "movement-record-procurement",
        label: "Record Procurement",
        route: { name: "stock.movement.record.procurement" },
        parentKey: "stock-movements",
      },
    ],
  },

  // ── User Management ───────────────────────────────────────────────────────
  {
    key: "user-management",
    icon: "iconoir-community",
    label: "User Management",
    children: [
      {
        key: "user-list",
        label: "All Users",
        route: { name: "user.list" },
        parentKey: "user-management",
      },
      {
        key: "user-create",
        label: "Add New User",
        route: { name: "users.create" },
        parentKey: "user-management",
      },
    ],
  },

  // ── Audit Logs ────────────────────────────────────────────────────────────
  {
    key: "audit-logs",
    icon: "iconoir-journal-page",
    label: "Audit Logs",
    route: { name: "logs.view" },
  },
];
