# OrderService - Microservicio de Gestión de Clientes y Pedidos

Este microservicio maneja la gestión de clientes y pedidos.

## Características

### Gestión de Clientes
- Crear nuevo cliente
- Listar clientes
- Editar información de cliente
- Eliminar cliente

### Gestión de Pedidos
- Crear nuevo pedido asociado a un cliente
- Listar pedidos con filtros por estado, fecha, cliente, etc.
- Editar detalles del pedido
- Marcar pedido como completado o cancelado
- Eliminar pedido

## Tecnologías
- Laravel 9
- MySQL 8.0
- PHP 8.1

## Instalación

```bash
docker-compose up -d orderservice
docker-compose exec orderservice composer install
docker-compose exec orderservice php artisan key:generate
docker-compose exec orderservice php artisan migrate
```

## API Endpoints

### Clientes
- `GET /api/customers` - Listar todos los clientes
- `POST /api/customers` - Crear nuevo cliente
- `GET /api/customers/{id}` - Obtener cliente por ID
- `PUT /api/customers/{id}` - Actualizar cliente
- `DELETE /api/customers/{id}` - Eliminar cliente

### Pedidos
- `GET /api/orders` - Listar pedidos (con filtros opcionales)
- `POST /api/orders` - Crear nuevo pedido
- `GET /api/orders/{id}` - Obtener pedido por ID
- `PUT /api/orders/{id}` - Actualizar pedido
- `PATCH /api/orders/{id}/complete` - Marcar como completado
- `PATCH /api/orders/{id}/cancel` - Marcar como cancelado
- `DELETE /api/orders/{id}` - Eliminar pedido
