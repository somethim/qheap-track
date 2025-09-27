export interface Product {
    id: number;
    name: string;
    description: string | null;
    price: number | null;
    stock: number | null;
}

export interface Order {
    id: number;
    client_id: number | null;
    products: Pick<Product, 'id' | 'price' | 'stock'>;
}
