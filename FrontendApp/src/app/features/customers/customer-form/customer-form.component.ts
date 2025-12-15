import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { CustomerService } from '../../../core/services/customer.service';

@Component({
    selector: 'app-customer-form',
    templateUrl: './customer-form.component.html',
    styleUrls: ['./customer-form.component.css']
})
export class CustomerFormComponent implements OnInit {
    customerForm!: FormGroup;
    loading = false;
    errorMessage = '';
    successMessage = '';
    customerId: number | null = null;
    isEditMode = false;

    constructor(
        private formBuilder: FormBuilder,
        private customerService: CustomerService,
        private router: Router,
        private route: ActivatedRoute
    ) { }

    ngOnInit(): void {
        this.customerForm = this.formBuilder.group({
            name: ['', [Validators.required, Validators.minLength(3)]],
            email: ['', [Validators.required, Validators.email]],
            phone: ['', [Validators.required]]
        });

        const id = this.route.snapshot.params['id'];
        if (id) {
            this.customerId = Number(id);
            this.isEditMode = true;
            this.loadCustomer();
        }
    }

    loadCustomer(): void {
        if (this.customerId) {
            this.loading = true;
            this.customerService.getCustomer(this.customerId).subscribe({
                next: (customer) => {
                    console.log('Customer loaded:', customer);
                    this.customerForm.patchValue({
                        name: customer.name,
                        email: customer.email,
                        phone: customer.phone
                    });
                    this.loading = false;
                },
                error: (error) => {
                    this.errorMessage = 'Error al cargar el cliente';
                    console.error('Error loading customer:', error);
                    this.loading = false;
                }
            });
        }
    }

    onSubmit(): void {
        if (this.customerForm.invalid) {
            return;
        }

        this.loading = true;
        this.errorMessage = '';
        this.successMessage = '';

        const customerData = this.customerForm.value;

        const request = this.isEditMode
            ? this.customerService.updateCustomer(this.customerId!, customerData)
            : this.customerService.createCustomer(customerData);

        request.subscribe({
            next: () => {
                this.successMessage = this.isEditMode
                    ? 'Cliente actualizado exitosamente'
                    : 'Cliente creado exitosamente';
                setTimeout(() => {
                    this.router.navigate(['/customers']);
                }, 1500);
            },
            error: (error) => {
                this.errorMessage = error.error?.message || 'Error al guardar el cliente';
                this.loading = false;
            }
        });
    }

    cancel(): void {
        this.router.navigate(['/customers']);
    }

    get f() {
        return this.customerForm.controls;
    }
}
