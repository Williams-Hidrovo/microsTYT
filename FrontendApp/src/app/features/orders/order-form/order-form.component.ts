import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { OrderService } from '../../../core/services/order.service';
import { CustomerService } from '../../../core/services/customer.service';
import { Customer } from '../../../core/models/customer.model';

@Component({
    selector: 'app-order-form',
    templateUrl: './order-form.component.html',
    styleUrls: ['./order-form.component.css']
})
export class OrderFormComponent implements OnInit {
    orderForm!: FormGroup;
    loading = false;
    errorMessage = '';
    successMessage = '';
    orderId: number | null = null;
    isEditMode = false;
    customers: Customer[] = [];

    statuses = [
        { value: 'pending', label: 'Pendiente' },
        { value: 'processing', label: 'En Proceso' },
        { value: 'completed', label: 'Completado' },
        { value: 'cancelled', label: 'Cancelado' }
    ];

    constructor(
        private formBuilder: FormBuilder,
        private orderService: OrderService,
        private customerService: CustomerService,
        private router: Router,
        private route: ActivatedRoute
    ) { }

    ngOnInit(): void {
        this.orderForm = this.formBuilder.group({
            customer_id: ['', [Validators.required]],
            order_number: [''],
            order_date: [''],
            status: ['pending', [Validators.required]],
            total_amount: ['', [Validators.required, Validators.min(0)]],
            currency: ['USD'],
            notes: ['']
        });

        this.loadCustomers();

        this.orderId = this.route.snapshot.params['id'];
        if (this.orderId) {
            this.isEditMode = true;
            this.loadOrder();
        } else {
            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            this.orderForm.patchValue({ order_date: today });
        }
    }

    loadCustomers(): void {
        this.customerService.getCustomers(undefined, 100).subscribe({
            next: (response) => {
                this.customers = response.data;
            },
            error: (error) => {
                console.error('Error loading customers:', error);
            }
        });
    }

    loadOrder(): void {
        if (this.orderId) {
            this.orderService.getOrder(this.orderId).subscribe({
                next: (order) => {
                    // Format date for input type="datetime-local"
                    const date = order.order_date ? new Date(order.order_date).toISOString().slice(0, 16) : '';
                    this.orderForm.patchValue({
                        customer_id: order.customer_id,
                        order_number: order.order_number,
                        order_date: date,
                        status: order.status,
                        total_amount: order.total_amount,
                        currency: order.currency || 'USD',
                        notes: order.notes || ''
                    });
                },
                error: (error) => {
                    this.errorMessage = 'Error al cargar el pedido';
                    console.error(error);
                }
            });
        }
    }

    onSubmit(): void {
        if (this.orderForm.invalid) {
            return;
        }

        this.loading = true;
        this.errorMessage = '';
        this.successMessage = '';

        const formValue = this.orderForm.value;
        const orderData: any = {
            customer_id: formValue.customer_id,
            status: formValue.status,
            total_amount: formValue.total_amount
        };

        if (formValue.order_number) orderData.order_number = formValue.order_number;
        if (formValue.order_date) orderData.order_date = new Date(formValue.order_date).toISOString();
        if (formValue.currency) orderData.currency = formValue.currency;
        if (formValue.notes) orderData.notes = formValue.notes;

        const request = this.isEditMode
            ? this.orderService.updateOrder(this.orderId!, orderData)
            : this.orderService.createOrder(orderData);

        request.subscribe({
            next: () => {
                this.successMessage = this.isEditMode
                    ? 'Pedido actualizado exitosamente'
                    : 'Pedido creado exitosamente';
                setTimeout(() => {
                    this.router.navigate(['/orders']);
                }, 1500);
            },
            error: (error) => {
                this.errorMessage = error.error?.message || 'Error al guardar el pedido';
                this.loading = false;
            }
        });
    }

    cancel(): void {
        this.router.navigate(['/orders']);
    }

    get f() {
        return this.orderForm.controls;
    }
}
