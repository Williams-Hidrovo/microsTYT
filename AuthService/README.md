# AuthService - Microservicio de Autenticación

##  Cómo Levantar el Proyecto

### 1. Iniciar SQL Server
```bash
docker-compose up -d
```

### 2. Aplicar Migraciones
```bash
cd AuthService
dotnet ef database update -p src/AuthService.Infrastructure -s src/AuthService.Api
```

### 3. Ejecutar la Aplicación
```bash
cd src/AuthService.Api
dotnet run
```

La API estará disponible en:
- **Swagger UI**: https://localhost:7xxx/swagger

##  Cómo Autenticarse

### 1. Registrar un Usuario
```
POST /api/auth/register

{
  "email": "usuario@ejemplo.com",
  "password": "Password123!",
  "confirmPassword": "Password123!"
}
```

### 2. Iniciar Sesión
```
POST /api/auth/login

{
  "email": "usuario@ejemplo.com",
  "password": "Password123!"
}

Respuesta:
{
  "accessToken": "eyJhbGciOiJIUzI1NiIs...",
  "refreshToken": "base64_encoded_token...",
  "expiresAt": "2025-12-21T10:30:00Z"
}
```

### 3. Usar el Token
En Swagger, haz clic en "Authorize" e ingresa:
```
Bearer {tu_accessToken_aqui}
```
