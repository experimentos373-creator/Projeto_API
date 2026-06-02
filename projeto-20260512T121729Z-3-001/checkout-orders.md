# Plan: Checkout and Order Management (Group 4) - Revised

This document contains the step-by-step technical plan for implementing Checkout and Order Management in the FunShirt project, incorporating all corrections.

---

## 🎯 Overview
Implementing the full checkout pipeline for validated customers: pre-populating client profile information, calling the external payment API, saving orders/items in a database transaction with price immutability, and creating administrative interfaces for order lookup and state transitions (pending -> closed/canceled).

---

## 📋 Success Criteria
1. Verified customers can access the checkout page (`/checkout`) with their default info (NIF, Address, payment type, payment reference) prefilled.
   - **MB Mapeamento:** If client's default type is `'MB'`, map it to `'MB WAY'`.
2. Accessing checkout or submitting with an empty cart redirects back to `/cart` with an info/error message.
3. Checkout fields validation matches specifications:
   - Visa: 16 digits starting with `4`
   - PayPal: Valid email
   - MB WAY: 9 digits starting with `9`
4. The calculated total value (`value`) is verified before submission to be a positive number between `0.01` and `999999.99` with max 2 decimal places.
5. Communication with the external API `https://ainet-payments-api.vercel.app/api/payments` executes correctly, handling successful payments and gracefully reporting failure/validation issues (like HTTP 422).
6. Upon successful payment, orders and order_items are saved inside a DB transaction (restoring historical item prices and clearing the cart session).
   - Order `date` field is filled with current date (`today()->toDateString()`, format `Y-m-d` without time).
7. Trigger an email notification (`OrderPendingMail` from Group 6) when the order is successfully created in `'pending'` state.
8. Orders search interfaces:
   - Clients only see their own orders.
   - Employees see pending orders.
   - Admins see all orders with filters (status, customer_id, date).
9. Order status changes:
   - Employees can close pending orders (move to `'closed'`).
   - Admins can close or cancel pending orders. The field `reason_for_cancellation` is **optional (nullable)** when canceling.
10. Orders list and detail views are secure (protected by Policies).

---

## 🛠️ Tech Stack
- **Framework:** Laravel 11/13
- **CSS:** Tailwind CSS v4 / custom styles (premium.css)
- **Database:** SQLite (Eloquent ORM)
- **HTTP Client:** Laravel HTTP Client (`Illuminate\Support\Facades\Http`)
- **JavaScript:** Vanilla JS (used for dynamic client-side field behavior)

---

## 📂 File Structure

The following files will be created/modified:
- **Modified:**
  - `routes/web.php` - Register order and checkout routes
  - `app/Http/Controllers/CheckoutController.php` - Process checkout details & API payment requests
  - `resources/views/checkout/index.blade.php` - Build interactive checkout form
  - `app/Models/Order.php` - Verify fillable fields & relationships (done)
  - `app/Models/Order_item.php` - Verify fillable fields & relationships (done)
- **New:**
  - `app/Http/Controllers/OrderController.php` - Manage lists, details, and status updates of orders
  - `app/Policies/OrderPolicy.php` - Access control policy for orders
  - `resources/views/orders/index.blade.php` - Orders list page (responsive table + admin filters)
  - `resources/views/orders/show.blade.php` - Order details page (information + items list with previews + action buttons)

---

## 📅 Task Breakdown

### Task 1: Setup Routes and Middleware
- **Agent:** `backend-specialist`
- **Skills:** `api-patterns`
- **Priority:** P0
- **Dependencies:** None
- **Description:** Register checkout routes (`POST /checkout`) and order resource routes in `routes/web.php`.
- **INPUT:** `routes/web.php`
- **OUTPUT:** Functional checkout POST endpoint and order management route definitions.
- **VERIFY:** Run `php artisan route:list` to verify routes are registered correctly.

### Task 2: Create OrderPolicy
- **Agent:** `security-auditor`
- **Skills:** `vulnerability-scanner`
- **Priority:** P0
- **Dependencies:** None
- **Description:** Generate `OrderPolicy.php` to secure the order listings, detail views, and status updates.
- **INPUT:** Laravel CLI
- **OUTPUT:** `app/Policies/OrderPolicy.php`
- **VERIFY:** Check that policy restricts normal users to viewing only their own orders, employees to viewing and closing pending orders, and admins to managing all orders.

### Task 3: Implement CheckoutController Logic
- **Agent:** `backend-specialist`
- **Skills:** `api-patterns`, `database-design`
- **Priority:** P1
- **Dependencies:** Task 1
- **Description:** Implement `index` and `store` methods in `CheckoutController.php`.
  - Validate non-empty cart (redirect to `/cart` if empty).
  - Pre-fill customer data, mapping `'MB'` -> `'MB WAY'`.
  - Validate total price (`value`) between `0.01` and `999999.99` with max 2 decimal places.
  - Implement external HTTP payment request and handle HTTP 422 errors gracefully.
  - In database transaction, save order (using `today()->toDateString()`) and items, clear session, and trigger pending order email (G6 integration hook).
- **INPUT:** `app/Http/Controllers/CheckoutController.php`
- **OUTPUT:** Fully functional checkout pipeline controller.
- **VERIFY:** Verify behavior during mock payments.

### Task 4: Design Checkout View
- **Agent:** `frontend-specialist`
- **Skills:** `frontend-design`
- **Priority:** P2
- **Dependencies:** Task 3
- **Description:** Build the checkout form in `resources/views/checkout/index.blade.php`. Include dynamic fields with Vanilla JS to update placeholders and pattern validations. Map `'MB'` to `'MB WAY'`.
- **INPUT:** `resources/views/checkout/index.blade.php`
- **OUTPUT:** Responsive two-column checkout page.
- **VERIFY:** Check layout in browser and verify dynamic input switches.

### Task 5: Implement OrderController
- **Agent:** `backend-specialist`
- **Skills:** `database-design`
- **Priority:** P1
- **Dependencies:** Task 2, Task 3
- **Description:** Create `OrderController.php` to handle listings, detail views, and status updates. Ensure validation of `reason_for_cancellation` is `nullable` (optional) when updating status to `'canceled'`.
- **INPUT:** New file `app/Http/Controllers/OrderController.php`
- **OUTPUT:** Order manager controller.
- **VERIFY:** Verify role-based query isolation.

### Task 6: Design Order Views (Index & Show)
- **Agent:** `frontend-specialist`
- **Skills:** `frontend-design`
- **Priority:** P2
- **Dependencies:** Task 5
- **Description:** Build `resources/views/orders/index.blade.php` and `resources/views/orders/show.blade.php`. Detail view must render cancel input as optional and support cancel description.
- **INPUT:** New views in `resources/views/orders/`
- **OUTPUT:** Sleek orders dashboard, detail views with T-shirt previews, and status update options.
- **VERIFY:** Test visually in browser using different user roles.

---

## 🧪 Phase X: Verification

Execute the following commands after implementation:
```bash
# Verify syntax and linting
php artisan pint --test
# Run database schema verifications
php artisan test
```

### Compliance Checklist:
- [ ] No purple/violet hex codes used in new styles.
- [ ] No generic layouts or template placeholders.
- [ ] Access control (Policies) working on all order details.
- [ ] Cart correctly cleared after successful checkout.
- [ ] Payment failures handled cleanly without showing error traces.
