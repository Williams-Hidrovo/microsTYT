# MicrosTYT - Microservices Project

Proyecto de arquitectura de microservicios que incluye:
- **AuthService**: Servicio de autenticación (.NET/C#)
- **OrderService**: Servicio de gestión de pedidos (Laravel/PHP)
- **FrontendApp**: Aplicación web frontend (Angular)

##  Requisitos Previos

- Docker Desktop instalado y en ejecución
- Docker Compose
- Puertos disponibles: 80, 5432, 3306, 8080, 8081

##  Instalación y Configuración

### 1. Construir los contenedores

Construye todos los servicios desde cero (sin caché):

```bash
docker-compose build --no-cache
```

### 2. Levantar los servicios

Inicia todos los contenedores en modo detached (segundo plano):

```bash
docker-compose up -d
```

### 3. Ejecutar migraciones de base de datos

Crea las tablas y datos de prueba en el servicio de órdenes:

```bash
docker exec orderservice php artisan migrate:fresh --force --seed
```

**Nota**: Este comando eliminará todos los datos existentes y recreará las tablas con datos de prueba.










##  Actualización de Servicios

### Actualizar Frontend

Si realizas cambios en el código del frontend, reconstruye solo ese servicio:

```bash
docker-compose up -d --build frontend
```

### Actualizar OrderService

Si realizas cambios en el código PHP del OrderService, tienes dos opciones:

**Opción 1 - Solo reiniciar** (recomendado para cambios de código):
```bash
docker-compose restart orderservice
```

**Opción 2 - Reconstruir el contenedor** (si cambias Dockerfile o dependencias):
```bash
docker-compose up -d --build orderservice
```

### Actualizar AuthService

Si realizas cambios en el código C# del AuthService:

```bash
docker-compose up -d --build authservice
```

### Actualizar todos los servicios

Para reconstruir y reiniciar todos los servicios:

```bash
docker-compose up -d --build
```

##  Credenciales de Acceso

Para acceder a la aplicación, usa las siguientes credenciales por defecto:

- **Email**: `admin@gmail.com`
- **Password**: `admin123`



##  Verificación

Una vez iniciados los servicios, puedes acceder a:

- **Frontend**: http://localhost
- **OrderService API**: http://localhost:8080
- **AuthService API**: http://localhost:8081
- **Swagger OrderService**: http://localhost:8080/api/documentation

##  Comandos Útiles

### Ver logs de un servicio específico
```bash
docker-compose logs -f [nombre-servicio]
```

### Detener todos los servicios
```bash
docker-compose down
```

### Reiniciar un servicio específico
```bash
docker-compose restart [nombre-servicio]
```

### Acceder al contenedor de un servicio
```bash
docker exec -it [nombre-servicio] bash
```
