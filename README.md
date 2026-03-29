# SAGD – Sistema de Almacenamiento y Gestión de Datos
## Iglesia Cristiana Emanuel – La Gabriela

---

## 📁 Estructura del proyecto

```
SAGD/
├── config/
│   └── database.php          # Configuración de conexión MySQL
├── includes/
│   └── funciones.php         # Funciones auxiliares y autenticación
├── admin/
│   ├── dashboard.php         # Panel principal del administrador
│   ├── miembros.php          # CRUD de miembros y asistentes
│   ├── anuncios.php          # CRUD de anuncios
│   ├── cronograma.php        # CRUD del cronograma semanal
│   └── actividades.php       # CRUD de actividades especiales
├── sql/
│   └── sagd_db.sql           # Script para crear la base de datos
├── index.php                 # Página pública de bienvenida
├── login.php                 # Página de inicio de sesión
├── logout.php                # Cerrar sesión
└── logo_iglesia.png          # Logo de la iglesia
```

---

## ⚙️ Requisitos

- PHP 8.0 o superior
- MySQL 5.7 o superior
- Servidor web: Apache (XAMPP/WAMP) o Nginx
- Extensiones PHP: `pdo`, `pdo_mysql`, `mbstring`

---

## 🚀 Instalación paso a paso

### 1. Clonar o copiar el proyecto
Copia la carpeta `SAGD/` dentro de la carpeta raíz de tu servidor:
- **XAMPP (Windows):** `C:/xampp/htdocs/SAGD/`
- **WAMP:** `C:/wamp64/www/SAGD/`
- **Linux:** `/var/www/html/SAGD/`

### 2. Crear la base de datos
1. Abre **phpMyAdmin** (http://localhost/phpmyadmin)
2. Ve a la pestaña **SQL**
3. Copia y pega el contenido de `sql/sagd_db.sql`
4. Haz clic en **Ejecutar**

Esto creará la base de datos `sagd_db` con todas las tablas y datos de prueba.

### 3. Configurar la conexión
Edita el archivo `config/database.php` con tus credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sagd_db');
define('DB_USER', 'root');      // Tu usuario MySQL
define('DB_PASS', '');          // Tu contraseña MySQL
```

### 4. Ejecutar el proyecto
Abre tu navegador y entra a:
```
http://localhost/SAGD/
```

---

## 🔐 Credenciales de acceso por defecto

| Campo    | Valor                  |
|----------|------------------------|
| Correo   | admin@iglesia.com      |
| Contraseña | Admin123*            |

> ⚠️ Cambia la contraseña después del primer ingreso.

---

## 📋 Módulos del sistema

| Módulo          | Acceso       | Descripción                                      |
|-----------------|-------------|--------------------------------------------------|
| Página pública  | Todos        | Anuncios, cronograma y actividades visibles       |
| Login           | Todos        | Inicio de sesión con correo y contraseña          |
| Dashboard       | Admin        | Resumen general con estadísticas                  |
| Miembros        | Admin        | Registrar, editar, buscar y eliminar miembros     |
| Anuncios        | Admin        | Crear, editar y eliminar anuncios                 |
| Cronograma      | Admin        | Gestionar el cronograma semanal                   |
| Actividades     | Admin        | Gestionar actividades y eventos especiales        |

---

## 🔒 Seguridad implementada

- Autenticación con sesiones PHP seguras (HttpOnly, SameSite)
- Contraseñas cifradas con `password_hash()` (bcrypt)
- Prepared statements con PDO para prevenir SQL Injection
- Sanitización de entradas con `htmlspecialchars()`
- Expiración automática de sesión (1 hora)
- Protección de rutas administrativas

---

## 🧪 Pruebas unitarias recomendadas

1. **Login con credenciales incorrectas** → debe mostrar mensaje de error
2. **Login con credenciales correctas** → debe redirigir al dashboard
3. **Registrar miembro sin campos obligatorios** → debe mostrar error
4. **Buscar miembro por nombre/documento** → debe retornar resultados en < 3s
5. **Eliminar miembro** → debe desactivarlo (borrado lógico)
6. **Sesión expirada** → debe redirigir al login con mensaje

---

## 🛠️ Tecnologías utilizadas

- **Backend:** PHP 8 + PDO
- **Base de datos:** MySQL
- **Frontend:** HTML5 + CSS3 + JavaScript vanilla
- **Tipografías:** Google Fonts (Cinzel + Lato)
- **Servidor:** Apache (XAMPP)
- **Control de versiones:** Git

---

## 📌 Historias de usuario cubiertas

- ✅ HU-01: Inicio de sesión con usuario y contraseña
- ✅ HU-02: Administrar datos de miembros y asistentes (CRUD completo)
- ✅ HU-03: Buscador de datos en el sistema
- ✅ HU-04: Visualización de anuncios, cronogramas y actividades (público)
- ✅ HU-05: Eliminación o modificación de datos

---

*Desarrollado para la evidencia GA8-220501096-AA1-EV01 – SENA Ficha 2977457*
