// ---- Model types matching the Laravel database schema ----

export interface Category {
    id: number;
    name: string;
    description: string | null;
    products_count?: number;
    created_at: string;
    updated_at: string;
}

export interface Supplier {
    id: number;
    name: string;
    contact_person: string | null;
    phone: string | null;
    email: string | null;
    address: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    name: string;
    description: string | null;
    part_number: string | null;
    category_id: number;
    supplier_id: number | null;
    unit: 'piece' | 'litre' | 'set' | 'pair' | 'box';
    cost_price: number;
    selling_price: number;
    quantity: number;
    reorder_level: number;
    image: string | null;
    is_active: boolean;
    category?: Category;
    supplier?: Supplier;
    created_at: string;
    updated_at: string;
}

export interface Customer {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

export interface Sale {
    id: number;
    sale_number: string;
    customer_id: number | null;
    user_id: number;
    payment_method: 'cash' | 'momo';
    momo_reference: string | null;
    subtotal: number;
    discount: number;
    total: number;
    amount_tendered: number | null;
    change_given: number | null;
    status: 'completed' | 'refunded';
    notes: string | null;
    customer?: Customer;
    user?: { id: number; name: string; email: string };
    items?: SaleItem[];
    created_at: string;
    updated_at: string;
}

export interface SaleItem {
    id: number;
    sale_id: number;
    product_id: number;
    quantity: number;
    unit_price: number;
    line_total: number;
    product?: Product;
    created_at: string;
    updated_at: string;
}

export interface Expense {
    id: number;
    title: string;
    amount: number;
    category: 'rent' | 'utilities' | 'transport' | 'salaries' | 'stock' | 'maintenance' | 'other';
    payment_method: 'cash' | 'momo';
    expense_date: string;
    notes: string | null;
    user_id: number;
    user?: { id: number; name: string };
    created_at: string;
    updated_at: string;
}

// ---- Pagination ----

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}
