# Despliegue de Revista Digital — pasos con tu hosting ya contratado

Este paquete ya viene sin `vendor/` (no lo usa la app en producción — PHPMailer
se carga directo desde la carpeta `PHPMailer/`) y sin `.github/modernize/` ni
`.vscode/`. Son solo los archivos que tu servidor necesita.

## 1. Subir el código
Sube todo el contenido de esta carpeta a `public_html` (o la subcarpeta de tu
dominio) usando el Gestor de Archivos de cPanel, o clona tu repo de GitHub con
Git™ Version Control si tu panel lo ofrece.

## 2. Crear la base de datos
cPanel → MySQL® Databases:
- Crea la base de datos (te quedará con prefijo, ej. `usuario_revista_digital2`).
- Crea un usuario con contraseña fuerte.
- Asígnale **todos los privilegios** sobre esa base.

## 3. Importar el esquema
phpMyAdmin → selecciona la base → pestaña **Importar** → sube
`revista_digital2.sql` de este paquete.

## 4. Configurar la conexión
Edita el archivo `htaccess-PRODUCCION.txt` (incluido en esta carpeta) y
reemplaza:
- `TU_HOST_AQUI` → normalmente `localhost` en hosting compartido
- `TU_USUARIO_AQUI` → el usuario que creaste en el paso 2 (con prefijo)
- `TU_PASSWORD_AQUI` → la contraseña de ese usuario
- `TU_BASEDEDATOS_AQUI` → el nombre de la base (con prefijo)

Luego **renómbralo a `.htaccess`** y súbelo solo al servidor de producción
(no lo uses en XAMPP local — ver nota dentro del archivo). No necesitas tocar
ningún archivo `.php`: `conexion.php` ya lee estos valores automáticamente
con `getenv()`.

## 5. Probar
Abre la URL pública en este orden:
1. `ddp/index.php` — debe cargar sin errores.
2. El login del panel admin (`ddp/admin/`).
3. Boletines, podcast y reportajes.

Si algo falla, activa temporalmente `display_errors` o prueba la conexión
mysqli aislada para saber en qué capa está el problema (código, base de
datos, o variables de entorno).

## Usuario administrador

Usuario admin creado en revista_digital2.

Email: admin@ddp.pe
Contraseña: 461c4edb8dce
Rol: admin