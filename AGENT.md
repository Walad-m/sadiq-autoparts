# AGENT.md — Sabr 89 Management System
> Your single source of truth. Read the section you need. Do the work. Come back for the next.
> One step at a time. Understand it completely before moving forward.

---

## HOW TO USE THIS FILE

This document is your complete guide. It tells you:
- What you are building and why every decision was made
- Your exact confirmed tech stack and what each tool does
- The full database design
- Every build stage broken into numbered steps you can follow one at a time
- What you should understand after completing each step

**Rule:** Do not skip ahead. Every stage builds on the one before it.
**Rule:** Read a step, understand it, do it, confirm it works, then move on.
**Rule:** Reuse what the starter kit gives you. Only create new files when nothing existing can do the job.

---

## PART 1 — THE PROJECT

### What You Are Building

A complete business management system for **Sabr 89**, a car parts shop in Kumasi, Ghana.



### Who Uses the System

| Person | Role | What they can do |
|---|---|---|
| Sabr (owner) | Admin | Everything — full access to all modules |


### The Eight Modules

| Module | Purpose |
|---|---|
| Dashboard | Morning overview — revenue, stock health, low-stock alerts |
| Point of Sale | Sell parts at the counter, print receipts |
| Products | Manage the parts catalogue and stock levels |
| Sales | Full history of every transaction |
| Customers | Track who buys from Sabr |
| Suppliers | Track where stock comes from |
| Expenses | Record and categorise business costs |
| Reports | Daily closing, monthly P&L, stock valuation |

---

---

## PROJECT STATUS

- **Stage 1 — Design system & layout:** ✅ Completed. (Light mode default, 8 brand colors, Syne + DM Sans fonts, constants, sidebar, header, dashboard scaffold)
- **Stage 2 — Permissions & auditing:** ✅ Completed. (Spatie permission installed, roles table seeded with 30+ permissions, User model updated with HasRoles trait, activitylog ready)
- **Stage 3 — Data models & CRUD:** ✅ Completed. (All migrations, models with relationships + casts, Form Request validation, resource controllers with $request->validated(), Products CRUD with full form, seeders for categories/suppliers/products/customers)
- **Stage 4 — Customers & Suppliers:** ✅ Completed. (CustomerController, SupplierController with Form Requests, frontend pages)
- **Current stage:** Stage 5 — Point of Sale (POS) (next).

Stage 5 immediate tasks:

- Install Zustand, react-hotkeys-hook, react-to-print.
- Create cart store (use-cart.ts) with Zustand.
- Create SaleService with DB::transaction.
- Create PosController with search and store methods.
- Build the two-panel POS layout.

**IMPORTANT — Dropped fields:** `barcode` and `brand` have been intentionally removed from the products table. The system does not use barcode scanning or brand tracking.

Follow the staged roadmap below in order. Confirm each Stage's acceptance criteria before progressing.

## PART 2 — YOUR CONFIRMED TECH STACK

These are confirmed from your actual project files. Do not guess — reference this table.

| Technology | Version | Role |
|---|---|---|
| PHP | ^8.3 | Language Laravel runs on |
| Laravel | ^13.7 | Backend framework — routing, database, business logic |
| React | ^19.2 | Frontend UI library — what the user sees |
| Inertia.js | ^3.0 | Bridge between Laravel and React — no REST API needed |
| TypeScript | ^5.7 | Typed JavaScript — catches errors before runtime |
| Tailwind CSS | ^4.0 | Utility CSS — style with class names in JSX |
| Vite | ^8.0 | Build tool — compiles frontend assets |
| shadcn/ui | new-york style | Pre-built accessible React components |
| Lucide React | ^0.475 | Icon library — already installed |
| Sonner | ^2.0 | Toast notifications — already installed |
| Laravel Wayfinder | latest | Auto-generates typed TypeScript route helpers |

### What the Starter Kit Already Gave You (NEVER reinstall or overwrite these)

Herd created your project with all of this already working:

- ✅ Authentication — login, register, password reset, email verification
- ✅ shadcn/ui — configured with `new-york` style, `neutral` base color
- ✅ Tailwind CSS v4 — configured in `vite.config.ts`, NOT in a config file
- ✅ Inertia.js v3 — fully wired between Laravel and React
- ✅ TypeScript strict mode — type errors are enforced
- ✅ ESLint + Prettier — code quality tools ready
- ✅ Lucide React — icon library ready to import
- ✅ Sonner v2 — toast notifications ready
- ✅ Laravel Wayfinder — typed route helpers auto-generated
- ✅ Pest v4 — testing framework ready
- ✅ Bunny Fonts — privacy-friendly font loading via Vite

**Your package manager is `npm`.** Always use `npm install`, not `pnpm` or `yarn`.

### Packages You Will Install During the Project

Install each one only at the stage listed. This way you understand WHY each package exists.

```
STAGE 2:  composer require spatie/laravel-permission
          composer require spatie/laravel-activitylog

STAGE 3:  npm install @tanstack/react-table
          npm install date-fns

STAGE 5:  npm install zustand
          npm install react-hotkeys-hook
          npm install @react-thermal-printer/printer
          npm install react-to-print

STAGE 6:  composer require barryvdh/laravel-dompdf

STAGE 8:  npm install recharts

STAGE 9:  composer require maatwebsite/excel
```

---

## PART 3 — UNDERSTANDING YOUR TOOLS

Read this once before writing any code. Understanding why each tool exists makes the whole project make sense.

### Laravel — The Backend Engine

Laravel is your server. When a user visits a URL or submits a form, Laravel:
1. Receives the request
2. Checks if the user is logged in and has permission
3. Validates the data
4. Reads from or writes to the database
5. Returns a response

You write Laravel code in PHP inside the `app/` folder.

### React — The Frontend UI

React builds everything the user sees. It runs in the browser, not on the server. You build the UI from components — reusable pieces like buttons, tables, cards, and forms. You write React code in TypeScript/TSX inside `resources/js/`.

### Inertia.js — The Bridge (Most Important to Understand)

Normally if you use Laravel + React together, you need a REST API. Laravel exposes JSON endpoints, React fetches them — double the work, two separate systems to maintain.

Inertia eliminates that completely:

1. You write a normal Laravel controller
2. Instead of `return view('...')`, you return `return Inertia::render('page-name', $data)`
3. Inertia automatically passes `$data` as props to your React component
4. React renders the page — no full reload, feels like a modern app
5. When the user clicks a link, Inertia intercepts it and only fetches new data

**Result:** One codebase. Write Laravel the way Laravel was designed. Write React normally. Inertia connects them.

```php
// PHP controller — returns an Inertia page
public function index()
{
    return Inertia::render('products/index', [
        'products' => Product::with('category')->paginate(20),
    ]);
}
```

```tsx
// React component — receives the data as props
export default function ProductsIndex({ products }: Props) {
    return <div>{products.data.map(p => <div key={p.id}>{p.name}</div>)}</div>;
}
```

### TypeScript — Catches Mistakes Before They Crash

TypeScript is JavaScript with types added. Your editor catches mistakes while you type instead of when the app crashes in production.

```typescript
// JavaScript — crashes at runtime with no warning
function formatPrice(amount) { return '₵' + amount.toFixed(2); }
formatPrice('hello'); // crashes

// TypeScript — VS Code shows a red underline immediately
function formatPrice(amount: number): string { return '₵' + amount.toFixed(2); }
formatPrice('hello'); // red underline before you even run it
```

### Tailwind CSS v4 — Important Differences from v3

You write class names directly in JSX instead of separate CSS files.

```tsx
<div className="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
    Content here
</div>
```

**Critical for your project:** You have Tailwind v4. This is different from most tutorials online which show v3.
- There is **no `tailwind.config.js`** file
- All configuration goes inside `resources/css/app.css` using `@theme {}` blocks
- Custom colors are defined once in `app.css` and then used as Tailwind classes everywhere

### shadcn/ui — Pre-Built Professional Components

Instead of building every button, dropdown, dialog, and table from scratch, you use shadcn's. They are already styled, accessible, and match your Tailwind setup.

To add a component you don't have yet:
```bash
npx shadcn@latest add dialog
npx shadcn@latest add select
npx shadcn@latest add popover
```

The files go into `resources/js/components/ui/` — **never edit these manually.**

### Laravel Wayfinder — Typed Route Helpers

Instead of hardcoding URL strings (which break silently when you rename a route), Wayfinder generates typed TypeScript functions from your Laravel routes.

```typescript
// Without Wayfinder — typos cause silent 404 errors
router.post('/produts', data); // typo, breaks silently

// With Wayfinder — TypeScript shows an error if the route doesn't exist
import { store } from '@/routes/products';
router.post(store(), data); // auto-generated, refactor-safe
```

Wayfinder regenerates every time you run `npm run dev` or `npm run build`.

---

## PART 4 — THE DESIGN SYSTEM

Everything visual follows these rules. Consistent design makes a professional product.

### The Approach

The starter kit comes with a full light/dark theme system. You will:
1. Keep all of that working
2. Set **light mode as the default** (one line change)
3. Add your Sabr brand colors on top as custom Tailwind classes
4. Apply those colors only to the KPI cards and key accents

Do not fight the starter kit's theme system — extend it.

### Brand Colors

These go in `resources/css/app.css` inside the `@theme {}` block.

```css
/* Sabr brand — used for KPI cards and accents */
--color-sabr-red:    #C8410A;   /* Today's Sales, primary buttons, active nav */
--color-sabr-gold:   #F5C842;   /* Transactions, highlights, logo text */
--color-sabr-black:  #faf8f8;   /* Sidebar background, Total Products card */
--color-sabr-teal:   #0F6E56;   /* Customers card, MoMo indicator */
--color-sabr-blue:   #185FA5;   /* Monthly Revenue card */
--color-sabr-purple: #534AB7;   /* Pending Orders card */
--color-sabr-coral:  #D85A30;   /* Expenses card */
--color-sabr-green:  #3B6D11;   /* Gross Profit card, Cash indicator */
```

After adding these, you use them as Tailwind classes: `bg-sabr-red`, `text-sabr-gold`, `border-sabr-teal`.

### UI Surface Colors (Light Mode)

```
Page background:  #F7F6F3  — warm off-white, not cold gray
Card background:  #FFFFFF
Border:           #E0DDD8
Text primary:     #1A1A1A
Text muted:       #6B7280
```

### Typography

Your `vite.config.ts` already loads fonts via Bunny Fonts. You will update it to use:
- **Syne** — headings, page titles, logo (bold and modern)
- **DM Sans** — body text, labels, table content (clean, readable)
- **JetBrains Mono** — POS receipt numbers and prices (looks like a real receipt)

### Sidebar Design

```
Background:       # #faf8f8
Width:            240px, fixed position
Logo area:        use the logo i will provide in the public folder 
Nav items:        Gray text + Lucide icon, subtle hover highlight
Active nav item:  bg-sabr-red, white text
Bottom:           Settings link pinned to bottom
```

### KPI Cards (8 on the Dashboard)

Each card has a solid bold background. Not pastel. Not faded. Each card owns its color.

```
Structure of each card:
  - Label: small caps, top-left, slightly transparent white
  - Value: large bold number, center/left
  - Subtitle: small text, bottom (e.g. "↑ 12% vs yesterday")
  - Icon: large Lucide icon, top-right corner, 20% opacity (decorative)
```

| Card | Color |
|---|---|
| Today's Sales | bg-sabr-red |
| Transactions | bg-sabr-gold (dark text) |
| Total Products | bg-sabr-black |
| Customers | bg-sabr-teal |
| Monthly Revenue | bg-sabr-blue |
| Pending Orders | bg-sabr-purple |
| Expenses (Month) | bg-sabr-coral |
| Gross Profit | bg-sabr-green |

---

## PART 5 — FILE STRUCTURE

Know where everything lives. Only create files that are not already provided by the starter kit.

```
sabr-89/
│
├── app/                                    ← ALL PHP/Laravel backend code
│   ├── Http/
│   │   ├── Controllers/                    ← Handle requests, return Inertia pages
│   │   │   ├── DashboardController.php     ← create (invokable, single action)
│   │   │   ├── ProductController.php       ← create (resource)
│   │   │   ├── CategoryController.php      ← create (resource)
│   │   │   ├── SaleController.php          ← create (resource)
│   │   │   ├── PosController.php           ← create (manual methods)
│   │   │   ├── CustomerController.php      ← create (resource)
│   │   │   ├── SupplierController.php      ← create (resource)
│   │   │   ├── ExpenseController.php       ← create (resource)
│   │   │   └── ReportController.php        ← create (manual methods)
│   │   ├── Requests/                       ← Form validation — one per form
│   │   │   ├── StoreProductRequest.php
│   │   │   ├── UpdateProductRequest.php
│   │   │   ├── StoreSaleRequest.php
│   │   │   ├── StoreCustomerRequest.php
│   │   │   ├── StoreSupplierRequest.php
│   │   │   └── StoreExpenseRequest.php
│   │   └── Middleware/                     ← already exists, do not edit
│   ├── Models/                             ← One class per database table
│   │   ├── User.php                        ← ALREADY EXISTS — only add traits
│   │   ├── Product.php                     ← create
│   │   ├── Category.php                    ← create
│   │   ├── Sale.php                        ← create
│   │   ├── SaleItem.php                    ← create
│   │   ├── Customer.php                    ← create
│   │   ├── Supplier.php                    ← create
│   │   ├── Expense.php                     ← create
│   │   └── PurchaseOrder.php               ← create
│   └── Services/                           ← Business logic (you create this folder)
│       ├── SaleService.php
│       ├── StockService.php
│       └── ReportService.php
│
├── database/
│   ├── migrations/                         ← One file per table
│   └── seeders/                            ← Sample/test data
│       ├── DatabaseSeeder.php              ← ALREADY EXISTS — edit to call yours
│       ├── RoleSeeder.php                  ← create
│       ├── CategorySeeder.php              ← create
│       ├── SupplierSeeder.php              ← create
│       ├── ProductSeeder.php               ← create
│       └── CustomerSeeder.php              ← create
│
├── resources/
│   ├── css/
│   │   └── app.css                         ← ALREADY EXISTS — add colors + fonts here
│   └── js/
│       ├── app.tsx                         ← ALREADY EXISTS — do not touch
│       ├── components/
│       │   ├── ui/                         ← ALREADY EXISTS — shadcn, never edit manually
│       │   ├── app-sidebar.tsx             ← EDIT — update with Sabr branding
│       │   ├── kpi-card.tsx                ← create (Stage 8)
│       │   ├── data-table.tsx              ← create (Stage 3)
│       │   ├── payment-toggle.tsx          ← create (Stage 5)
│       │   └── receipt-printer.tsx         ← create (Stage 5)
│       ├── layouts/
│       │   └── app-layout.tsx              ← ALREADY EXISTS — update with sidebar
│       ├── pages/                          ← One folder per module
│       │   ├── dashboard.tsx               ← ALREADY EXISTS — replace content
│       │   ├── pos/
│       │   │   └── index.tsx               ← create
│       │   ├── products/
│       │   │   ├── index.tsx               ← create
│       │   │   ├── create.tsx              ← create
│       │   │   └── edit.tsx                ← create
│       │   ├── sales/
│       │   │   ├── index.tsx               ← create
│       │   │   └── show.tsx                ← create
│       │   ├── customers/
│       │   │   ├── index.tsx               ← create
│       │   │   └── create.tsx              ← create
│       │   ├── suppliers/
│       │   │   ├── index.tsx               ← create
│       │   │   └── create.tsx              ← create
│       │   ├── expenses/
│       │   │   ├── index.tsx               ← create
│       │   │   └── create.tsx              ← create
│       │   └── reports/
│       │       └── index.tsx               ← create
│       ├── hooks/
│       │   ├── use-appearance.tsx          ← ALREADY EXISTS — change default to 'light'
│       │   └── use-cart.ts                 ← create (Stage 5, Zustand POS store)
│       └── lib/
│           ├── utils.ts                    ← ALREADY EXISTS — keep the cn() helper
│           └── constants.ts               ← create (Stage 1 — formatGHS, app config)
│
├── routes/
│   └── web.php                             ← ALREADY EXISTS — add your routes here
│
└── resources/views/pdf/                    ← create this folder in Stage 6
    ├── receipt.blade.php
    ├── daily-close.blade.php
    └── monthly-pl.blade.php
```

**Key rule:** Check if a file already exists before creating it. The starter kit gives you `app-sidebar.tsx`, `app-layout.tsx`, `dashboard.tsx`, and the auth pages. Edit those. Don't create duplicates.

---

## PART 6 — DATABASE DESIGN

Every table, every column, every relationship. Run migrations in this exact order.

### Table: users (already exists)
```
id, name, email, password, remember_token, created_at, updated_at
```
You will only add the `HasRoles` trait to the model in Stage 2. Do not touch the migration.

### Table: categories
```
id
name            string        required  — "Oil Filters", "Brake Parts"
description     text          nullable
created_at, updated_at
```

### Table: suppliers
```
id
name            string        required
contact_person  string        nullable
phone           string        nullable
email           string        nullable
address         text          nullable
notes           text          nullable
created_at, updated_at
```

### Table: products
```
id
name            string        required  — "Oil Filter — Toyota Corolla"
description     text          nullable
part_number     string        nullable  — manufacturer's reference number
category_id     foreignId     → categories
supplier_id     foreignId     → suppliers (nullable)
unit            string        required  — piece, litre, set, pair, box
cost_price      decimal(10,2) required  — cost price (what Sabr paid)
selling_price   decimal(10,2) required  — selling price (customer pays)
quantity        integer       required  default 0
reorder_level   integer       required  default 5 — alert when stock hits this
image           string        nullable  — stored file path
is_active       boolean       default true
created_at, updated_at
```

### Table: customers
```
id
name            string        required
phone           string        nullable
email           string        nullable
address         text          nullable
notes           text          nullable
created_at, updated_at
```

### Table: sales
```
id
sale_number     string        unique    — SAL-2026-00001
customer_id     foreignId     → customers (nullable — walk-in customer)
user_id         foreignId     → users   — the cashier who processed it
payment_method  enum          cash | momo
momo_reference  string        nullable  — MoMo transaction reference number
subtotal        decimal(10,2) required
discount        decimal(10,2) default 0
total           decimal(10,2) required
amount_tendered decimal(10,2) nullable  — cash given by customer
change_given    decimal(10,2) nullable  — change returned
status          enum          completed | refunded   default: completed
notes           text          nullable
created_at, updated_at
```

### Table: sale_items
```
id
sale_id         foreignId     → sales (cascade on delete)
product_id      foreignId     → products
quantity        integer       required
unit_price      decimal(10,2) required  — price AT TIME OF SALE (not current)
line_total      decimal(10,2) required  — quantity × unit_price
created_at, updated_at
```

### Table: expenses
```
id
title           string        required  — "May Rent", "Generator fuel"
amount          decimal(10,2) required
category        enum          rent | utilities | transport | salaries | stock | maintenance | other
payment_method  enum          cash | momo
expense_date    date          required
notes           text          nullable
user_id         foreignId     → users
created_at, updated_at
```

### Table: purchase_orders
```
id
supplier_id     foreignId     → suppliers
status          enum          draft | sent | received | cancelled   default: draft
expected_date   date          nullable
notes           text          nullable
user_id         foreignId     → users
created_at, updated_at
```

### Relationships Summary
```
Category      → has many Products
Supplier      → has many Products
Supplier      → has many PurchaseOrders
Product       → belongs to Category
Product       → belongs to Supplier (nullable)
Product       → has many SaleItems
Customer      → has many Sales
User          → has many Sales (as cashier)
Sale          → belongs to Customer (nullable)
Sale          → belongs to User
Sale          → has many SaleItems
SaleItem      → belongs to Sale
SaleItem      → belongs to Product
Expense       → belongs to User
PurchaseOrder → belongs to Supplier
PurchaseOrder → belongs to User
```

---

## PART 7 — CODING PATTERNS

These are the standard patterns used throughout this project. Learn each one once. Apply it everywhere.

### Pattern 1: Thin Controller → Service → Model

Controllers only receive requests and return responses. Business logic (calculations, multi-step writes, stock changes) lives in Service classes where it can be tested and reused.

```php
// app/Http/Controllers/SaleController.php
// CORRECT — thin controller, delegates to service
class SaleController extends Controller
{
    public function store(StoreSaleRequest $request, SaleService $saleService)
    {
        $sale = $saleService->completeSale(
            $request->validated(),
            auth()->user()
        );

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Sale #' . $sale->sale_number . ' completed.');
    }
}
```

```php
// app/Services/SaleService.php
// Business logic lives here — testable, reusable
class SaleService
{
    public function completeSale(array $data, User $cashier): Sale
    {
        // DB::transaction: if ANYTHING fails, NOTHING saves.
        // Either the whole sale completes, or zero changes are made.
        return DB::transaction(function () use ($data, $cashier) {

            $sale = Sale::create([
                'sale_number'    => $this->generateSaleNumber(),
                'customer_id'    => $data['customer_id'] ?? null,
                'user_id'        => $cashier->id,
                'payment_method' => $data['payment_method'],
                'subtotal'       => $data['subtotal'],
                'total'          => $data['total'],
                'status'         => 'completed',
            ]);

            foreach ($data['items'] as $item) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);

                // Reduce stock by the quantity sold
                Product::find($item['product_id'])
                    ->decrement('quantity', $item['quantity']);
            }

            return $sale;
        });
    }

    private function generateSaleNumber(): string
    {
        $count = Sale::whereYear('created_at', now()->year)->count() + 1;
        return 'SAL-' . now()->year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
        // Result: SAL-2026-00001
    }
}
```

### Pattern 2: Form Requests for Validation

Never validate inside a controller. Always use a Form Request class. Laravel automatically returns validation errors back to Inertia/React.

```php
// Generate: php artisan make:request StoreProductRequest
// app/Http/Requests/StoreProductRequest.php

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // permission checks happen on the route via middleware
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'selling_price'    => ['required', 'numeric', 'min:0'],
            'cost_price'    => ['required', 'numeric', 'min:0'],
            'quantity'      => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'unit'          => ['required', 'in:piece,litre,set,pair,box'],
            'category_id'   => ['required', 'exists:categories,id'],
        ];
    }
}
```

### Pattern 3: Inertia::render in Controllers

```php
use Inertia\Inertia;

public function index()
{
    // with('category') = eager load — avoids making one DB query per product
    $products = Product::with('category')
        ->where('is_active', true)
        ->orderBy('name')
        ->paginate(20); // 20 per page

    return Inertia::render('products/index', [
        'products' => $products,
        // The key 'products' becomes the prop name in your React component
    ]);
}
```

### Pattern 4: TypeScript Interfaces + Inertia Props in React

```tsx
// resources/js/pages/products/index.tsx

// Step 1: Define what your data looks like
interface Category { id: number; name: string; }

interface Product {
    id: number;
    name: string;
    part_number: string | null;
    selling_price: number;
    cost_price: number;
    quantity: number;
    reorder_level: number;
    category: Category;
    is_active: boolean;
}

interface PaginatedProducts {
    data: Product[];
    total: number;
    current_page: number;
    last_page: number;
}

interface Props {
    products: PaginatedProducts;
}

// Step 2: Receive props — TypeScript checks they match the interface
export default function ProductsIndex({ products }: Props) {
    return (
        <AppLayout title="Products">
            {products.data.map(product => (
                <div key={product.id}>
                    {product.name} — {formatGHS(product.selling_price)}
                </div>
            ))}
        </AppLayout>
    );
}
```

### Pattern 5: Inertia useForm for Create/Edit Forms

```tsx
import { useForm } from '@inertiajs/react';

export default function CreateProduct() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        selling_price: '',
        cost_price: '',
        quantity: '',
        category_id: '',
        unit: 'piece',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/products');
        // On success: Laravel redirects → Inertia follows automatically
        // On validation failure: errors object is populated automatically
    }

    return (
        <form onSubmit={handleSubmit}>
            <input
                value={data.name}
                onChange={e => setData('name', e.target.value)}
                className="border rounded px-3 py-2 w-full"
            />
            {errors.name && (
                <p className="text-red-500 text-sm mt-1">{errors.name}</p>
            )}

            <button
                type="submit"
                disabled={processing}
                className="bg-sabr-red text-white px-6 py-2 rounded-lg disabled:opacity-50"
            >
                {processing ? 'Saving...' : 'Save Product'}
            </button>
        </form>
    );
}
```

### Pattern 6: cn() for Conditional Tailwind Classes

```tsx
import { cn } from '@/lib/utils'; // already in your project

<button
    className={cn(
        // Base classes — always applied
        'px-4 py-2 rounded-lg font-semibold transition-colors',
        // Conditional classes
        isActive
            ? 'bg-sabr-red text-white'
            : 'bg-transparent text-gray-600 hover:bg-gray-100'
    )}
>
    Click me
</button>
```

### Pattern 7: Currency Formatting — Always Use This

```typescript
// resources/js/lib/constants.ts
export const formatGHS = (amount: number): string => {
    return new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
        minimumFractionDigits: 2,
    }).format(amount);
};
// formatGHS(4820)   → "GH₵ 4,820.00"
// formatGHS(126.5)  → "GH₵ 126.50"

export const APP_NAME = 'Sabr 89';
export const SHOP_PHONE = '0537 202641 / 0559 133733';
export const SHOP_LOCATION = 'Kumasi, Ghana';
```

---

## PART 8 — MODULE SPECIFICATIONS

### 8.1 Dashboard

**8 KPI Cards — 2 rows of 4:**

| Card | Color Class | Query |
|---|---|---|
| Today's Sales | bg-sabr-red | SUM(total) WHERE date = today AND status = completed |
| Transactions | bg-sabr-gold | COUNT WHERE date = today AND status = completed |
| Total Products | bg-sabr-black | COUNT WHERE is_active = true |
| Customers | bg-sabr-teal | COUNT all customers |
| Monthly Revenue | bg-sabr-blue | SUM(total) WHERE month = current AND status = completed |
| Pending Orders | bg-sabr-purple | COUNT WHERE status = sent |
| Expenses (Month) | bg-sabr-coral | SUM(amount) WHERE month = current |
| Gross Profit | bg-sabr-green | Monthly Revenue minus Monthly Expenses |

**Below the cards:**
- Weekly bar chart — rust red for weekdays, gold for weekends (Recharts)
- Top 5 selling products this week (simple table)
- Recent 10 transactions with customer name, amount, payment method icon
- Low-stock alerts — products at or below reorder level with progress bars
- Cash vs MoMo payment split for today

### 8.2 Point of Sale (POS)

A cashier must complete a full sale in under 30 seconds.

**Two-panel layout:**

LEFT PANEL (60% width):
- Large search input — autofocused on load
- Searches by: name or part number
- Results as clickable product cards — click to add to cart
- Cart items below: name, quantity stepper (- qty +), unit price, line total, × remove
- Subtotal at the bottom of the panel

RIGHT PANEL (40% width):
- Customer selector — searchable, defaults to "Walk-in Customer"
- Payment toggle: CASH (dark, Banknote icon) | MOMO (teal, Smartphone icon)
  - Selected = solid filled; unselected = outlined. Always one selected.
- If CASH: "Amount Tendered" input → change calculated automatically
- If MOMO: optional "MoMo Reference" text input
- CHARGE button — large, full width, sabr-red, disabled when cart is empty

**Keyboard shortcuts:**
```
/         → Focus the product search input
F2        → Clear cart / start new sale
F8        → Process charge (only if cart has items)
Escape    → Cancel current action
```

**After successful sale:**
- Receipt prints automatically
- Success toast notification
- Cart clears, page resets for next customer

### 8.3 Products

Table columns: name + part number, category, stock qty (red if low), unit, sell price, status badge, edit/delete actions.

Create/Edit form fields:
- Name (required), Part Number
- Category (Select), Supplier (Select)
- Unit (Select: piece / litre / set / pair / box)
- Cost Price + Selling Price (side by side, GHS)
- Current Stock Quantity, Reorder Level
- Description (textarea)
- Active toggle

Rows where `quantity <= reorder_level` get a red left border to catch Sabr's eye.

### 8.4 Sales History

Table: receipt number, date/time, customer (or Walk-in), cashier, payment badge, total, status.

Filters: date range (from/to), payment method.

Sale detail page:
- Full receipt layout with all items, quantities, prices
- Payment method and MoMo reference if applicable
- Cashier name and timestamp
- Download PDF button
- Process Refund button (Admin only) — restores stock, marks sale as refunded

### 8.5 Customers

Table: name, phone, email, date added. Search by name or phone.

Customer profile page shows their full purchase history.

### 8.6 Suppliers

Table: name, contact person, phone, email. Each supplier page shows their purchase orders.

### 8.7 Expenses

Monthly total shown prominently at the top. Table: title, category badge, amount, payment method, date.

Filters: category, month.

Add form: title, amount, category, payment method (Cash/MoMo), date, notes.

### 8.8 Reports

- **Daily Closing Report** — total sales, cash received, MoMo received, top 5 parts, cashier breakdown → PDF download
- **Monthly P&L** — revenue, expenses, gross profit, margin % → PDF download
- **Stock Valuation** — total inventory value at cost price → PDF download
- **Excel Import** — Sabr uploads existing stock list as .xlsx to bulk-create products

---

## PART 9 — BUILD STAGES

Build in exactly this order. Each stage has clear numbered steps and a done check at the end. Do not start the next stage until the current one passes its done check.

---

### STAGE 0 — Confirm Your Environment
**Goal:** Verify everything is working before writing a single line of your own code.
**You will learn:** How the development workflow operates.

```
Step 0.1  Open your project in VS Code:
          File → Open Folder → C:\Users\Admin\Herd\sabr-89

Step 0.2  Open the integrated terminal:
          Shortcut: Ctrl + ` (the backtick key, top-left of keyboard)

Step 0.3  Open a SECOND terminal tab:
          Click the + icon in the terminal panel
          You now have Terminal 1 and Terminal 2

Step 0.4  Terminal 1 — start the dev server:
          npm run dev
          Leave this running. You will see "VITE ready" when it's up.
          This watches your files and reloads the browser automatically.
          NEVER close this terminal while developing.

Step 0.5  Terminal 2 — run database migrations:
          php artisan migrate
          You will see a list of tables being created.
          This sets up your database from the migration files that came with the kit.

Step 0.6  Visit your app:
          http://sabr-89.test
          You should see the default authentication page.

Step 0.7  Register your admin account:
          Click Register, fill in:
          Name:     Sabr Admin
          Email:    admin@Sabr.com
          Password: (choose something memorable, write it down)

Step 0.8  After registering you land on /dashboard
          The default starter kit dashboard is visible. It looks basic. That is fine.
          You will replace it in Stage 1.

Step 0.9  Explore the existing files (do not edit yet):
          Open these files in VS Code and just read them:
          - routes/web.php          ← all URLs are defined here
          - resources/js/pages/dashboard.tsx    ← the page you just saw
          - resources/js/layouts/app-layout.tsx ← the wrapper around pages
          - resources/js/components/app-sidebar.tsx ← the sidebar
          Understanding what is already there prevents you from duplicating it.
```

**Done check:** You can log in and see the dashboard at `http://sabr-89.test/dashboard`.

```bash
git add .
git commit -m "chore: stage 0 complete — environment confirmed working"
```

---

### STAGE 1 — Design System & App Layout
**Goal:** Apply the Sabr brand to the existing layout. Set light mode as default.
**You will learn:** Tailwind v4 custom colors, how React layout components work, editing existing starter kit files.

**Important:** You are EDITING existing files in this stage. Not creating from scratch.

```
Step 1.1  SET LIGHT MODE AS DEFAULT
          Open: resources/js/hooks/use-appearance.tsx
          This file already exists. It controls the light/dark mode.

          Find the line that sets the initial appearance value.
          It will look something like:
          const [appearance, setAppearance] = useLocalStorage('appearance', 'system');

          Change 'system' to 'light':
          const [appearance, setAppearance] = useLocalStorage('appearance', 'light');

          WHY: Shop computers should always start in light mode.
          Dark mode stays available as a user toggle — you are not removing it.

Step 1.2  ADD BRAND COLORS TO TAILWIND
          Open: resources/css/app.css
          Find the @theme { } block. If it doesn't exist, add it after the @import line.

          Inside @theme { }, add your Sabr brand colors:

          @theme {
            --color-sabr-red:    #C8410A;
            --color-sabr-gold:   #F5C842;
            --color-sabr-black:  #111111;
            --color-sabr-teal:   #0F6E56;
            --color-sabr-blue:   #185FA5;
            --color-sabr-purple: #534AB7;
            --color-sabr-coral:  #D85A30;
            --color-sabr-green:  #3B6D11;
          }

          After saving, you can use bg-sabr-red, text-sabr-gold etc. anywhere.
          WHY: Defining colors once means you change them in one place if needed.

Step 1.3  UPDATE FONTS
          Open: vite.config.ts
          Find the fonts array. It currently loads a default font.
          Replace it with:

          fonts: [
              bunny('Syne', { weights: [600, 700, 800] }),
              bunny('DM Sans', { weights: [400, 500, 600] }),
          ],

          Then in app.css inside @theme { }, add:
          --font-sans:    'DM Sans', sans-serif;
          --font-display: 'Syne', sans-serif;

          WHY: Bunny Fonts is already wired into your Vite setup.
          You are just telling it which fonts to load.
          Syne for headings/logo. DM Sans for body text.

Step 1.4  CREATE THE CONSTANTS FILE
          Create: resources/js/lib/constants.ts
          (The lib/ folder already exists with utils.ts — add constants.ts alongside it)

          Add the formatGHS function from Part 7 Pattern 7.
          Add APP_NAME, SHOP_PHONE, SHOP_LOCATION.

          This file is imported throughout the project wherever you need GHS formatting.

Step 1.5  UPDATE THE SIDEBAR (edit existing file — do not create a new one)
          Open: resources/js/components/app-sidebar.tsx
          This file already exists from the starter kit. Read it first.

          Update it to include:
          - Logo area: use the logo in the [public folder]
          - Navigation groups with labels:
              MAIN: Dashboard, Point of Sale, Products, Sales
              PEOPLE: Customers, Suppliers
              FINANCE: Expenses, Reports
          - Each item: Lucide icon (left) + label text
          - Active state: bg-sabr-red text-white
          - Inactive state: text-gray-400 hover:bg-white/5
          - Settings link at the very bottom using mt-auto

          Import icons from 'lucide-react':
          LayoutDashboard, ShoppingCart, Package, Receipt,
          Users, Truck, CreditCard, BarChart3, Settings

          Use usePage() from '@inertiajs/react' to detect the current URL:
          const { url } = usePage();
          Then check if each nav item's href matches the current URL for the active state.

          Use <Link> from '@inertiajs/react' for all navigation links.
          NEVER use <a href="..."> for internal navigation — it causes full page reloads.

Step 1.6  UPDATE THE APP LAYOUT (edit existing file)
          Open: resources/js/layouts/app-layout.tsx
          This file already exists. Read it first.

          The layout should produce this structure:
          <div className="flex h-screen bg-[#F7F6F3]">
            <AppSidebar />                      ← 240px fixed left
            <div className="flex-1 flex flex-col overflow-hidden">
              <header>                          ← top bar
                Page title (prop)
                Current date (use date-fns)
                User avatar with initials
              </header>
              <main className="flex-1 overflow-auto p-6">
                {children}
              </main>
            </div>
          </div>

          Install date-fns now (you need it for the date display):
          npm install date-fns

          In the top bar, show:
          - Page title using the font-display class (Syne font)
          - Current date: import { format } from 'date-fns'
            then: format(new Date(), 'EEEE, dd MMM yyyy')
            Result: "Tuesday, 13 May 2026"
          - User avatar: a circle with the user's initials
            Get the user from: const { auth } = usePage().props as any;
            Initials: auth.user.name.split(' ').map(n => n[0]).join('').slice(0,2)

Step 1.7  UPDATE THE DASHBOARD PAGE (edit existing file)
          Open: resources/js/pages/dashboard.tsx
          This file already exists. Replace its content with:

          import AppLayout from '@/layouts/app-layout';

          export default function Dashboard() {
              return (
                  <AppLayout title="Dashboard">
                      <p className="text-gray-500">
                          Dashboard analytics coming in Stage 8.
                      </p>
                  </AppLayout>
              );
          }

          WHY: Every page wraps itself in AppLayout.
          The layout handles the sidebar and top bar automatically.
          You only write the main content area inside AppLayout.

Step 1.8  TEST YOUR WORK
          Save all files. Check npm run dev is still running in Terminal 1.
          Visit http://sabr-89.test/dashboard

          You should see:
          ✓ Black sidebar on the left with Sabr in gold
          ✓ Warm off-white page background (#F7F6F3)
          ✓ Top bar with "Dashboard" title and your avatar
          ✓ All nav items with Lucide icons
          ✓ Dashboard nav item highlighted in red (active state)
          ✓ Light mode by default (not dark)
```

**Done check:** App looks like the Sabr brand. #faf8f8 sidebar, gold logo, warm white background, correct fonts. Light mode is the default. Every authenticated page uses the updated layout.

```bash
git add .
git commit -m "feat: Sabr design system, brand colors, sidebar, and app layout"
```

---

### STAGE 2 — Roles & Permissions
**Goal:** Control who can access what before building any features.
**You will learn:** Installing Composer packages, running package migrations, adding traits to Models, the Tinker REPL for running code without building a UI.

```
Step 2.1  INSTALL SPATIE PERMISSION
          In Terminal 2:
          composer require spatie/laravel-permission

          WHY: This package adds a complete roles and permissions system to Laravel.
          Without it, every logged-in user can access everything.

Step 2.2  PUBLISH THE CONFIG AND MIGRATE
          php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
          php artisan migrate

          "Publish" copies the package's config file into your project so you can customise it.
          "Migrate" creates 5 new database tables for roles, permissions, and their assignments.

Step 2.3  ADD HasRoles TRAIT TO USER MODEL
          Open: app/Models/User.php
          This file already exists. Add one import and one trait:

          At the top: use Spatie\Permission\Traits\HasRoles;
          Inside the class, add HasRoles to the existing use statement.

          WHY: A PHP trait adds methods to a class without inheritance.
          HasRoles gives every User object methods like:
          $user->assignRole('Admin')
          $user->hasRole('Cashier')
          $user->getRoleNames()

Step 2.4  CREATE THE ROLE SEEDER
          php artisan make:seeder RoleSeeder

          Open: database/seeders/RoleSeeder.php
          In the run() method, add:

          use Spatie\Permission\Models\Role;

          Role::create(['name' => 'Admin']);
          Role::create(['name' => 'Cashier']);
          Role::create(['name' => 'Inventory Manager']);
          Role::create(['name' => 'Report Viewer']);

Step 2.5  RUN THE SEEDER
          php artisan db:seed --class=RoleSeeder

Step 2.6  ASSIGN YOURSELF THE ADMIN ROLE
          php artisan tinker

          At the >>> prompt:
          >>> User::where('email', 'admin@Sabr.com')->first()->assignRole('Admin')
          >>> exit

          WHY: Tinker is an interactive PHP console — you can run any Laravel code
          directly here without building a UI. Very useful for quick one-time tasks.

Step 2.7  VERIFY IT WORKED
          php artisan tinker
          >>> User::where('email', 'admin@Sabr.com')->first()->getRoleNames()
          Should output: Illuminate\Support\Collection {#... all: ["Admin"]}
          >>> exit

Step 2.8  INSTALL ACTIVITY LOG
          composer require spatie/laravel-activitylog
          php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
          php artisan migrate

          WHY: Every important action — a sale, a stock change, a refund —
          gets recorded with who did it and when. If something goes wrong in the shop,
          you can trace exactly who did what and when. Essential for a multi-user system.
```

**Done check:** Tinker confirms your user has the Admin role. `php artisan migrate` shows no errors.

```bash
git add .
git commit -m "feat: roles, permissions, and activity logging installed"
```

---

### STAGE 3 — Products & Inventory
**Goal:** Sabr can add, view, search, and edit every auto part in his catalogue.
**You will learn:** Creating migrations, Models, Eloquent relationships, resource controllers, Form Requests, TanStack Table, Inertia forms.

```
Step 3.1  CREATE CATEGORIES MIGRATION
          php artisan make:migration create_categories_table

          Open the new file in database/migrations/.
          Inside the up() method, in Schema::create():

          $table->id();
          $table->string('name');
          $table->text('description')->nullable();
          $table->timestamps();

          php artisan migrate

          WHY: Migrations are version control for your database.
          Each file records exactly what changed. Anyone can recreate
          the database from scratch using these files.

Step 3.2  CREATE SUPPLIERS MIGRATION
          php artisan make:migration create_suppliers_table
          Add all supplier fields from Part 6.
          php artisan migrate

Step 3.3  CREATE PRODUCTS MIGRATION
          php artisan make:migration create_products_table
          Add all product fields from Part 6.

          For foreign keys use these Laravel helpers:
          $table->foreignId('category_id')->constrained()->restrictOnDelete();
          $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();

          php artisan migrate

Step 3.4  CREATE THE MODELS
          php artisan make:model Category
          php artisan make:model Supplier
          php artisan make:model Product

          In each model you need to add THREE things:

          1. protected $fillable = [...] — list all field names
             WHY: Security whitelist. Only these fields can be mass-assigned.
             Without this, a malicious user could inject unexpected data.

          2. Relationships:
             Category:  public function products() { return $this->hasMany(Product::class); }
             Supplier:  public function products() { return $this->hasMany(Product::class); }
             Product:   public function category() { return $this->belongsTo(Category::class); }
                        public function supplier() { return $this->belongsTo(Supplier::class); }

          3. In Product model, add type casts:
             protected $casts = [
                 'cost_price'  => 'decimal:2',
                 'selling_price' => 'decimal:2',
                 'is_active'  => 'boolean',
             ];

Step 3.5  CREATE THE CONTROLLERS
          php artisan make:controller ProductController --resource
          php artisan make:controller CategoryController --resource

          --resource generates 7 methods automatically:
          index, create, store, show, edit, update, destroy
          You implement the ones you need, delete the ones you don't.

Step 3.6  CREATE FORM REQUEST VALIDATORS
          php artisan make:request StoreProductRequest
          php artisan make:request UpdateProductRequest
          Add validation rules following Pattern 2 in Part 7.

Step 3.7  ADD ROUTES
          Open: routes/web.php
          Inside the existing auth middleware group, add:

          Route::resource('products', ProductController::class);
          Route::resource('categories', CategoryController::class);

          Route::resource() generates all 7 CRUD routes with one line.
          Verify them: php artisan route:list | grep product

Step 3.8  IMPLEMENT ProductController@index
          $products = Product::with('category')->orderBy('name')->paginate(20);
          return Inertia::render('products/index', ['products' => $products]);

Step 3.9  IMPLEMENT ProductController@create
          $categories = Category::orderBy('name')->get();
          $suppliers = Supplier::orderBy('name')->get();
          return Inertia::render('products/create', compact('categories', 'suppliers'));

Step 3.10 IMPLEMENT ProductController@store
          Accept StoreProductRequest.
          Product::create($request->validated());
          Redirect with success flash message.

Step 3.11 IMPLEMENT ProductController@edit AND @update
          Same pattern as create/store but pre-populate with existing product data.
          Update uses patch() in the React form.

Step 3.12 INSTALL TANSTACK TABLE
          npm install @tanstack/react-table

          WHY: A headless table library. "Headless" means it handles all the sorting,
          filtering and pagination logic but you control the HTML/styling.
          This gives you professional tables that look exactly how you design them.

Step 3.13 CREATE THE REUSABLE DataTable COMPONENT
          Create: resources/js/components/data-table.tsx

          WHY creating this: Products, Sales, Customers, Suppliers, Expenses all need
          tables with the same features (search, sort, paginate). Build it once,
          use it everywhere. This is the DRY principle (Don't Repeat Yourself).

          This component accepts:
          - columns: ColumnDef[] — defines what each column shows
          - data: TData[] — the actual row data
          - Optional: searchable column key, filter options

          Internally uses useReactTable from @tanstack/react-table.
          Features: sortable columns (click header), search input, pagination.

Step 3.14 BUILD products/index.tsx PAGE
          Create: resources/js/pages/products/index.tsx
          Wrap in <AppLayout title="Products">

          Define columns array for DataTable:
          - Name cell: product name in bold, part number below in muted gray
          - Category: category.name
          - Brand: brand or "—" if null
          - Stock: quantity (red text + red bg if quantity <= reorder_level)
          - Price: formatGHS(selling_price)
          - Status: green badge if is_active, gray badge if not
          - Actions: Edit link, Delete button with confirmation

          Pass columns and products.data to <DataTable />.
          Add pagination controls below using products.current_page and products.last_page.
          Add "Add Product" button in the top-right that links to /products/create.

Step 3.15 BUILD products/create.tsx PAGE
          Create: resources/js/pages/products/create.tsx
          Use useForm from '@inertiajs/react' (see Pattern 5 in Part 7).
          All form fields with labels, inputs, and error messages below each field.
          Use shadcn Select for unit and category dropdowns.
          Submit: post('/products')

Step 3.16 BUILD products/edit.tsx PAGE
          Create: resources/js/pages/products/edit.tsx
          Same as create.tsx but:
          - Pre-populate useForm with the existing product data (passed as a prop)
          - Submit uses: patch(route('products.update', product.id))

Step 3.17 SEED SAMPLE DATA
          php artisan make:seeder CategorySeeder
          php artisan make:seeder SupplierSeeder
          php artisan make:seeder ProductSeeder

          In CategorySeeder, add realistic Ghanaian auto parts categories:
          Oil & Fluids, Filters, Brake System, Electrical, Engine Parts,
          Suspension & Steering, Tyres & Wheels, Body Parts, Accessories

          In ProductSeeder, add 25+ realistic products, for example:
          - "Oil Filter — Toyota Corolla", buy: 12, sell: 20, qty: 45
          - "Brake Pads (Front) — Nissan Almera", buy: 55, sell: 90, qty: 12
          - "ATF Fluid 1 Litre — Honda", buy: 18, sell: 30, qty: 30
          - "Spark Plug Set x4 — Bosch Universal", buy: 25, sell: 45, qty: 8
          - "Car Battery 55AH — Leoch", buy: 280, sell: 420, qty: 6
          - Include some products where quantity <= reorder_level to test alerts

          In DatabaseSeeder.php (already exists), add your seeders to the run() method:
          $this->call([
              RoleSeeder::class,
              CategorySeeder::class,
              SupplierSeeder::class,
              ProductSeeder::class,
          ]);

          php artisan db:seed
```

**Done check:** Products table shows with sorting and search. You can add, edit, and view products. Low-stock rows have a visible red left border. Pagination works.

```bash
git add .
git commit -m "feat: products module — migrations, models, CRUD, data table"
```

---

### STAGE 4 — Customers & Suppliers
**Goal:** Manage the people Sabr buys from and sells to.
**You will learn:** You now apply Stage 3's patterns independently. Things get faster.

```
Step 4.1  CREATE customers MIGRATION AND MODEL
          php artisan make:migration create_customers_table
          php artisan make:model Customer
          Add all fields from Part 6. Migrate.

Step 4.2  CREATE CustomerController AND ROUTES
          php artisan make:controller CustomerController --resource
          php artisan make:request StoreCustomerRequest
          Validation: name required, phone/email optional, max lengths.
          Add Route::resource('customers', CustomerController::class) to web.php.

Step 4.3  IMPLEMENT CustomerController@index AND @store
          index(): paginate customers, return Inertia page
          store(): validate, create, redirect with success

Step 4.4  BUILD customers/index.tsx
          Table columns: name, phone, email, date added (use date-fns format).
          Search input that filters by name or phone.
          "Add Customer" button.

Step 4.5  BUILD customers/create.tsx
          Form: name (required), phone, email, address, notes.
          Name is the only required field — walk-ins often only share a name.

Step 4.6  BUILD THE SUPPLIERS MODULE
          The Supplier model and migration were already created in Stage 3 Step 3.2.
          Now build:
          - SupplierController --resource
          - StoreSupplierRequest
          - Route::resource('suppliers', SupplierController::class)
          - suppliers/index.tsx (table: name, contact person, phone, email)
          - suppliers/create.tsx (form: name, contact person, phone, email, address)

Step 4.7  SEED SAMPLE DATA
          php artisan make:seeder CustomerSeeder
          Add 15 sample customers with realistic Ghanaian names and phone numbers.
          Add CustomerSeeder to DatabaseSeeder and run: php artisan db:seed
```

**Done check:** You can add customers and suppliers and view them in searchable tables.

```bash
git add .
git commit -m "feat: customers and suppliers modules"
```

---

### STAGE 5 — Point of Sale (POS)
**Goal:** A cashier can complete a full sale with receipt printing in under 30 seconds.
**You will learn:** Zustand for global state, keyboard shortcuts in React, complex form submission, thermal receipt generation.

```
Step 5.1  INSTALL POS PACKAGES
          npm install zustand
          npm install react-hotkeys-hook
          npm install @react-thermal-printer/printer
          npm install react-to-print

Step 5.2  CREATE THE CART STORE (Zustand)
          Create: resources/js/hooks/use-cart.ts

          WHY Zustand: The POS screen has three separate panels — search results,
          cart list, and payment panel. All three need to read and update the same cart.
          Normally in React you would pass cart state down as props, which gets messy.
          Zustand creates a global store outside of React's component tree.
          Any component can read or update it directly.

          The store holds this state:
          interface CartItem {
              product_id: number;
              name: string;
              unit_price: number;
              quantity: number;
              line_total: number;
          }

          interface CartStore {
              items: CartItem[];
              customer_id: number | null;
              customer_name: string;
              payment_method: 'cash' | 'momo';
              momo_reference: string;
              amount_tendered: number;

              // Actions
              addItem: (product: Product) => void;
              removeItem: (product_id: number) => void;
              updateQuantity: (product_id: number, qty: number) => void;
              setCustomer: (id: number | null, name: string) => void;
              setPaymentMethod: (method: 'cash' | 'momo') => void;
              setMomoReference: (ref: string) => void;
              setAmountTendered: (amount: number) => void;
              clearCart: () => void;

              // Computed helpers
              getSubtotal: () => number;
              getChange: () => number;
          }

          Key logic for addItem:
          - If product already exists in items, increment quantity
          - If new, push a new CartItem
          - Always recalculate line_total = quantity × unit_price

Step 5.3  CREATE SALES TABLE MIGRATIONS
          php artisan make:migration create_sales_table
          php artisan make:migration create_sale_items_table
          Add all fields from Part 6 for both tables.
          php artisan migrate

Step 5.4  CREATE Sale AND SaleItem MODELS
          php artisan make:model Sale
          php artisan make:model SaleItem

          Sale model:
          - $fillable: all sale fields
          - $casts: total/subtotal as 'decimal:2', is active as 'boolean'
          - Relationships: belongsTo Customer, belongsTo User, hasMany SaleItems

          SaleItem model:
          - $fillable: all fields
          - $casts: unit_price, line_total as 'decimal:2'
          - Relationships: belongsTo Sale, belongsTo Product

Step 5.5  CREATE THE SERVICES FOLDER AND SaleService
          Create folder: app/Services/
          Create file: app/Services/SaleService.php

          Implement completeSale() and generateSaleNumber() from Part 7 Pattern 1.
          This is the most important file in the backend — read Pattern 1 carefully
          before writing a single line. DB::transaction() is critical here.

Step 5.6  CREATE PosController
          php artisan make:controller PosController

          Three methods:
          - index(): return Inertia page with active products + all customers
          - search(Request $request): search products by name/barcode/part_number,
            return response()->json($results) — this is used by the live search
          - store(StoreSaleRequest $request): call SaleService, return success

Step 5.7  ADD POS ROUTES
          Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
          Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
          Route::post('/pos', [PosController::class, 'store'])->name('pos.store');

Step 5.8  BUILD PaymentToggle COMPONENT
          Create: resources/js/components/payment-toggle.tsx

          Props: { value: 'cash' | 'momo', onChange: (v: 'cash' | 'momo') => void }

          Two large buttons side by side:
          - CASH: bg-sabr-black when active, outlined when not. Icon: Banknote
          - MOMO: bg-sabr-teal when active, outlined when not. Icon: Smartphone

          Use cn() for conditional classes.
          This component is reused in the Expenses form too.

Step 5.9  BUILD ReceiptPrinter COMPONENT
          Create: resources/js/components/receipt-printer.tsx

          This component renders the receipt HTML. It is hidden on screen (display: none)
          but when react-to-print triggers printing, only this component prints.

          Receipt structure:
          - Header: Sabr 89 (bold), location, phone numbers
          - Divider line (dashes)
          - Receipt number, date/time, cashier name
          - Divider
          - Each item: name, qty × unit price = line total
          - Divider
          - Subtotal, discount if any, TOTAL (larger)
          - Payment method
          - If cash: Amount Tendered and Change
          - If MoMo: "Ref: [reference]" if provided
          - Divider
          - "Thank you for your business!" footer

          Use JetBrains Mono font for the numbers: className="font-mono"
          (Add font-mono to your @theme in app.css after installing the font in vite.config.ts)

Step 5.10 BUILD pos/index.tsx PAGE
          Create: resources/js/pages/pos/index.tsx
          Wrap in <AppLayout title="Point of Sale">

          Read ALL cart state from Zustand:
          const { items, addItem, removeItem, updateQuantity, ... } = useCart();
          NEVER use useState for cart data — it must come from Zustand.

          LEFT PANEL (w-3/5):
          Search input:
          - ref={searchRef} for keyboard shortcut focus
          - Debounce 300ms on change (use setTimeout + clearTimeout)
          - On change: fetch('/pos/search?q=' + query) and store results in local state
          - Results displayed as a grid of product cards
          - Each card: product name, part number, stock qty, price
          - onClick: addItem(product) — disabled if product.quantity === 0

          Cart items below search:
          - Each row: product name, [−] qty [+] buttons, unit price, line total, [×]
          - Highlight the row if qty being added would exceed stock

          Subtotal at bottom of left panel.

          RIGHT PANEL (w-2/5):
          Customer combobox (use shadcn Popover + Command for searchable dropdown)
          Default display: "Walk-in Customer"

          <PaymentToggle value={payment_method} onChange={setPaymentMethod} />

          If payment_method === 'cash':
          - Input for amount_tendered
          - Calculated change shown below: Change: formatGHS(getChange())

          If payment_method === 'momo':
          - Optional text input for momo_reference
          - Label: "MoMo Reference (optional)"

          CHARGE button:
          - Full width, bg-sabr-red, large
          - disabled when items.length === 0 or form is submitting
          - Show keyboard hint below: "F8 to charge"

Step 5.11 ADD KEYBOARD SHORTCUTS
          At the top of pos/index.tsx:
          import { useHotkeys } from 'react-hotkeys-hook';

          useHotkeys('/', (e) => { e.preventDefault(); searchRef.current?.focus(); });
          useHotkeys('f2', () => clearCart());
          useHotkeys('f8', () => handleCharge(), { enabled: items.length > 0 });

Step 5.12 WIRE THE CHARGE BUTTON AND RECEIPT PRINT
          Install react-to-print was done in Step 5.1.

          Create a ref for the receipt component: const receiptRef = useRef(null);
          Use useReactToPrint({ contentRef: receiptRef }) to get a print() function.

          handleCharge function:
          router.post('/pos', {
              items: items,
              customer_id: customer_id,
              payment_method: payment_method,
              momo_reference: momo_reference,
              amount_tendered: amount_tendered,
              subtotal: getSubtotal(),
              total: getSubtotal(),
          }, {
              onSuccess: () => {
                  print();           // trigger receipt print
                  clearCart();       // reset cart
                  toast.success('Sale completed!'); // Sonner toast
              }
          });

          Render the receipt component but keep it invisible:
          <div className="hidden">
              <ReceiptPrinter ref={receiptRef} items={items} ... />
          </div>

Step 5.13 TEST THE COMPLETE POS FLOW
          - Search for a product → click to add to cart ✓
          - Adjust quantity with + / − ✓
          - Select CASH, enter amount tendered → change appears ✓
          - Press F8 or click CHARGE ✓
          - Receipt appears / triggers print dialog ✓
          - Cart clears automatically ✓
          - Check database: new row in sales, rows in sale_items ✓
          - Check product: quantity decremented by correct amount ✓
          - Test MoMo: reference saves with the sale ✓
```

**Done check:** Full sale completes in under 30 seconds. Receipt prints. Stock decrements. Sale records appear in the database with correct data.

```bash
git add .
git commit -m "feat: complete POS — cart store, payment toggle, receipt printing"
```

---

### STAGE 6 — Sales History & PDF Receipts
**Goal:** View every past transaction. Download PDF receipts. Process refunds.
**You will learn:** Laravel PDF generation (server-side), Blade views for PDF, refund logic in a Service.

```
Step 6.1  CREATE SaleController
          php artisan make:controller SaleController

Step 6.2  ADD ROUTES
          Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
          Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
          Route::get('sales/{sale}/pdf', [SaleController::class, 'pdf'])->name('sales.pdf');
          Route::post('sales/{sale}/refund', [SaleController::class, 'refund'])->name('sales.refund');

Step 6.3  IMPLEMENT SaleController@index
          $sales = Sale::with(['customer', 'user'])
              ->latest()
              ->paginate(20);
          return Inertia::render('sales/index', ['sales' => $sales]);

Step 6.4  IMPLEMENT SaleController@show
          $sale = Sale::with(['customer', 'user', 'items.product'])->findOrFail($id);
          return Inertia::render('sales/show', ['sale' => $sale]);

Step 6.5  BUILD sales/index.tsx
          Table: receipt number, date+time, customer name (or "Walk-in"), cashier,
          payment method badge (cash = dark gray pill, momo = teal pill), total, status badge.

          Date range filter: two date inputs (from / to) above the table.
          Payment method filter: dropdown (All / Cash / MoMo).

          Clicking any row navigates to sales.show using Inertia <Link>.

Step 6.6  BUILD sales/show.tsx
          Full receipt layout:
          - Header: shop name + receipt number
          - Customer name, cashier name, date/time
          - Items table: product name, qty, unit price, line total
          - Subtotal, discount, total
          - Payment method + MoMo reference if applicable
          - Two buttons: "Download PDF" and "Process Refund" (Admin only)

          For the Admin-only refund button, check the role:
          const { auth } = usePage().props;
          {auth.user.roles.includes('Admin') && <button>Process Refund</button>}

Step 6.7  INSTALL DOMPDF
          composer require barryvdh/laravel-dompdf
          php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"

          WHY: dompdf converts HTML to PDF on the server.
          You write a Blade template (HTML), dompdf renders it as a downloadable PDF.

Step 6.8  CREATE THE PDF BLADE VIEW
          Create folder: resources/views/pdf/
          Create file: resources/views/pdf/receipt.blade.php

          This is a Blade (PHP HTML template) file — NOT a React file.
          Use inline styles (dompdf does not support external CSS).

          Structure:
          - Shop header with name, address, phone
          - Receipt number, date, cashier
          - Items table with columns
          - Totals section
          - Payment details
          - Footer thank-you message

Step 6.9  IMPLEMENT SaleController@pdf
          use Barryvdh\DomPDF\Facade\Pdf;

          public function pdf(Sale $sale)
          {
              $sale->load(['customer', 'user', 'items.product']);
              $pdf = Pdf::loadView('pdf.receipt', compact('sale'));
              return $pdf->download('receipt-' . $sale->sale_number . '.pdf');
          }

Step 6.10 IMPLEMENT REFUND in SaleService
          Add a refund(Sale $sale): void method.

          Inside DB::transaction():
          1. Guard: if ($sale->status === 'refunded') throw new \Exception('Already refunded');
          2. foreach ($sale->items as $item) — restore stock:
             Product::find($item->product_id)->increment('quantity', $item->quantity);
          3. $sale->update(['status' => 'refunded']);
          4. Log the activity:
             activity()->causedBy(auth()->user())->log('Refund: ' . $sale->sale_number);
```

**Done check:** Sales table shows all transactions. PDF downloads with correct data. Refund marks the sale as refunded and restores stock quantities in the products table.

```bash
git add .
git commit -m "feat: sales history, PDF receipt download, and refund processing"
```

---

### STAGE 7 — Expenses
**Goal:** Sabr records and reviews all business costs.
**You will learn:** Applying all previous patterns independently — this stage moves faster.

```
Step 7.1  CREATE EXPENSES MIGRATION AND MODEL
          php artisan make:migration create_expenses_table
          php artisan make:model Expense
          Add all fields from Part 6. Migrate.

Step 7.2  CREATE ExpenseController, FORM REQUEST, AND ROUTES
          php artisan make:controller ExpenseController --resource
          php artisan make:request StoreExpenseRequest

          Validation rules:
          'title'          => ['required', 'string', 'max:255'],
          'amount'         => ['required', 'numeric', 'min:0.01'],
          'category'       => ['required', 'in:rent,utilities,transport,salaries,stock,maintenance,other'],
          'payment_method' => ['required', 'in:cash,momo'],
          'expense_date'   => ['required', 'date'],

          Add Route::resource('expenses', ExpenseController::class) to web.php.

Step 7.3  IMPLEMENT ExpenseController@index
          Show monthly total at top:
          $monthlyTotal = Expense::whereMonth('expense_date', now()->month)
                              ->whereYear('expense_date', now()->year)
                              ->sum('amount');

          Paginate expenses with filters for category and month.
          Return Inertia page with expenses and monthlyTotal.

Step 7.4  BUILD expenses/index.tsx
          At the top: large monthly total card in bg-sabr-coral.
          Filter row: category Select + month input.
          Table: title, category badge (color-coded), amount in GHS, payment method, date.
          "Add Expense" button.

Step 7.5  BUILD expenses/create.tsx
          Form fields using shadcn components:
          - Title: text input
          - Amount: number input with "GH₵" prefix label
          - Category: Select with all category options
          - Payment Method: reuse <PaymentToggle> component from Stage 5
          - Date: date input (default to today: new Date().toISOString().split('T')[0])
          - Notes: textarea (optional)
```

**Done check:** Expenses are added and visible. Monthly total updates correctly. Category filter and month filter work.

```bash
git add .
git commit -m "feat: expenses module with monthly total and category filtering"
```

---

### STAGE 8 — Dashboard Analytics
**Goal:** All 8 KPI cards show real live data. Charts visualise trends.
**You will learn:** Complex Eloquent queries, single-action controllers, Recharts charts, composing a page from many focused components.

```
Step 8.1  INSTALL RECHARTS
          npm install recharts

Step 8.2  CREATE DashboardController
          php artisan make:controller DashboardController --invokable

          WHY --invokable: When a controller only does one thing,
          use a single-action controller with __invoke() instead of index().
          Cleaner and more expressive.

          Update the dashboard route in web.php:
          Route::get('/dashboard', DashboardController::class)->name('dashboard');

Step 8.3  IMPLEMENT __invoke() WITH ALL QUERIES
          $today     = today();
          $thisMonth = now()->startOfMonth();

          return Inertia::render('dashboard', [
              'todaySalesTotal'    => Sale::whereDate('created_at', $today)
                                         ->where('status', 'completed')->sum('total'),
              'todayTransactions'  => Sale::whereDate('created_at', $today)
                                         ->where('status', 'completed')->count(),
              'totalProducts'      => Product::where('is_active', true)->count(),
              'totalCustomers'     => Customer::count(),
              'monthlyRevenue'     => Sale::where('created_at', '>=', $thisMonth)
                                         ->where('status', 'completed')->sum('total'),
              'monthlyExpenses'    => Expense::where('expense_date', '>=', $thisMonth)
                                         ->sum('amount'),
              'pendingOrders'      => PurchaseOrder::where('status', 'sent')->count(),
              'weeklySales'        => $this->getWeeklySales(),
              'topProducts'        => $this->getTopProducts(),
              'recentTransactions' => Sale::with('customer', 'user')
                                         ->latest()->limit(10)->get(),
              'lowStockAlerts'     => Product::with('category')
                                         ->whereColumn('quantity', '<=', 'reorder_level')
                                         ->orderBy('quantity')->limit(8)->get(),
              'paymentBreakdown'   => $this->getPaymentBreakdown(),
          ]);

          Implement private methods:
          getWeeklySales(): array — returns [{day: 'Mon', total: 1200}, ...] for the last 7 days
          getTopProducts(): array — top 5 products by quantity sold this week
          getPaymentBreakdown(): array — [{method: 'cash', total: X}, {method: 'momo', total: Y}]

Step 8.4  CREATE KpiCard COMPONENT
          Create: resources/js/components/kpi-card.tsx

          Props:
          {
              label:      string       — e.g. "Today's Sales"
              value:      string       — pre-formatted, e.g. "GH₵ 4,820.00" or "38"
              subtitle:   string       — e.g. "↑ 12% vs yesterday"
              icon:       LucideIcon   — the icon component itself (not a string)
              colorClass: string       — e.g. "bg-sabr-red"
              textClass?: string       — defaults to "text-white"
          }

          Structure:
          <div className={cn("relative rounded-xl p-5 overflow-hidden", colorClass, textClass)}>
              <Icon className="absolute right-4 top-4 opacity-20 w-10 h-10" />
              <p className="text-xs font-semibold uppercase tracking-wider opacity-80">{label}</p>
              <p className="text-3xl font-bold mt-1 font-display">{value}</p>
              <p className="text-xs opacity-75 mt-2">{subtitle}</p>
          </div>

Step 8.5  BUILD THE DASHBOARD PAGE (replace the placeholder from Stage 1)
          Open: resources/js/pages/dashboard.tsx
          Replace the placeholder content with the full dashboard.

          Layout inside <AppLayout title="Dashboard">:
          Row 1: grid grid-cols-4 gap-4 — first 4 KPI cards
          Row 2: grid grid-cols-4 gap-4 — last 4 KPI cards
          Row 3: grid grid-cols-3 gap-4 — WeeklySalesChart (spans 2 cols) + TopProducts
          Row 4: grid grid-cols-3 gap-4 — RecentTransactions + LowStockAlerts + PaymentBreakdown

Step 8.6  BUILD WeeklySalesChart COMPONENT
          Create: resources/js/components/weekly-sales-chart.tsx

          Use from recharts: BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, Cell

          Color logic for bars:
          const isWeekend = (day: string) => day === 'Sat' || day === 'Sun';
          <Cell fill={isWeekend(entry.day) ? '#F5C842' : '#C8410A'} />
          — weekdays in sabr-red, weekends in sabr-gold

          Tooltip: custom formatter that shows formatGHS(value)
          Minimal style: no grid lines, clean axes

Step 8.7  BUILD LowStockAlerts COMPONENT
          Create: resources/js/components/low-stock-alerts.tsx

          For each low-stock product show:
          - Product name (bold) + category name (muted)
          - A progress bar: width = (quantity / reorder_level) * 100 %
            Color: red. This shows how depleted the stock is.
          - Stock count in red text on the right: "3 left"

Step 8.8  BUILD PaymentBreakdown COMPONENT
          Create: resources/js/components/payment-breakdown.tsx

          Show today's cash vs MoMo totals.
          A horizontal bar split proportionally between the two:
          - Left section: sabr-black (Cash)
          - Right section: sabr-teal (MoMo)
          Below the bar: formatted GHS amounts and percentages for each method.
```

**Done check:** All 8 KPI cards show real data. Weekly chart updates when you make test sales. Low-stock alerts show products that need restocking. Payment breakdown reflects actual test transactions.

```bash
git add .
git commit -m "feat: live dashboard — KPI cards, weekly chart, analytics"
```

---

### STAGE 9 — Reports & Excel Import
**Goal:** Sabr generates daily and monthly reports as PDFs. Can bulk-import stock from Excel.
**You will learn:** Building report PDFs from Blade templates, Excel import with Maatwebsite.

```
Step 9.1  INSTALL EXCEL IMPORT PACKAGE
          composer require maatwebsite/excel
          php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config

Step 9.2  CREATE ReportController
          php artisan make:controller ReportController

          Methods:
          - index(): return Inertia reports/index page
          - dailyClose(Request): generate PDF for a given date
          - monthlyPL(Request): generate PDF for a given month+year
          - stockValuation(): generate current stock valuation PDF
          - importProducts(Request): accept Excel file, run import

Step 9.3  ADD REPORT ROUTES
          Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
          Route::get('reports/daily', [ReportController::class, 'dailyClose'])->name('reports.daily');
          Route::get('reports/monthly', [ReportController::class, 'monthlyPL'])->name('reports.monthly');
          Route::get('reports/stock', [ReportController::class, 'stockValuation'])->name('reports.stock');
          Route::post('reports/import', [ReportController::class, 'importProducts'])->name('reports.import');

Step 9.4  BUILD reports/index.tsx
          Three report cards in a grid, each with:
          - Report name and description
          - A date input (for daily/monthly reports)
          - Generate & Download button that links to the correct route with date params

Step 9.5  BUILD PDF BLADE TEMPLATES
          Create: resources/views/pdf/daily-close.blade.php
          - Date and shop name header
          - Total sales, cash received, MoMo received
          - Top 5 parts sold (name, qty, revenue)
          - Per-cashier breakdown (name, sales count, total)

          Create: resources/views/pdf/monthly-pl.blade.php
          - Month, year, shop name header
          - Total revenue
          - Total expenses (broken down by category)
          - Gross profit
          - Profit margin percentage
          - Note: dompdf does not support JavaScript, so charts are HTML tables

Step 9.6  BUILD EXCEL PRODUCT IMPORT
          Create: app/Imports/ProductsImport.php
          Implement Maatwebsite\Excel\Concerns\ToModel interface.

          Map Excel columns (by position) to product fields:
          Column A → name
          Column B → part_number
          Column C → selling_price
          Column D → cost_price
          Column E → quantity
          Column F → unit (default 'piece' if empty)

          Add an import form to the reports page:
          File upload input (accepts .xlsx, .xls)
          "Import Products" button
          On submission: Excel::import(new ProductsImport, $request->file('file'))
```

**Done check:** All three PDFs generate and download correctly. Excel import successfully creates products from a test spreadsheet.

```bash
git add .
git commit -m "feat: reports with PDF export and Excel product import"
```

---

### STAGE 10 — Polish & Production Ready
**Goal:** Every part of the system is solid, secure, and ready for daily use in the shop.

```
Step 10.1  TOAST NOTIFICATIONS ON EVERY ACTION
           Sonner is already installed in your project.
           Open: resources/js/layouts/app-layout.tsx (already exists)
           Add inside the layout: <Toaster richColors position="top-right" />
           Import from 'sonner': import { Toaster, toast } from 'sonner';

           Wire Laravel flash messages to Sonner.
           In app-layout.tsx:
           const { flash } = usePage().props as any;
           useEffect(() => {
               if (flash?.success) toast.success(flash.success);
               if (flash?.error) toast.error(flash.error);
           }, [flash]);

           In every controller that redirects, add a flash message:
           return redirect()->route('products.index')->with('success', 'Product added.');
           return redirect()->route('products.index')->with('error', 'Failed to delete.');

Step 10.2  LOADING STATES ON EVERY SUBMIT BUTTON
           Every submit button in every form must:
           - Show disabled={processing} when the Inertia form is submitting
           - Change text from "Save" to "Saving..." while processing
           - Show a small spinner icon next to the text

           Example using Lucide:
           import { Loader2 } from 'lucide-react';
           {processing && <Loader2 className="w-4 h-4 animate-spin mr-2" />}
           {processing ? 'Saving...' : 'Save Product'}

Step 10.3  ENFORCE ROLES ON ROUTES
           Open: routes/web.php
           Wrap route groups with role middleware from Spatie:

           // Inventory management — Admin and Inventory Manager only
           Route::middleware(['role:Admin|Inventory Manager'])->group(function () {
               Route::resource('products', ProductController::class)->except(['index', 'show']);
               Route::resource('categories', CategoryController::class);
               Route::resource('suppliers', SupplierController::class);
           });

           // Reports and refunds — Admin only
           Route::middleware(['role:Admin'])->group(function () {
               Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
               Route::post('sales/{sale}/refund', [SaleController::class, 'refund'])->name('sales.refund');
           });

           // POS and sales — Cashier and above
           Route::middleware(['role:Admin|Cashier|Inventory Manager|Report Viewer'])->group(function () {
               Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
               Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
           });

Step 10.4  ACTIVITY LOGGING ON KEY ACTIONS
           In SaleService completeSale():
           activity()->causedBy($cashier)
               ->log('Sale ' . $sale->sale_number . ' — Total: GH₵ ' . $sale->total);

           In SaleService refund():
           activity()->causedBy(auth()->user())
               ->log('Refund processed: ' . $sale->sale_number);

           In ProductController update():
           activity()->causedBy(auth()->user())->performedOn($product)
               ->withProperties(['old' => $product->getOriginal(), 'new' => $product->getChanges()])
               ->log('Product updated: ' . $product->name);

Step 10.5  FINAL FORM VALIDATION REVIEW
           Go through every form in the system:
           ✓ All required fields are validated server-side (in Form Requests)
           ✓ Every field shows its error message below it: {errors.fieldName && <p>...}
           ✓ No form can be submitted empty

Step 10.6  ADD DATABASE INDEXES FOR PERFORMANCE
           php artisan make:migration add_indexes_to_tables

           In the up() method:
           Schema::table('products', function (Blueprint $table) {
               $table->index('part_number');
               $table->index('barcode');
               $table->index('is_active');
           });
           Schema::table('sales', function (Blueprint $table) {
               $table->index('created_at');
               $table->index('sale_number');
               $table->index('status');
           });

           php artisan migrate

           WHY: As the database grows to thousands of records, unindexed queries
           on frequently searched columns get slow. Indexes are like a book's index —
           they let the database find records without scanning every row.

Step 10.7  PRODUCTION BUILD
           In .env, confirm:
           APP_ENV=production
           APP_DEBUG=false

           Run:
           php artisan config:cache
           php artisan route:cache
           php artisan view:cache
           npm run build

           WHY these commands:
           config:cache  → loads all config files into one file (faster startup)
           route:cache   → compiles all routes into one file (faster routing)
           view:cache    → pre-compiles Blade templates (faster page rendering)
           npm run build → compiles + minifies React/TypeScript for production

Step 10.8  DATABASE BACKUPS
           Set up automated daily backup of your MySQL database.
           Keep at minimum 30 days of backups.
           Test restoring from a backup before going live — untested backups are not backups.

Step 10.9  STAFF TRAINING DOCUMENT
           Create a simple one-page printed guide for the cashier with screenshots:
           1. How to make a sale (step by step)
           2. How to process a refund
           3. What to do if the printer does not work
           4. Who to call if something breaks
```

**Done check:** All roles enforced. All forms show validation errors. Toasts work on every action. Activity log captures key events. Production build completes with no errors.

```bash
git add .
git commit -m "feat: production polish — roles enforced, toast notifications, activity log, indexes"
```

---

## PART 10 — GIT COMMIT STRATEGY

Commit after every step that successfully works. Small, focused commits mean readable history and the ability to undo specific changes without losing other work.

**Format:** `type: short description of what changed`

| Type | When to use |
|---|---|
| `feat:` | New feature or functionality |
| `fix:` | Bug fix |
| `style:` | Visual/CSS only — no logic change |
| `refactor:` | Code restructured, same behaviour |
| `chore:` | Config, package installs, tooling |

```bash
# Run after each working step:
git add .
git commit -m "feat: add products migration and model"
```

---

## PART 11 — TROUBLESHOOTING GUIDE

Keep this section open when something goes wrong.

| Problem | Solution |
|---|---|
| 404 Not Found | Check routes/web.php. Run: `php artisan route:list` |
| PHP class not found | Run: `composer dump-autoload` |
| React not updating | Is `npm run dev` still running? Check browser console (F12) |
| Database change not showing | Forgot to run `php artisan migrate` |
| Inertia not passing data | Controller must use `Inertia::render()` not `view()`. Prop key must match TypeScript interface exactly. |
| TypeScript red underline | Read the error — TypeScript tells you exactly what is wrong. Fix before running. |
| shadcn component missing | `npx shadcn@latest add [component-name]` |
| Validation errors not showing | Check `{errors.fieldName && <p>...</p>}` is in the JSX for each field |
| Roles not working | Did you add `use HasRoles;` to User model? Run `php artisan migrate`? Run `RoleSeeder`? |
| `npm run dev` crashes | Check terminal output for the exact error. Usually a TypeScript error in a file you just edited. |
| Inertia page not found | Page file path must exactly match the string in `Inertia::render('products/index')` |

---

## PART 12 — CONFIRMED PROJECT FACTS

| Fact | Value |
|---|---|
| Package manager | `npm` — always use npm, not pnpm or yarn |
| PHP version | ^8.3 |
| Laravel version | ^13.7 |
| React version | ^19.2 |
| Inertia.js version | ^3.0 |
| Tailwind CSS version | ^4.0 — no tailwind.config.js — config is in app.css |
| shadcn/ui style | new-york |
| shadcn base color | neutral |
| Icon library | lucide-react — already installed |
| TypeScript | strict: true — type errors are enforced, not optional |
| Pages location | `resources/js/pages/` (lowercase) |
| Path aliases | `@/components`, `@/lib`, `@/hooks`, `@/pages` — all work |
| Dev server command | `npm run dev` (Terminal 1, leave running) |
| Production build | `npm run build` |
| PHP commands | `php artisan [command]` (Terminal 2) |
| Local URL | `http://sabr-89.test` |
| Project folder | `C:\Users\Admin\Herd\sabr-89` |
| Font system | Bunny Fonts loaded via laravel-vite-plugin in vite.config.ts |
| Format code | `npm run format` |
| Type check | `npm run types:check` |

---

*Document version: 3.0*
*Stack: Laravel 13 + React 19 + Inertia.js v3 + Tailwind v4 + TypeScript strict mode*
*Project: Sabr 89 Management System — Kumasi, Ghana*
*Principle: Edit existing starter kit files first. Only create new files when nothing existing can serve the purpose.*
