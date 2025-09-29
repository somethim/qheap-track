export type Product = {
    id: number;
    name: string;
    description: string | null;
    sku: string;
    price: number;
    stock_quantity: number;
    created_at: string;
    updated_at: string;
};

export interface BaseOrder {
    id: number;
    order_number: string;
    total_amount: number;
    item_count: number;
    created_at: string;
    updated_at: string;
    products: Product[];
}

export type Client = {
    id: number;
    name: string;
    description: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    address: string | null;
    created_at: string;
    updated_at: string;
};

export interface ClientOrder extends BaseOrder {
    client_id: number;
    supplier_id: null;
    client: Client;
}

export type Supplier = Client;

export interface SupplierOrder extends BaseOrder {
    client_id: null;
    supplier_id: number;
    supplier: Supplier;
}

export type Order = ClientOrder | SupplierOrder;

export const isClientOrder = (order: Order): order is ClientOrder => {
    return 'client' in order && order.client_id !== null;
};
