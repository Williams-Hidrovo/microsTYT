import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { OrderService } from '../../../core/services/order.service';
import { Order } from '../../../core/models/order.model';

@Component({
    selector: 'app-order-list',
    templateUrl: './order-list.component.html',
    styleUrls: ['./order-list.component.css']
})
export class OrderListComponent implements OnInit {
    orders: Order[] = [];
    loading = true;
    errorMessage = '';
    searchTerm = '';
    filterStatus = '';
    currentPage = 1;
    totalPages = 1;
    perPage = 15;

    constructor(
        private orderService: OrderService,
        private router: Router
    ) { }

    ngOnInit(): void {
        this.loadOrders();
    }

    loadOrders(): void {
        this.loading = true;
        const filters: any = { per_page: this.perPage };
        if (this.searchTerm) filters.search = this.searchTerm;
        if (this.filterStatus) filters.status = this.filterStatus;

        this.orderService.getOrders(filters).subscribe({
            next: (response) => {
                this.orders = response.data;
                this.currentPage = response.current_page;
                this.totalPages = response.last_page;
                this.loading = false;
            },
            error: (error) => {
                this.errorMessage = 'Error al cargar pedidos';
                this.loading = false;
                console.error(error);
            }
        });
    }

    onSearch(): void {
        this.loadOrders();
    }

    onFilterChange(): void {
        this.loadOrders();
    }

    deleteOrder(id: number): void {
        if (confirm('¿Está seguro de eliminar este pedido?')) {
            this.orderService.deleteOrder(id).subscribe({
                next: () => {
                    this.loadOrders();
                },
                error: (error) => {
                    this.errorMessage = 'Error al eliminar pedido';
                    console.error(error);
                }
            });
        }
    }

    editOrder(id: number): void {
        this.router.navigate(['/orders/edit', id]);
    }

    get filteredOrders(): Order[] {
        return this.orders;
    }

    getStatusClass(status: string): string {
        const statusMap: { [key: string]: string } = {
            'completed': 'badge-completed',
            'pending': 'badge-pending',
            'processing': 'badge-processing',
            'cancelled': 'badge-cancelled'
        };
        return statusMap[status] || '';
    }

    formatAmount(amount: string | number): string {
        const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
        return numAmount.toFixed(2);
    }
}
