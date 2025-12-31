# 🌐 Seminario PITI 2025 - Semana de la Ciencia

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Status](https://img.shields.io/badge/Status-Active-success)](https://seminario.clasespiti.co/)

> Plataforma web oficial para el evento **Semana de la Ciencia 2025** organizado por el Programa de Ingeniería en Telecomunicaciones e Informática (PITI).

## 📋 Descripción

**Seminario PITI 2025** es una aplicación web moderna diseñada para gestionar y promover la Semana de la Ciencia, un evento educativo enfocado en tecnologías emergentes y transformación digital en Colombia. La plataforma permite a estudiantes, profesionales y entusiastas de la tecnología:

- 🎓 Registrarse gratuitamente para el evento
- 📅 Consultar el horario de conferencias y talleres
- 🏆 Descargar certificados de participación
- 📰 Mantenerse informados con las últimas noticias del evento
- 👥 Conocer a expertos nacionales, egresados destacados y futuros talentos

### 🎯 Temas del Evento

El seminario cubre las siguientes áreas tecnológicas de vanguardia:

- **Inteligencia Artificial y Ética en Telecomunicaciones**
- **Redes 5G y su Impacto en Colombia**
- **Ciberseguridad para Sectores Públicos y Privados**
- **Computación en la Nube y Arquitectura de Redes Modernas**
- **Internet de las Cosas (IoT) y Ciudades Inteligentes**
- **Tecnologías Emergentes y Transformación Digital**
- **Emprendimiento Digital y Casos de Éxito en Tecnología**

## ✨ Características

- 🎨 **Interfaz Moderna**: Diseño responsivo con animaciones dinámicas y efectos visuales atractivos
- 📝 **Sistema de Preinscripción**: Registro en línea para participantes
- 🎬 **Hero Section Dinámico**: Efecto typewriter con palabras rotativas (Innovación, Conectividad, Futuro Digital)
- 📄 **Generación de Certificados**: Sistema automatizado con FPDF/FPDI
- 🔐 **Panel de Administración**: Login seguro para gestión de contenido
- 📱 **Diseño Responsivo**: Optimizado para dispositivos móviles, tablets y desktop
- 🎥 **Video Background**: Experiencia inmersiva con video de fondo

## 🛠️ Tecnologías

### Frontend
- **HTML5** - Estructura semántica
- **CSS3** - Estilos modernos con animaciones
- **JavaScript (Vanilla)** - Interactividad y efectos dinámicos
- **Bootstrap Icons** - Iconografía

### Backend
- **PHP 7.4+** - Lógica del servidor
- **MySQL** - Base de datos relacional

### Librerías
- **FPDF** - Generación de PDFs
- **FPDI** - Manipulación de plantillas PDF

## 📁 Estructura del Proyecto

```
WebSeminarioPITI/
├── assets/                    # Recursos estáticos (deprecado, migrado a src/)
├── SQL/                       # Scripts de base de datos
├── src/
│   ├── assets/
│   │   ├── css/              # Hojas de estilo
│   │   │   ├── base/         # Estilos base
│   │   │   ├── components/   # Componentes reutilizables
│   │   │   ├── sections/     # Estilos por sección
│   │   │   └── utilities/    # Utilidades CSS
│   │   ├── fonts/            # Fuentes personalizadas
│   │   ├── img/              # Imágenes y logos
│   │   ├── js/               # Scripts JavaScript
│   │   └── video/            # Videos de fondo
│   ├── inc/                  # Includes PHP (header, footer, head)
│   ├── Pages/                # Páginas del sitio
│   └── uploads/              # Sistema de carga y generación de PDFs
│       ├── Egresados/        # Contenido de egresados
│       ├── Nacional/         # Contenido nacional
│       ├── Pages/            # Páginas dinámicas
│       └── libs/             # Librerías (FPDF, FPDI)
├── index.php                 # Página principal
├── ruta.php                  # Configuración de rutas
└── README.md                 # Este archivo
```

## 🚀 Instalación

### Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Composer (opcional)

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/Stivinsonweb/SeminarioPITI2025.git
   cd SeminarioPITI2025
   ```

2. **Configurar la base de datos**
   ```bash
   # Importar el script SQL en tu servidor MySQL
   mysql -u tu_usuario -p nombre_base_datos < SQL/database.sql
   ```

3. **Configurar la ruta base**
   
   Edita el archivo `ruta.php` y ajusta la constante `RUTA` según tu entorno:
   ```php
   <?php
   const RUTA = 'http://localhost/WebSeminarioPITI/'; // Para desarrollo local
   // const RUTA = 'https://seminario.clasespiti.co/'; // Para producción
   ?>
   ```

4. **Configurar la conexión a la base de datos**
   
   Edita el archivo de configuración de base de datos (ubicado en `src/inc/` o similar) con tus credenciales.

5. **Configurar permisos**
   ```bash
   # En Linux/Mac
   chmod -R 755 src/uploads/
   
   # Asegúrate de que el servidor web tenga permisos de escritura
   chown -R www-data:www-data src/uploads/
   ```

6. **Iniciar el servidor**
   
   **Opción 1: Servidor PHP integrado (desarrollo)**
   ```bash
   php -S localhost:8000
   ```
   
   **Opción 2: Apache/Nginx**
   - Configura un VirtualHost apuntando a la carpeta del proyecto
   - Accede via `http://localhost/WebSeminarioPITI/`

## 📝 Uso

### Para Usuarios

1. Visita la página principal
2. Explora los temas del evento y el horario
3. Haz clic en "Preinscripción Gratuita"
4. Completa el formulario de registro
5. Después del evento, descarga tu certificado desde "DESCARGA CERTIFICADO"

### Para Administradores

1. Accede al panel de administración via `/LOGIN`
2. Usa tus credenciales de administrador
3. Gestiona preinscripciones, noticias y contenido del evento
4. Genera y descarga reportes de participantes

## 🎨 Personalización

### Modificar el Hero Section

Las palabras rotativas se pueden editar en `index.php`:

```javascript
this.texts = [
  "Innovación", 
  "Conectividad",
  "Futuro Digital",
  "By PITI",
];
```

### Cambiar Temas del Evento

Edita las tarjetas de temas en `index.php` en la sección `.temas-grid`.

### Estilos Personalizados

Los estilos están organizados en:
- `src/assets/css/base/` - Variables, reset, tipografía
- `src/assets/css/components/` - Botones, cards, formularios
- `src/assets/css/sections/` - Hero, footer, navegación

## 🤝 Contribuciones

Las contribuciones son bienvenidas y apreciadas. Para contribuir:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/NuevaCaracteristica`)
3. Commit tus cambios (`git commit -m 'Add: Nueva característica'`)
4. Push a la rama (`git push origin feature/NuevaCaracteristica`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más detalles.

```
MIT License

Copyright (c) 2025 Programa de Ingeniería en Telecomunicaciones e Informática (PITI)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## 👥 Equipo

**Programa de Ingeniería en Telecomunicaciones e Informática (PITI)**

- 🌐 Sitio web: [https://seminario.clasespiti.co/](https://seminario.clasespiti.co/)
- 📧 Email: contacto@clasespiti.co

## 🙏 Agradecimientos

Con el apoyo de:
- Universidad e instituciones comprometidas con la innovación tecnológica
- Expertos nacionales y profesionales del sector
- Egresados destacados del programa
- Comunidad estudiantil PITI

---

<p align="center">
  <strong>Hecho con ❤️ por el equipo PITI</strong>
</p>

<p align="center">
  <sub>Evento: 30 al 31 de octubre 2025</sub>
</p>
