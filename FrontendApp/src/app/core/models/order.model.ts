export interface Order {
    id: number;
    customer_id: number;
    order_number: string;
    status: string;
    total_amount: string | number;
    currency: string;
    notes?: string;
    order_date: string;
    created_at?: string;
    updated_at?: string;
    customer?: {
        id: number;
        name: string;
        email: string;
        phone: string;
    };
}

export interface CreateOrderRequest {
    customer_id: number;
    order_number?: string;
    status?: string;
    total_amount: number;
    currency?: string;
    notes?: string;
    order_date?: string;
}

export interface UpdateOrderRequest {
    customer_id: number;
    status?: string;
    total_amount: number;
    currency?: string;
    notes?: string;
    order_date?: string;
}

export interface OrderResponse {
    message: string;
    data: Order;
}

export interface PaginatedOrderResponse {
    current_page: number;
    data: Order[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

export interface OrderDetailResponse {
    data: Order;
}

export interface OrderFilters {
    status?: OrderStatus;
    customer_id?: number;
    start_date?: string;
    end_date?: string;
    search?: string;
    per_page?: number;
}

export enum OrderStatus {
    PENDING = 'pending',
    PROCESSING = 'processing',
    COMPLETED = 'completed',
    CANCELLED = 'cancelled'
}
