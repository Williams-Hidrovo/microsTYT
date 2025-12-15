import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import {
    Customer,
    CreateCustomerRequest,
    UpdateCustomerRequest,
    CustomerResponse,
    PaginatedCustomerResponse,
    CustomerDetailResponse
} from '../models/customer.model';

@Injectable({
    providedIn: 'root'
})
export class CustomerService {
    private apiUrl = `${environment.orderServiceUrl}/api/clientes`;

    constructor(private http: HttpClient) { }

    getCustomers(search?: string, perPage?: number): Observable<PaginatedCustomerResponse> {
        let params = new HttpParams();
        if (search) {
            params = params.set('search', search);
        }
        if (perPage) {
            params = params.set('per_page', perPage.toString());
        }
        return this.http.get<PaginatedCustomerResponse>(this.apiUrl, { params });
    }

    getCustomer(id: number): Observable<Customer> {
        return this.http.get<Customer>(`${this.apiUrl}/${id}`);
    }

    createCustomer(customer: CreateCustomerRequest): Observable<Customer> {
        return this.http.post<CustomerResponse>(this.apiUrl, customer)
            .pipe(map(response => response.data));
    }

    updateCustomer(id: number, customer: UpdateCustomerRequest): Observable<Customer> {
        return this.http.put<CustomerResponse>(`${this.apiUrl}/${id}`, customer)
            .pipe(map(response => response.data));
    }

    deleteCustomer(id: number): Observable<{ message: string }> {
        return this.http.delete<{ message: string }>(`${this.apiUrl}/${id}`);
    }
}
