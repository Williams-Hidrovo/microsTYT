export interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string;
    created_at?: string;
    updated_at?: string;
    orders?: any[];
}

export interface CreateCustomerRequest {
    name: string;
    email: string;
    phone: string;
}

export interface UpdateCustomerRequest extends CreateCustomerRequest { }

export interface CustomerResponse {
    message: string;
    data: Customer;
}

export interface PaginatedCustomerResponse {
    current_page: number;
    data: Customer[];
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

export interface CustomerDetailResponse {
    data: Customer;
}
