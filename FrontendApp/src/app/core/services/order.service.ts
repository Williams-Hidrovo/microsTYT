import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import {
    Order,
    CreateOrderRequest,
    UpdateOrderRequest,
    OrderResponse,
    PaginatedOrderResponse,
    OrderDetailResponse,
    OrderFilters
} from '../models/order.model';

@Injectable({
    providedIn: 'root'
})
export class OrderService {
    private apiUrl = `${environment.orderServiceUrl}/api/pedidos`;

    constructor(private http: HttpClient) { }

    getOrders(filters?: OrderFilters): Observable<PaginatedOrderResponse> {
        let params = new HttpParams();
        if (filters) {
            if (filters.status) {
                params = params.set('status', filters.status);
            }
            if (filters.customer_id) {
                params = params.set('customer_id', filters.customer_id.toString());
            }
            if (filters.start_date) {
                params = params.set('start_date', filters.start_date);
            }
            if (filters.end_date) {
                params = params.set('end_date', filters.end_date);
            }
            if (filters.search) {
                params = params.set('search', filters.search);
            }
            if (filters.per_page) {
                params = params.set('per_page', filters.per_page.toString());
            }
        }
        return this.http.get<PaginatedOrderResponse>(this.apiUrl, { params });
    }

    getOrder(id: number): Observable<Order> {
        return this.http.get<OrderDetailResponse>(`${this.apiUrl}/${id}`)
            .pipe(map(response => response.data));
    }

    createOrder(order: CreateOrderRequest): Observable<Order> {
        return this.http.post<OrderResponse>(this.apiUrl, order)
            .pipe(map(response => response.data));
    }

    updateOrder(id: number, order: UpdateOrderRequest): Observable<Order> {
        return this.http.put<OrderResponse>(`${this.apiUrl}/${id}`, order)
            .pipe(map(response => response.data));
    }

    completeOrder(id: number): Observable<Order> {
        return this.http.patch<OrderResponse>(`${this.apiUrl}/${id}/complete`, {})
            .pipe(map(response => response.data));
    }

    cancelOrder(id: number): Observable<Order> {
        return this.http.patch<OrderResponse>(`${this.apiUrl}/${id}/cancel`, {})
            .pipe(map(response => response.data));
    }

    deleteOrder(id: number): Observable<{ message: string }> {
        return this.http.delete<{ message: string }>(`${this.apiUrl}/${id}`);
    }

    getOrdersByCustomer(customerId: number, perPage?: number): Observable<PaginatedOrderResponse> {
        return this.getOrders({ customer_id: customerId, per_page: perPage });
    }
}
