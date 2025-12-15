import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({
    providedIn: 'root'
})
export class AuthGuard implements CanActivate {
    constructor(
        private authService: AuthService,
        private router: Router
    ) { }

    canActivate(
        route: ActivatedRouteSnapshot,
        state: RouterStateSnapshot
    ): boolean {
        if (this.authService.isAuthenticated()) {
            // Si está autenticado pero no hay usuario cargado, intentar cargar del storage
            if (!this.authService.getCurrentUser()) {
                const token = this.authService.getToken();
                if (token) {
                    // El servicio cargará el usuario del token o storage automáticamente
                    // Si no hay usuario en storage, el navbar simplemente no se mostrará
                }
            }
            return true;
        }

        this.router.navigate(['/auth/login'], {
            queryParams: { returnUrl: state.url }
        });
        return false;
    }
}
