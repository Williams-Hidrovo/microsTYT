import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { CustomerService } from '../../../core/services/customer.service';
import { Customer } from '../../../core/models/customer.model';

@Component({
    selector: 'app-customer-list',
    templateUrl: './customer-list.component.html',
    styleUrls: ['./customer-list.component.css']
})
export class CustomerListComponent implements OnInit {
    customers: Customer[] = [];
    loading = true;
    errorMessage = '';
    searchTerm = '';
    currentPage = 1;
    totalPages = 1;
    perPage = 15;

    constructor(
        private customerService: CustomerService,
        private router: Router
    ) { }

    ngOnInit(): void {
        this.loadCustomers();
    }

    loadCustomers(): void {
        this.loading = true;
        this.customerService.getCustomers(this.searchTerm || undefined, this.perPage).subscribe({
            next: (response) => {
                this.customers = response.data;
                this.currentPage = response.current_page;
                this.totalPages = response.last_page;
                this.loading = false;
            },
            error: (error) => {
                this.errorMessage = 'Error al cargar clientes';
                this.loading = false;
                console.error(error);
            }
        });
    }

    onSearch(): void {
        this.loadCustomers();
    }

    deleteCustomer(id: number): void {
        if (confirm('¿Está seguro de eliminar este cliente?')) {
            this.customerService.deleteCustomer(id).subscribe({
                next: () => {
                    this.loadCustomers();
                },
                error: (error) => {
                    this.errorMessage = 'Error al eliminar cliente';
                    console.error(error);
                }
            });
        }
    }

    editCustomer(id: number): void {
        this.router.navigate(['/customers/edit', id]);
    }

    get filteredCustomers(): Customer[] {
        return this.customers;
    }
}
