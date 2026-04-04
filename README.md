# Sistema de Gestión PQRSD — SENA

<div align="center">

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.6+-003545?style=flat-square&logo=mariadb&logoColor=white)](https://mariadb.org/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![License](https://img.shields.io/badge/License-MIT-22c55e?style=flat-square)](LICENSE)

**Sistema web para la gestión eficiente de Peticiones, Quejas, Reclamos, Sugerencias y Denuncias (PQRSD) del personal interno del SENA.**

[Instalación](#-instalación) · [Documentación](#-estructura-del-proyecto) · [Contribuir](#-contribuidores)

</div>

---

## ¿Qué es este proyecto?

La **Comisión de Personal del SENA** recibe un alto volumen de PQRSD que hace inviable su gestión con métodos tradicionales. Este sistema web reemplaza esos flujos con una plataforma estructurada que cubre el ciclo completo de cada caso: desde la radicación inicial hasta el cierre y la evaluación.

---

## Características

| Módulo | Descripción |
|--------|-------------|
| **Gestión de casos** | Flujo completo de PQRSD: radicación, seguimiento, cierre y evaluación. |
| **Control de acceso por roles** | Perfiles diferenciados para Administradores y Comisionados. |
| **Autenticación 2FA** | Verificación de doble factor en el acceso de usuarios. |
| **Notificaciones y correo** | Alertas internas y envíos SMTP automáticos (cambios de estado, recuperación de contraseña). |
| **Reportería** | Exportación de informes detallados en PDF y Excel. |
| **Panel de auditoría** | Monitoreo en tiempo real de acciones administrativas y operativas. |
| **Enrutamiento dinámico** | Rutas limpias y seguras gestionadas desde el backend. |

---

## Stack tecnológico

### Backend
- **PHP 8.2+** con [AltoRouter](https://github.com/dannyvankooten/AltoRouter) para el manejo de rutas.
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) para gestión segura de variables de entorno.
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) para envíos SMTP.
- [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) y [DomPDF](https://github.com/dompdf/dompdf) para generación de reportes.

### Base de datos
- **MariaDB 10.6+** con uso extensivo de:
  - **Stored Procedures** para lógica de reportes y caracterización de demanda.
  - **Eventos automatizados** (`ev_caso_caducado`, `ev_caducar_usuarios_vencidos`) para transición de estados.
  - **Triggers** para auditoría y consistencia de datos.

### Frontend
- HTML5, CSS3 y JavaScript puro con interfaces responsivas.

---

## Requisitos previos

- PHP >= 8.2 con extensiones `pdo`, `gd`, `zip` y `mbstring` habilitadas.
- MariaDB o MySQL.
- [Composer](https://getcomposer.org/) instalado globalmente.
- Servidor web (Apache/Nginx) con soporte para reescritura de URLs (`mod_rewrite`).

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/KoryCarrera/tu-repositorio.git
cd tu-repositorio
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar variables de entorno

Crea un archivo `.env` en la raíz del proyecto basándote en `.env.example`:

```env
# Base de datos
DB_HOST=localhost
DB_USER=root
DB_PASS=tu_password
DB_NAME=proyectosena_db

# SMTP (PHPMailer)
SMTP_HOST=smtp.ejemplo.com
SMTP_USER=tu_correo@ejemplo.com
SMTP_PASS=tu_password_smtp
```

### 4. Inicializar la base de datos

Importa el archivo `db.sql` en tu gestor preferido (phpMyAdmin, DBeaver, etc.).

> **Importante:** Activa el Event Scheduler de MySQL/MariaDB para que funcionen las caducidades automáticas de casos:
> ```sql
> SET GLOBAL event_scheduler = ON;
> ```

### 5. Levantar el servidor

Con Docker, XAMPP, o el servidor integrado de PHP:

```bash
php -S localhost:8000
```

---

## Estructura del proyecto

```
/
├── app/
│   ├── controllers/      # Lógica de negocio por módulo
│   ├── models/           # Acceso a datos y stored procedures
│   └── views/            # Plantillas HTML/PHP
├── config/               # Configuración de BD y entorno
├── public/               # Punto de entrada (index.php), assets
├── vendor/               # Dependencias Composer
├── db.sql                # Esquema completo con eventos y triggers
├── .env.example
└── composer.json
```

---

## Seguridad

- **Variables de entorno:** Credenciales fuera del código fuente con `phpdotenv`.
- **Autenticación 2FA:** Doble factor de verificación en el acceso.
- **Tokens con expiración:** Recuperación de contraseñas mediante tokens vinculados a BD con límite de tiempo.
- **Eventos automáticos:** Limpieza y transición de estados de casos abandonados sin intervención manual.

> Próxima iteración incluirá auditorías OWASP y refuerzo de controles de seguridad.

---

## Roadmap

### Completado ✅
- [x] Sistema completo de notificaciones en BD.
- [x] Envío de alertas y recuperaciones por email (PHPMailer).
- [x] Panel de auditoría con historial de procesos (`monitoreo`).
- [x] Generador de reportes en PDF y Excel.
- [x] Autenticación de doble factor (2FA).
- [x] Enrutamiento backend moderno con AltoRouter.

### En progreso / Próximo
- [ ] Gestión avanzada de evidencias multimedia adjuntas a los casos.
- [ ] Auditorías OWASP y endurecimiento de seguridad.
- [ ] Refinamiento de UI/UX.

---

## Contribuidores

| Nombre | Rol | GitHub |
|--------|-----|--------|
| Kory Carrera | Líder de proyecto · FullStack | [@KoryCarrera](https://github.com/KoryCarrera) |
| Zack-Xd | Desarrollador Backend | [@Zack-Xd](https://github.com/Zack-Xd) |
| Juan Correal | Desarrollador Frontend | [@juan-correal](https://github.com/juan-correal) |
| Simón Peláez | Analista de BD · Desarrollador Backend | [@pelaezgonzalezsimon919-cyber](https://github.com/pelaezgonzalezsimon919-cyber) |

> Proyecto académico — Formación Técnica SENA 2025.

---

## Agradecimientos

- **Comisión de Personal del SENA** — Cliente y validador de requisitos.
- **Instructores SENA** — Guía y acompañamiento técnico.
- Comunidad open source por las librerías utilizadas: DomPDF, PhpSpreadsheet, AltoRouter, PHPMailer.

---

## Licencia

Distribuido bajo la [Licencia MIT](LICENSE).