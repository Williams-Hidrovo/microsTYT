# Test de los Microservicios con Docker Compose

## 1. Verificar que los servicios están corriendo
docker-compose ps

## 2. Registrar un usuario en AuthService
$registerBody = @{
    name = "Test User"
    email = "test@example.com"
    password = "Password123!"
} | ConvertTo-Json

$registerResponse = Invoke-RestMethod -Uri "http://localhost:5000/api/auth/register" `
    -Method Post `
    -Body $registerBody `
    -ContentType "application/json"

Write-Host "Usuario registrado:"
$registerResponse | ConvertTo-Json

## 3. Hacer login para obtener el token
$loginBody = @{
    email = "test@example.com"
    password = "Password123!"
} | ConvertTo-Json

$loginResponse = Invoke-RestMethod -Uri "http://localhost:5000/api/auth/login" `
    -Method Post `
    -Body $loginBody `
    -ContentType "application/json"

$token = $loginResponse.token
Write-Host "`nToken obtenido:"
Write-Host $token

## 4. Intentar acceder a OrderService SIN token (debe fallar)
try {
    Invoke-RestMethod -Uri "http://localhost:8001/api/customers" -Method Get
} catch {
    Write-Host "`n[OK] Sin token fue rechazado (401 Unauthorized)"
    Write-Host "Error: $_"
}

## 5. Acceder a OrderService CON token (debe funcionar)
$headers = @{
    Authorization = "Bearer $token"
}

$customers = Invoke-RestMethod -Uri "http://localhost:8001/api/customers" `
    -Method Get `
    -Headers $headers

Write-Host "`n[OK] Con token fue aceptado"
Write-Host "Clientes obtenidos:"
$customers | ConvertTo-Json

## 6. Crear un cliente
$customerBody = @{
    name = "John Doe"
    email = "john@example.com"
    phone = "1234567890"
} | ConvertTo-Json

$newCustomer = Invoke-RestMethod -Uri "http://localhost:8001/api/customers" `
    -Method Post `
    -Body $customerBody `
    -Headers $headers `
    -ContentType "application/json"

Write-Host "`n[OK] Cliente creado:"
$newCustomer | ConvertTo-Json
