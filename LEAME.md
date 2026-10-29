# Orion — Sistema de Gestión Comercial y Servicio Técnico

[![Sitio Web](https://img.shields.io/badge/Sitio_Web-android--evolucion.42web.io-blue?style=flat-square&logo=googlechrome)](https://android-evolucion.42web.io)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.4-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![Flight PHP](https://img.shields.io/badge/Flight_PHP-3.19-f38b00?style=flat-square)](https://flightphp.com/)
[![Eloquent ORM](https://img.shields.io/badge/Eloquent-13.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com/)
[![English Version](https://img.shields.io/badge/Language-English-informational?style=flat-square)](README.md)

> 🌐 **Sitio Web / Demo:** [https://android-evolucion.42web.io](https://android-evolucion.42web.io)  
> 📖 **Idioma:** Español | [English version](README.md)

Sistema web ligero y moderno para la administración comercial, control de inventario, facturación de ventas, gestión de apartados y servicio técnico de telefonía y repuestos, con soporte multi-negocio y sincronización de tasa de cambio oficial (BCV).

---

## 🚀 Características Principales

- **Gestión Multi-Negocio / Sucursales**:
  - Permite a cada usuario administrar múltiples tiendas o sucursales de forma independiente.
  - Conmutador rápido de sucursal activa en la barra superior.
  - Registro de información fiscal (RIF), dirección y teléfono por cada sede.

- **Cotización Diaria y Tasa BCV**:
  - Integración automática con API externa para obtener la tasa oficial del Banco Central de Venezuela (BCV).
  - Middleware de control que garantiza que la cotización del día esté confirmada antes de registrar ventas u operaciones.
  - Posibilidad de definir cotizaciones personalizadas por usuario.
  - Calculadora de conversión rápida USD/VES integrada.

- **Control de Inventario y Productos**:
  - Clasificación por categorías: Teléfonos (`phone`), Accesorios (`accessory`) y Repuestos (`spare_part`).
  - Soporte para seriales e identificadores de telefonía: **IMEI 1**, **IMEI 2** y códigos de lote.
  - Control de existencias por sucursal mediante lotes (`batches`).
  - Fijación de precios en divisas (USD) y cálculo automático en moneda nacional (VES).

- **Ventas y Facturación**:
  - Carrito y registro de ventas multilínea.
  - Soporte de múltiples métodos de pago combinados:
    - Efectivo en USD
    - Efectivo en Bolívares (VES)
    - Punto de Venta (Tarjeta de Débito/Crédito)
    - Transferencia bancaria / Pago Móvil
  - Generación de comprobante / ticket de venta imprimible (`invoice`).
  - Anulación de ventas con reversión automática del inventario.

- **Módulo de Apartados (Layaways)**:
  - Reserva de productos con abonos iniciales y pagos en cuotas sucesivas.
  - Seguimiento del saldo pendiente, estado y liquidación o cancelación.

- **Servicio Técnico y Reparaciones**:
  - Registro de órdenes de reparación de dispositivos (marca, modelo, cliente, motivo de ingreso, falla y costo estimado).
  - Control de pagos y abonos parciales a órdenes de servicio.
  - Comprobante de recepción y entrega de equipo técnico (`repair-receipt`).

- **Gestión de Clientes**:
  - Directorio centralizado de clientes con documento de identidad (C.I. / RIF), teléfono, correo y dirección física.

- **Interfaz y Experiencia de Usuario**:
  - Diseño responsivo y moderno tipo panel de administración (Dashboard).
  - Soporte para **Modo Oscuro** y **Modo Claro** automático con `light-dark()` de CSS.
  - Mensajes de confirmación y alertas mediante notificaciones flotantes (*toasts*).
  - Sin dependencias pesadas de JavaScript o bundlers (HTML5 semántico, CSS moderno y Vanilla JS).

---

## 🛠️ Stack Tecnológico

| Capa | Tecnología | Descripción |
| :--- | :--- | :--- |
| **Lenguaje** | PHP 8.4+ | Tipado estricto (`declare(strict_types=1);`), atributos nativos, promotores de propiedades |
| **Enrutador / Framework** | [Flight PHP](https://flightphp.com/) (^3.19) | Micro-framework ligero, rápido y flexible para rutas web y API |
| **ORM / Base de Datos** | [Illuminate Database](https://laravel.com/) (^13.38) | Eloquent ORM de Laravel para modelos, relaciones y migraciones de base de datos |
| **Autenticación** | [Leaf Auth](https://leafphp.dev/) (^5.1) | Manejo de sesiones y autenticación segura con Bcrypt |
| **Internacionalización** | [Leaf Lingo](https://leafphp.dev/) (^5.0) | Traducciones y localización en español |
| **Peticiones HTTP** | [Guzzle](https://docs.guzzlephp.org/) (^8.3) | Cliente HTTP para consumir la API de cotizaciones y pruebas de integración |
| **Variables de Entorno** | [Symfony Dotenv](https://symfony.com/doc/current/components/dotenv.html) (^8.1) | Carga y parseo seguro de configuración `.env` |
| **Base de Datos** | SQLite (por defecto) / MySQL | Almacenamiento local ligero en SQLite con opción de conexión a MariaDB/MySQL |
| **Pruebas** | [PHPUnit](https://phpunit.de/) (^13.4) | Suite completa de pruebas unitarias y de integración HTTP |

---

## 📁 Estructura del Proyecto

```text
android-evolucion-ss-fp/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controladores de recursos y de acción única (__invoke)
│   │   └── Middleware/           # Middlewares (Auth, BusinessSelected, ExchangeRate, etc.)
│   ├── Models/                   # Modelos de Eloquent (User, Business, Product, Sale, Repair...)
│   ├── Services/                 # Lógica externa (BcvExchangeRate, DailyExchangeRate)
│   ├── Support/                  # Clases auxiliares y enumeradores (PaymentMethod, PaymentDetails)
│   ├── Flash.php                 # Utilidades de mensajes flash
│   ├── FlashKey.php              # Claves de sesión flash
│   └── Migration.php             # Contrato de interfaz para migraciones
├── database/
│   ├── migrations/               # Migraciones de esquema ejecutadas automáticamente
│   └── database.sqlite           # Base de datos local SQLite (creada en el primer arranque)
├── lang/
│   └── es.yml                    # Archivo de mensajes y traducciones al español
├── resources/
│   ├── images/                   # Favicon y assets estáticos
│   ├── styles/
│   │   └── app.css               # Estilos CSS modernos con variables y temas
│   └── views/                    # Vistas PHP y componentes reutilizables
│       ├── components/           # Componentes modulares (layout, nav, sidebar, toast, etc.)
│       ├── dashboard.php         # Panel principal
│       ├── sales.php             # Módulo de ventas
│       ├── invoice.php           # Factura / Comprobante de venta
│       ├── products.php          # Módulo de inventario y productos
│       ├── repairs.php           # Módulo de servicio técnico
│       ├── repair-receipt.php    # Comprobante de reparación
│       ├── layaways.php          # Módulo de apartados
│       ├── clients.php           # Directorio de clientes
│       ├── businesses.php        # Gestión de negocios/sucursales
│       └── calculator.php        # Calculadora y ajuste de cotización
├── routes/
│   ├── web.php                   # Rutas de la aplicación web y vistas
│   └── api.php                   # Rutas API REST (e.g. consulta de tasa BCV)
├── tests/
│   ├── Feature/                  # Pruebas funcionales e integración HTTP con Guzzle
│   └── Unit/                     # Pruebas unitarias
├── .env.example                  # Plantilla de variables de entorno
├── .htaccess                     # Reglas de reescritura para servidor Apache
├── composer.json                 # Dependencias y autoloading PSR-4
├── index.php                     # Punto de entrada de la aplicación y contenedor de DI
└── phpunit.xml.dist              # Configuración del ejecutor de pruebas PHPUnit
```

---

## ⚙️ Requisitos del Entorno

1. **PHP**: Versión **8.4 o superior**.
2. **Extensiones de PHP requeridas**:
   - `pdo`
   - `pdo_sqlite` (o `pdo_mysql` si se usa base de datos MySQL)
   - `curl`
   - `mbstring`
   - `openssl`
3. **Composer**: Para instalación y gestión de dependencias.
4. **Servidor Web**: Apache (mediante XAMPP u otro entorno con `mod_rewrite` habilitado) o el servidor embebido de PHP para desarrollo local.

---

## 📥 Instalación y Puesta en Marcha

### 1. Clonar el repositorio
```bash
git clone https://github.com/fadrian06/android-evolucion-ss-fp.git
cd android-evolucion-ss-fp
```

### 2. Instalar dependencias de PHP
Ejecuta Composer para descargar las librerías:
```bash
composer install
```

### 3. Configuración de Variables de Entorno
Copia el archivo `.env.example` a `.env` (el sistema creará una copia automáticamente en la primera ejecución si no existe):
```bash
cp .env.example .env
```

Configura tus credenciales y parámetros en el archivo `.env`:
```ini
APP_NAME=Orion

APP_LOCALE=es
LOCALES_STRATEGY=header

BCRYPT_ROUNDS=12

# Conexión a Base de Datos (SQLite por defecto)
DB_CONNECTION=sqlite
DB_CHARSET=utf8
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database/database.sqlite
DB_USERNAME=root
DB_PASSWORD=

SESSION_LIFETIME=120

# Llave de API para obtener la cotización del BCV desde DolarVzla
# Obtén tu API key gratuita en: https://dolarvzla.com/settings
DOLARVZLA_KEY=tu_api_key_aqui
```

### 4. Base de Datos y Migraciones
No es necesario ejecutar comandos manuales de migración:
- El archivo `index.php` verifica y crea automáticamente el archivo `database/database.sqlite` si no existe.
- Todas las migraciones ubicadas en `database/migrations/` se ejecutan automáticamente de forma incremental al arrancar la aplicación.

### 5. Iniciar la Aplicación

#### Opción A: Servidor integrado de PHP (Recomendado para desarrollo rápido)
```bash
php -S localhost:8000 index.php
```
Accede desde tu navegador web a: `http://localhost:8000`

#### Opción B: Con XAMPP / Apache
1. Asegúrate de que el proyecto se encuentra en la carpeta `htdocs` de XAMPP (por ejemplo, `C:\xampp\htdocs\android-evolucion-ss-fp`).
2. Comprueba que el módulo `mod_rewrite` esté activo en Apache (`httpd.conf`).
3. Inicia Apache desde el panel de control de XAMPP.
4. Si configuras un VirtualHost o accedes a través de `http://localhost/android-evolucion-ss-fp`, el archivo `.htaccess` redirigirá todas las peticiones a `index.php`.

---

## 🧪 Pruebas Automatizadas

El proyecto cuenta con una batería de pruebas de integración con **PHPUnit**:

```bash
vendor/bin/phpunit
```

> [!NOTE]
> Las pruebas funcionales (`tests/Feature/`) utilizan Guzzle para enviar peticiones HTTP reales a `http://localhost`. Para ejecutarlas con éxito, asegúrate de tener el servidor web local activo y escuchando en el puerto configurado.

---

## 🗺️ Mapa de Rutas Principales

### Autenticación y Acceso
- `GET /ingresar` — Pantalla de inicio de sesión.
- `POST /ingresar` — Autenticar usuario.
- `GET /registrarse` — Registro de nuevo usuario administrador.
- `POST /registrarse` — Crear cuenta de usuario.
- `GET /cerrar-sesion` — Finalizar sesión.

### Negocios / Sucursales
- `GET /negocios` — Listado de negocios registrados.
- `POST /negocios` — Crear nueva sucursal.
- `POST /negocios/@id` — Actualizar información de la sucursal.
- `GET /negocios/@id/seleccionar` — Establecer sucursal activa.
- `GET /negocios/@id/eliminar` — Eliminar sucursal.

### Operación Diaria y Cotizaciones
- `GET /` — Panel de control principal (Dashboard).
- `GET /calculadora` — Calculadora de conversión y ajuste de cotización del día.
- `POST /calculadora/cotizacion` — Guardar tasa personalizada del día.
- `GET /api/bcv/exchange-rate` — Endpoint JSON para consultar la cotización actual del BCV.

### Inventario y Productos
- `GET /productos` — Catálogo de productos y stock disponible.
- `POST /productos` — Registrar nuevo producto o entrada de lote.
- `POST /productos/@id` — Actualizar datos del producto.
- `GET /productos/@id/eliminar` — Dar de baja producto.

### Clientes
- `GET /clientes` — Directorio de clientes.
- `POST /clientes` — Crear nuevo cliente.
- `POST /clientes/@id` — Modificar datos del cliente.
- `GET /clientes/@id/eliminar` — Eliminar cliente.

### Ventas y Facturación
- `GET /ventas` — Módulo de registro y listado de ventas.
- `POST /ventas` — Procesar y registrar venta con sus métodos de pago.
- `GET /ventas/@id` — Ver comprobante de venta (`invoice`).
- `POST /ventas/@id/pagar` — Registrar pago complementario.
- `POST /ventas/@id/anular` — Anular venta y restituir inventario.

### Apartados (Reservas)
- `GET /apartados` — Listado de apartados activos e históricos.
- `POST /apartados` — Crear nuevo apartado con abono inicial.
- `POST /apartados/@id/pagar` — Registrar abono a un apartado.
- `POST /apartados/@id/cancelar` — Cancelar apartado.

### Servicio Técnico (Reparaciones)
- `GET /reparaciones` — Listado de órdenes de servicio.
- `POST /reparaciones` — Crear orden de reparación.
- `GET /reparaciones/@id` — Ver comprobante de recepción y entrega de equipo (`repair-receipt`).
- `POST /reparaciones/@id/pagar` — Registrar abono o pago final de la reparación.

---

## 🔒 Seguridad y Buenas Prácticas

- **Contraseñas**: Hasheadas mediante Bcrypt con costo configurable (`BCRYPT_ROUNDS`).
- **Inyección SQL**: Consultas preparadas y parametrizadas mediante el ORM Eloquent y PDO.
- **Validación de Cotización**: Protección por middleware que impide transacciones desactualizadas frente a la fluctuación cambiaria.
- **Aislamiento Multi-tienda**: Integridad referencial que asocia estrictamente inventario, comprobantes y movimientos a la sucursal activa y al usuario autenticado.

---

## 📄 Licencia

Este proyecto es software privado desarrollado para la administración comercial y técnica de locales comerciales. Todos los derechos reservados.
