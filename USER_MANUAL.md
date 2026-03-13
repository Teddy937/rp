# KK Wholesalers — User Manual

---

## Table of Contents

1. [Getting Started](#1-getting-started)
2. [Roles & Permissions](#2-roles--permissions)
3. [Logging In](#3-logging-in)
4. [Branches & Stores](#4-branches--stores)
5. [SKU Management](#5-sku-management)
6. [Stock Ledger](#6-stock-ledger)
7. [Stock Movements](#7-stock-movements)
8. [Users](#8-users)
9. [Audit Logs](#9-audit-logs)
10. [Account Settings](#10-account-settings)

---

## 1. Getting Started

KK Wholesalers Inventory System is a web-based platform for managing stock across multiple branches and stores. It gives you real-time visibility into inventory levels, stock movements, transfers, and user activity.

**Access the system at:** `http://localhost:5173` (or your deployed URL)

---

## 2. Roles & Permissions

The system has three roles. What you can see and do depends on your assigned role.

| Feature | Administrator | Branch Manager | Store Manager |
|---|:---:|:---:|:---:|
| Manage Branches | ✅ | ❌ | ❌ |
| Manage Stores | ✅ | ✅ | ❌ |
| Manage SKUs | ✅ | ✅ | ❌ |
| View Stock Ledger | ✅ | ✅ | ✅ |
| Record Sale | ✅ | ✅ | ✅ |
| Request Transfer | ✅ | ✅ | ✅ |
| Approve Transfer | ✅ | ✅ | ❌ |
| Record Adjustment | ✅ | ✅ | ❌ |
| Record Procurement | ✅ | ✅ | ❌ |
| Manage Users | ✅ | ❌ | ❌ |
| View Audit Logs | ✅ | ❌ | ❌ |

> **Note:** Branch Managers only see data scoped to their own branch. Store Managers only see data for their assigned store.

---

## 3. Logging In

### Step 1 — Enter Credentials
1. Go to the login page
2. Enter your **Email** and **Password**
3. Click **Login**

### Step 2 — OTP Verification (2FA)
After your credentials are verified, a 6-digit OTP is sent to your registered contact.

1. Enter the 6-digit code in the boxes provided
2. Click **Verify**
3. The code expires in **10 minutes**
4. If you did not receive it, click **Resend OTP** (available after the 60-second countdown)

### Default Accounts (Development)

| Email | Role | Password |
|---|---|---|
| admin@kkwholesalers.co.ke | Administrator | password |
| alice@kkwholesalers.co.ke | Branch Manager (Branch A) | password |
| brian@kkwholesalers.co.ke | Branch Manager (Branch B) | password |
| carol@kkwholesalers.co.ke | Store Manager (Store A1) | password |
| david@kkwholesalers.co.ke | Store Manager (Store B1) | password |
| eve@kkwholesalers.co.ke | Store Manager (Store B2) | password |

---

## 4. Branches & Stores

### Branches
Branches are the top-level organisational units (e.g. Branch A, Branch B).

**To view branches:**
Navigate to **Branches** in the sidebar. You will see a paginated list with the store count per branch.

**To add a branch** *(Administrator only)*:
1. Click **Add Branch**
2. Fill in the branch name, code, location, and contact details
3. Click **Save**

**To edit a branch:**
Click the ✏️ edit icon on the branch row, update the fields, and save.

**To delete a branch:**
Click the 🗑️ delete icon. A confirmation prompt will appear. Note that deleting a branch will affect its linked stores and users.

---

### Stores
Stores belong to a branch. Each store has its own stock ledger.

**To view stores:**
Navigate to **Stores** in the sidebar. You can filter by branch using the dropdown.

**To add a store** *(Administrator or Branch Manager)*:
1. Click **Add Store**
2. Select the parent branch
3. Fill in name, location, and phone
4. Click **Save**

**To view store details:**
Click the store name to open the detail view, which shows the store's branch, contact info, and quick links to its stock and movements.

---

## 5. SKU Management

SKUs (Stock Keeping Units) are the products tracked across all stores.

**To view SKUs:**
Navigate to **SKUs** in the sidebar. You can search by name or code.

**To add a SKU** *(Administrator or Branch Manager)*:
1. Click **Add SKU**
2. Fill in:
   - **Name** — product name
   - **Code** — unique identifier (e.g. `RICE-5KG`)
   - **Unit** — measurement unit (e.g. `kg`, `pcs`, `litres`)
   - **Unit Cost** — buying price
   - **Unit Price** — selling price (margin is calculated automatically)
   - **Reorder Level** — quantity at which a low-stock alert is triggered
3. Click **Save**

**To edit a SKU:**
Click the ✏️ edit icon on the SKU row. The same form opens pre-filled.

**To delete a SKU:**
Click the 🗑️ delete icon and confirm. SKUs with stock movements cannot be deleted.

---

## 6. Stock Ledger

The stock ledger shows the current quantity on hand for every SKU in a store.

**To view stock for a store:**
1. Navigate to **Stock Ledger** in the sidebar
2. Select a store from the dropdown
3. The table shows each SKU, its current quantity, reorder level, and status

**Summary cards at the top show:**
- Total SKUs tracked
- SKUs currently in stock
- SKUs at or below reorder level (low stock)
- SKUs with zero quantity (out of stock)

**Searching:** Use the search box to filter by SKU name or code. This is instant — no page reload.

---

### Stock Alerts
Navigate to **Stock Alerts** to see only the SKUs that are at or below their reorder level.

- The **Shortage** column shows how many units are needed to reach the reorder level
- Click **Restock** to go directly to the Procurement form pre-filled with that SKU and store

---

## 7. Stock Movements

Stock movements are the records of every time stock changes — sales, transfers, procurement, and adjustments.

### Viewing Movements
Navigate to **Stock Movements**. You can filter by:
- Type (Sale, Transfer, Adjustment, Procurement)
- Status (Pending, Completed, Rejected)
- Date range
- Reference number

Click the 👁️ icon on any row to view full movement details.

---

### Recording a Sale
1. Go to **Movements → Record Sale**
2. Select the **Store**
3. Select the **SKU** — the current stock quantity is shown next to it
4. Enter the **Quantity** sold
5. Enter the **Unit Price** at point of sale
6. Click **Record Sale**

> The system will block the submission if the quantity entered exceeds available stock.

---

### Recording a Transfer
Transfers move stock between stores and require approval.

1. Go to **Movements → Record Transfer**
2. Select the **From Store** — available stock loads automatically
3. Select the **To Store** (must be different from source)
4. Select the **SKU** and enter the **Quantity**
5. Click **Submit Transfer**

The transfer is created with status **Pending** until a Branch Manager or Administrator approves it.

---

### Approving / Rejecting Transfers
*Available to Branch Managers and Administrators.*

1. Go to **Movements → Pending Transfers**
2. Review the list of pending transfers
3. Click ✅ **Approve** to approve — stock is immediately moved
4. Click ❌ **Reject** to reject — enter a reason, then confirm

---

### Recording Procurement
Procurement adds new stock into a store (from a supplier).

1. Go to **Movements → Record Procurement**
2. Select the **Branch**, then the **Store**
3. Select the **SKU** — unit cost auto-fills from the SKU record
4. Enter the **Quantity** received
5. Optionally update the unit cost and add notes
6. Click **Record Procurement**

Stock is added immediately upon saving.

---

### Recording an Adjustment
Adjustments correct stock discrepancies (e.g. damaged goods, counting errors).

1. Go to **Movements → Record Adjustment**
2. Select the store and SKU
3. Choose **Stock In** (add) or **Stock Out** (deduct)
4. Enter the quantity and a mandatory note explaining the reason
5. Click **Record Adjustment**

---

## 8. Users

*Available to Administrators only.*

### Viewing Users
Navigate to **Users** in the sidebar. You can filter by role, account status, and search by name, email, or phone.

**Status indicators:**
- 🟢 **Online** — user has an active session
- ⚫ **Offline** — no active session
- Badge colours: Red = Administrator, Yellow = Branch Manager, Blue = Store Manager

---

### Adding a User
1. Click **Add User**
2. Fill in identity details (name, email, phone, etc.)
3. Select a **Role**:
   - If **Branch Manager** — select the branch they manage
   - If **Store Manager** — select the branch, then the store
4. Set a **Password** (minimum 8 characters)
5. Click **Create User**

---

### Editing a User
Click the ✏️ edit icon on the user row. You can update all fields except the password (use Reset Password for that).

---

### Resetting a Password
Click the 🔑 key icon on the user row, enter the new password and confirm it, then click **Reset Password**.

---

### Changing Account Status
Click the ⚙️ icon on the user row and select the new status:
- **Active** — user can log in normally
- **Inactive** — soft-disabled, cannot log in
- **Suspended** — access blocked pending review

---

### Deleting a User
Click the 🗑️ delete icon and confirm. You cannot delete your own account.

---

## 9. Audit Logs

*Available to Administrators only.*

Navigate to **Audit Logs** to see a full trail of every action performed in the system.

**You can filter by:**
- User
- Action (created, updated, deleted, approved, rejected, login, logout)
- Model type (User, Branch, Store, SKU, Stock Movement)
- Date range
- Search (description, IP address, user name)

**To view before/after changes:**
Click the `< >` icon on any log entry that has a diff. A modal shows the old values and new values side by side in JSON format.

---

## 10. Account Settings

### Changing Your Password
1. Click your name/avatar in the top navigation
2. Select **Change Password**
3. Enter your current password, then the new password twice
4. Click **Update Password**

### Logging Out
Click your name/avatar in the top navigation and select **Logout**. Your session is immediately terminated.

---

*KK Wholesalers Inventory System — Internal Use Only*