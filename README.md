# 🏎️ F1 2026 Manager

> Sistema web de gestión de la temporada de Fórmula 1 2026 — Proyecto Final DAM2V

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/Licencia-MIT-green?style=flat-square)

---

## 📸 Vista previa

| Dashboard | Pilotos | Resultados |
|-----------|---------|------------|
| Mundial de pilotos y constructores en tiempo real | CRUD completo con modal y radiobuttons | Pestañas Finalizadas / Pendientes |

---

## 📋 Descripción

**F1 2026 Manager** es una aplicación web full-stack para gestionar todos los datos de la temporada de Fórmula 1 2026. Permite administrar pilotos, escuderías, circuitos, carreras, resultados y neumáticos desde un panel centralizado con diseño oscuro inspirado en la identidad visual de la F1.

El dashboard actualiza automáticamente el **Mundial de Pilotos** y el **Mundial de Constructores** a medida que se registran resultados, usando los datos reales de la parrilla 2026.

---

## ✨ Funcionalidades

- 🔐 **Autenticación completa** — Login, registro, sesiones, cookies "Recordarme" 7 días
- 👥 **Control de roles** — `admin` puede crear/editar/borrar; `user` solo puede ver
- 🏆 **Mundial de Pilotos y Constructores** — Clasificación en tiempo real basada en resultados
- 📅 **Próximas carreras** — Dashboard con los siguientes GPs del calendario
- 🌤️ **API del clima** — Condiciones meteorológicas en tiempo real de cada circuito (Open-Meteo)
- ✅ **Validación JS** — Todos los formularios validados en cliente sin usar `required` HTML
- 🔔 **Confirmaciones SweetAlert2** — Antes de borrar o editar cualquier registro
- 📱 **Responsive** — Adaptado a móvil y escritorio con Bootstrap 5

---

## 🗃️ Base de datos

7 tablas con datos reales de la temporada 2026:

| Tabla | Registros | Descripción |
|-------|-----------|-------------|
| `usuarios` | — | Cuentas con roles admin/user |
| `escuderias` | 11 | Todos los equipos de la parrilla |
| `pilotos` | 22 | Parrilla completa con estadísticas |
| `circuitos` | 25 | Calendario + coordenadas para API clima |
| `carreras` | 24 | GPs con estado y vuelta rápida |
| `resultados` | — | Clasificación por carrera y puntos |
| `neumaticos` | 5 | Compuestos Pirelli 2026 |

---

## 🚀 Instalación

### Requisitos
- XAMPP (Apache + MySQL + PHP 8.x)
- PHP con extensiones `PDO` y `PDO_MySQL`
- Navegador moderno
- Conexión a internet (Bootstrap CDN + API clima)

### Pasos

**1. Clonar el repositorio**
```bash
git clone https://github.com/TU_USUARIO/F1_2026.git
```

**2. Copiar en htdocs**
```
C:\xampp\htdocs\F1_2026\
```

**3. Crear la base de datos**

Abrir phpMyAdmin → Nueva BD → nombre: `f1_2026` → cotejamiento `utf8mb4_general_ci`

**4. Importar el SQL**

En la BD `f1_2026` → pestaña **Importar** → seleccionar `f1_2026.sql`

**5. Abrir en el navegador**
```
http://localhost/F1_2026/
```

---

## 🔑 Credenciales por defecto

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | `admin@f1.com` | `password` |
| Usuario | `carlos@f1.com` | `password` |

> ⚠️ Cambia las contraseñas tras la primera instalación desde **Mi Perfil**

---

## 🏗️ Estructura del proyecto

```
F1_2026/
├── config/
│   ├── conexion.php       # Conexión PDO + session_start()
│   ├── config.php         # Constantes: APP_NAME, BASE_URL, WEATHER_API
│   └── auth.php           # Guard de autenticación + control de roles
├── includes/
│   ├── header.php         # Navbar con usuario y rol
│   └── footer.php         # Scripts Bootstrap y SweetAlert2
├── assets/
│   ├── css/styles.css     # Tema oscuro con variables CSS
│   └── js/validaciones.js # Validaciones JS y confirmaciones
├── home.php               # Dashboard con mundiales
├── pilotos.php            # CRUD pilotos
├── escuderias.php         # CRUD escuderías
├── circuitos.php          # CRUD circuitos + API clima
├── carreras.php           # CRUD carreras
├── resultados.php         # CRUD resultados (Finalizadas / Pendientes)
├── neumaticos.php         # CRUD neumáticos
├── perfil.php             # Edición de perfil y contraseña
├── login.php              # Autenticación
├── registro.php           # Registro de usuarios
├── logout.php             # Cierre de sesión
└── f1_2026.sql            # Volcado completo de la BD
```

---

## 🔒 Seguridad

| Medida | Implementación |
|--------|---------------|
| Contraseñas | `password_hash()` bcrypt + `password_verify()` |
| Inyección SQL | Sentencias preparadas PDO con parámetros nombrados |
| XSS | `htmlspecialchars()` en toda salida de datos |
| Control de acceso | `auth.php` en todas las páginas privadas |
| Roles | `solo_admin()` bloquea acciones en servidor |
| Sesiones | Destrucción completa en logout |
| Cookies | Solo email, 7 días, eliminada en logout |

---

## 🌤️ API externa — Open-Meteo

Se integra la API gratuita [Open-Meteo](https://open-meteo.com) para mostrar las condiciones meteorológicas en tiempo real de cada circuito. No requiere clave de API.

```
https://api.open-meteo.com/v1/forecast?latitude={lat}&longitude={lon}
  &current=temperature_2m,relative_humidity_2m,rain,wind_speed_10m,weather_code
```

Devuelve temperatura, humedad, lluvia, viento y código WMO que se interpreta como **Seco / Mojado / Mixto**.

---

## 🛠️ Tecnologías

| Tecnología | Uso |
|------------|-----|
| PHP 8.x | Backend y lógica de negocio |
| MySQL 8.x + PDO | Base de datos relacional |
| Bootstrap 5.3 | Diseño responsive |
| Bootstrap Icons 1.11 | Iconografía |
| SweetAlert2 11 | Alertas y confirmaciones JS |
| Open-Meteo API | Datos meteorológicos |

---

## 📄 Licencia

MIT — libre para uso educativo y personal.

---

<p align="center">
  Hecho con ❤️ para el Proyecto Final DAM2V · 2025/2026
</p>
