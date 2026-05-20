export const APP_NAME = 'Sabr 89';
export const SHOP_PHONE = '0537 202641 / 0559 133733';
export const SHOP_LOCATION = 'Kumasi, Ghana';

export function formatGHS(amount: number): string {
    try {
        const formatter = new Intl.NumberFormat('en-GH', {
            style: 'currency',
            currency: 'GHS',
            minimumFractionDigits: 2,
        });

        return formatter.format(amount);
    } catch {
        // Fallback simple formatting
        const fixed = Number(amount || 0).toFixed(2);
        return `GHS ${fixed.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
    }
}

export function formatSimpleDate(dateStr?: string | null): string {
    if (!dateStr) return '—';
    return dateStr.split('T')[0];
}

/** Expense category options */
export const EXPENSE_CATEGORIES = [
    { value: 'rent', label: 'Rent' },
    { value: 'utilities', label: 'Utilities' },
    { value: 'transport', label: 'Transport' },
    { value: 'salaries', label: 'Salaries' },
    { value: 'stock', label: 'Stock' },
    { value: 'maintenance', label: 'Maintenance' },
    { value: 'other', label: 'Other' },
] as const;

/** Product unit options */
export const PRODUCT_UNITS = [
    { value: 'piece', label: 'Piece' },
    { value: 'litre', label: 'Litre' },
    { value: 'set', label: 'Set' },
    { value: 'pair', label: 'Pair' },
    { value: 'box', label: 'Box' },
] as const;

/** Payment method options */
export const PAYMENT_METHODS = [
    { value: 'cash', label: 'Cash' },
    { value: 'momo', label: 'MoMo' },
] as const;

