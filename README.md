# Proyecto de Registro de Archivos XML

Este proyecto ha sido desarrollado en **Laravel** y está diseñado para gestionar el registro y procesamiento de archivos XML de manera eficiente.

## Características principales
- **Procesamiento en segundo plano:** Utiliza servicios y colas de trabajo para manejar las operaciones de almacenamiento y extracción de datos desde los archivos XML.
- **Extracción de datos:** Analiza los archivos XML para extraer información relevante y almacenarla en una base de datos MySQL local.

---

## Requisitos previos
Antes de comenzar, asegúrate de tener instalados los siguientes componentes en tu entorno:
- PHP 8.1 o superior
- Composer
- Node.js y npm
- MySQL
- Servidor web local (como XAMPP o Laravel Valet)

---

## Pasos para levantar el proyecto

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/TU_USUARIO/TU_REPOSITORIO.git
   cd TU_REPOSITORIO
   ```

2. **Instalar las dependencias de PHP**
   ```bash
   composer install
   ```

3. **Configurar el archivo `.env`**
   - Copia el archivo `.env.example` a `.env`:
     ```bash
     cp .env.example .env
     ```
   - Configura las credenciales de tu base de datos MySQL local.

4. **Migrar la base de datos y cargar los seeders**
   ```bash
   php artisan migrate --seed
   ```

5. **Compilar los assets del frontend**
   ```bash
   npm install
   npm run dev
   ```

6. **Iniciar el procesamiento en segundo plano**
   Ejecuta el siguiente comando para manejar las colas de trabajo:
   ```bash
   php artisan queue:work
   ```

7. **Iniciar el servidor local**
   ```bash
   php artisan serve
   ```

---

## Credenciales de acceso

Para iniciar sesión en la aplicación, utiliza las siguientes credenciales predefinidas:

- **Usuario 1:**
  - Correo: `usuario1@example.com`
  - Contraseña: `password1234`

- **Usuario 2:**
  - Correo: `usuario2@example.com`
  - Contraseña: `password12345`

> **Nota:** Estas cuentas se crean automáticamente al ejecutar los seeders.

---

## Acceso a la aplicación

1. Abre tu navegador y ve a la siguiente ruta:
   ```
   http://localhost:8000/
   ```

2. Inicia sesión con las credenciales proporcionadas.

¡El proyecto estará listo para usar!

---

## Notas adicionales
- Si tienes problemas con las colas de trabajo, verifica que tu configuración de `queue` en `.env` sea correcta.
- Recuerda que los archivos XML se procesan automáticamente al subirlos.

Si necesitas más ayuda, consulta la documentación oficial de Laravel o contacta al desarrollador del proyecto.

