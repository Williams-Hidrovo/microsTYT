import { Component, OnInit } from '@angular/core';
import { ChartConfiguration, ChartData, ChartType } from 'chart.js';
import { OrderService } from '../../core/services/order.service';
import { CustomerService } from '../../core/services/customer.service';
import { Order, OrderStatus } from '../../core/models/order.model';
import { Customer } from '../../core/models/customer.model';
import { forkJoin } from 'rxjs';

interface DashboardStats {
    totalOrders: number;
    completedOrders: number;
    pendingOrders: number;
    processingOrders: number;
    activeCustomers: number;
    totalRevenue: number;
}

@Component({
    selector: 'app-dashboard',
    templateUrl: './dashboard.component.html',
    styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
    stats: DashboardStats = {
        totalOrders: 0,
        completedOrders: 0,
        pendingOrders: 0,
        processingOrders: 0,
        activeCustomers: 0,
        totalRevenue: 0
    };

    loading = true;
    orders: Order[] = [];
    customers: Customer[] = [];

    // Chart configurations
    public lineChartData: ChartConfiguration<'line'>['data'] = {
        labels: [],
        datasets: []
    };

    public lineChartOptions: ChartConfiguration<'line'>['options'] = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true }
        }
    };

    public pieChartData: ChartData<'pie', number[], string> = {
        labels: ['Completados', 'Pendientes', 'En Proceso', 'Cancelados'],
        datasets: [{
            data: [0, 0, 0, 0]
        }]
    };

    public pieChartOptions: ChartConfiguration<'pie'>['options'] = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    };

    public pieChartType: ChartType = 'pie';

    constructor(
        private orderService: OrderService,
        private customerService: CustomerService
    ) { }

    ngOnInit(): void {
        this.loadDashboardData();
    }

    loadDashboardData(): void {
        this.loading = true;

        forkJoin({
            orders: this.orderService.getOrders({ per_page: 1000 }),
            customers: this.customerService.getCustomers(undefined, 1000)
        }).subscribe({
            next: (data) => {
                this.orders = data.orders.data;
                this.customers = data.customers.data;
                this.calculateStats();
                this.prepareCharts();
                this.loading = false;
            },
            error: (error) => {
                console.error('Error loading dashboard data:', error);
                this.loading = false;
            }
        });
    }

    calculateStats(): void {
        this.stats.totalOrders = this.orders.length;
        this.stats.completedOrders = this.orders.filter(o => o.status === 'completed').length;
        this.stats.pendingOrders = this.orders.filter(o => o.status === 'pending').length;
        this.stats.processingOrders = this.orders.filter(o => o.status === 'processing').length;
        this.stats.activeCustomers = this.customers.length;
        this.stats.totalRevenue = this.orders
            .filter(o => o.status === 'completed')
            .reduce((sum, order) => sum + (typeof order.total_amount === 'string' ? parseFloat(order.total_amount) : order.total_amount), 0);
    }

    prepareCharts(): void {
        this.preparePieChart();
        this.prepareLineChart();
    }

    preparePieChart(): void {
        const completed = this.orders.filter(o => o.status === 'completed').length;
        const pending = this.orders.filter(o => o.status === 'pending').length;
        const processing = this.orders.filter(o => o.status === 'processing').length;
        const cancelled = this.orders.filter(o => o.status === 'cancelled').length;

        this.pieChartData = {
            labels: ['Completados', 'Pendientes', 'En Proceso', 'Cancelados'],
            datasets: [{
                data: [completed, pending, processing, cancelled],
                backgroundColor: ['#28a745', '#ffc107', '#007bff', '#dc3545']
            }]
        };
    }

    prepareLineChart(): void {
        // Group orders by date
        const ordersByDate = this.groupOrdersByDate();
        const dates = Object.keys(ordersByDate).sort();
        const counts = dates.map(date => ordersByDate[date].length);
        const revenues = dates.map(date =>
            ordersByDate[date].reduce((sum, order) => sum + (typeof order.total_amount === 'string' ? parseFloat(order.total_amount) : order.total_amount), 0)
        );

        this.lineChartData = {
            labels: dates.slice(-30), // Last 30 days
            datasets: [
                {
                    data: counts.slice(-30),
                    label: 'Número de Pedidos',
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    data: revenues.slice(-30),
                    label: 'Ingresos ($)',
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        };

        this.lineChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: { display: true, position: 'top' }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left'
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        };
    }

    groupOrdersByDate(): { [key: string]: Order[] } {
        const grouped: { [key: string]: Order[] } = {};

        this.orders.forEach(order => {
            const date = new Date(order.order_date).toISOString().split('T')[0];
            if (!grouped[date]) {
                grouped[date] = [];
            }
            grouped[date].push(order);
        });

        return grouped;
    }

    formatAmount(amount: string | number): string {
        const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
        return numAmount.toFixed(2);
    }
}
