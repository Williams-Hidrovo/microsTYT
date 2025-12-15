import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';
import { AuthResponse, LoginRequest, RegisterRequest, RefreshTokenRequest, RevokeTokenRequest, User } from '../models/auth.model';

@Injectable({
    providedIn: 'root'
})
export class AuthService {
    private readonly AUTH_TOKEN_KEY = 'auth_token';
    private readonly REFRESH_TOKEN_KEY = 'refresh_token';
    private readonly USER_KEY = 'user';

    private currentUserSubject = new BehaviorSubject<User | null>(this.getUserFromStorage());
    public currentUser$ = this.currentUserSubject.asObservable();

    constructor(
        private http: HttpClient,
        private router: Router
    ) { }

    login(credentials: LoginRequest): Observable<AuthResponse> {
        return this.http.post<AuthResponse>(
            `${environment.authServiceUrl}/api/auth/login`,
            credentials
        ).pipe(
            tap(response => {
                this.handleAuthResponse(response);
                // Decodificar el token y extraer el usuario
                this.setUserFromToken(response.accessToken);
            })
        );
    }

    register(data: RegisterRequest): Observable<AuthResponse> {
        return this.http.post<AuthResponse>(
            `${environment.authServiceUrl}/api/auth/register`,
            data
        ).pipe(
            tap(response => {
                this.handleAuthResponse(response);
                // Decodificar el token y extraer el usuario
                this.setUserFromToken(response.accessToken);
            })
        );
    }

    logout(): void {
        const refreshToken = this.getRefreshToken();
        if (refreshToken) {
            // Intentar revocar el token antes de limpiar
            this.revokeToken({ refreshToken }).subscribe({
                next: () => this.clearAuthData(),
                error: () => this.clearAuthData() // Limpiar aunque falle la revocación
            });
        } else {
            this.clearAuthData();
        }
    }

    refreshToken(): Observable<AuthResponse> {
        const refreshToken = this.getRefreshToken();
        return this.http.post<AuthResponse>(
            `${environment.authServiceUrl}/api/auth/refresh`,
            { refreshToken }
        ).pipe(
            tap(response => this.handleAuthResponse(response))
        );
    }

    revokeToken(request: RevokeTokenRequest): Observable<{ message: string }> {
        return this.http.post<{ message: string }>(
            `${environment.authServiceUrl}/api/auth/revoke`,
            request
        );
    }

    getCurrentUserFromApi(): Observable<User> {
        return this.http.get<User>(
            `${environment.authServiceUrl}/api/auth/me`
        ).pipe(
            tap(user => {
                localStorage.setItem(this.USER_KEY, JSON.stringify(user));
                this.currentUserSubject.next(user);
            })
        );
    }

    isAuthenticated(): boolean {
        return !!this.getToken();
    }

    getToken(): string | null {
        return localStorage.getItem(this.AUTH_TOKEN_KEY);
    }

    getRefreshToken(): string | null {
        return localStorage.getItem(this.REFRESH_TOKEN_KEY);
    }

    getCurrentUser(): User | null {
        return this.currentUserSubject.value;
    }

    private handleAuthResponse(response: AuthResponse): void {
        localStorage.setItem(this.AUTH_TOKEN_KEY, response.accessToken);
        localStorage.setItem(this.REFRESH_TOKEN_KEY, response.refreshToken);
    }

    private clearAuthData(): void {
        localStorage.removeItem(this.AUTH_TOKEN_KEY);
        localStorage.removeItem(this.REFRESH_TOKEN_KEY);
        localStorage.removeItem(this.USER_KEY);
        this.currentUserSubject.next(null);
        this.router.navigate(['/auth/login']);
    }

    private getUserFromStorage(): User | null {
        const userJson = localStorage.getItem(this.USER_KEY);
        return userJson ? JSON.parse(userJson) : null;
    }

    private setUserFromToken(token: string): void {
        try {
            // Decodificar el JWT (formato: header.payload.signature)
            const parts = token.split('.');
            if (parts.length !== 3) {
                console.error('Token JWT inválido');
                return;
            }

            // Decodificar el payload (segunda parte)
            const payload = JSON.parse(atob(parts[1]));

            // Extraer claims del usuario
            const user: User = {
                userId: payload.sub || payload['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/nameidentifier'],
                email: payload.email || payload['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'],
                username: payload.username || payload.email
            };

            // Guardar en localStorage y actualizar el subject
            localStorage.setItem(this.USER_KEY, JSON.stringify(user));
            this.currentUserSubject.next(user);
        } catch (error) {
            console.error('Error al decodificar token:', error);
        }
    }
}
