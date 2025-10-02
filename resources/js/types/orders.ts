export type Product = {
    id: number;
    name: string;
    sku: string;
    price: number;
    stock: number;
    created_at: string;
    updated_at: string;
};

export type OrderProduct = {
    id: number;
    order_id: number;
    product_id: number;
    stock: number;
    price: number;
    product: Product;
};

export interface BaseOrder {
    id: number;
    order_number: string;
    cost: number;
    stock: number;
    created_at: string;
    updated_at: string;
    order_products: OrderProduct[];
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
