# FREDMEL Abogados — Sitio Web

Copyright (c) 2026 FREDMEL / Rogelio Noriega Luna
Todos los derechos reservados.

Queda estrictamente prohibida la reproducción, distribución, modificación o uso comercial 
de este código fuente y de los diseños asociados, total o parcialmente, sin la autorización 
previa y por escrito del autor.


Sitio web institucional con formulario de contacto que envía correos vía SMTP
(Gmail) usando [PHPMailer](https://github.com/PHPMailer/PHPMailer).


---

## 📁 Estructura del proyecto

```
FREDMEL_Final/
├── index.html              Página de inicio
├── nosotros.html           Página "Nosotros"
├── Servicios.html          Página de servicios
├── Contacto.html           Página de contacto (con el formulario)
├── TyC.html                Términos y condiciones
├── procesar-contacto.php   Procesa el formulario y envía el correo
├── config.php              ⚠️ Credenciales SMTP (NO público, ver seguridad)
├── config.example.php      Plantilla de configuración (sin datos reales)
├── .htaccess               Reglas de seguridad para Apache (GoDaddy)
├── css/                    Hojas de estilo
├── images/ , data/         Imágenes y recursos
├── PHPMailer/ , src/        Librería PHPMailer
└── README.md               Este archivo
```

---

## ⚙️ Configuración

1. Copia la plantilla y renómbrala:

   ```
   config.example.php  ->  config.php
   ```

2. Edita `config.php` y coloca los valores reales:

   ```php
   return [
       'smtp_username'     => 'tu-correo@gmail.com',
       'smtp_password'     => 'xxxx xxxx xxxx xxxx',   // Contraseña de APLICACIÓN de Gmail
       'smtp_host'         => 'smtp.gmail.com',
       'smtp_port'         => 587,
       'contact_recipient' => 'destino@ejemplo.com',   // Correo que recibe las consultas
   ];
   ```

### Contraseña de aplicación de Gmail

No uses la contraseña normal de la cuenta. Genera una **contraseña de aplicación**:

1. Activa la verificación en dos pasos en la cuenta de Google.
2. Entra a <https://myaccount.google.com/apppasswords>.
3. Crea una contraseña de aplicación y cópiala en `smtp_password`.

---

## 🚀 Despliegue en GoDaddy (cPanel / FTP)

1. Sube **todo el contenido del sitio** a la carpeta `public_html`.

2. **Recomendado (máxima seguridad):** sube `config.php` **un nivel arriba**
   de `public_html`, por ejemplo:

   ```
   /home/tu_usuario/config.php        <-- aquí (fuera de la web, inaccesible)
   /home/tu_usuario/public_html/      <-- aquí va el sitio
   ```

   El código ya busca `config.php` en esa ubicación automáticamente
   (`__DIR__ . '/../config.php'`). No hay que cambiar nada más.

3. **Alternativa:** si prefieres dejar `config.php` dentro de `public_html`,
   también funciona porque el `.htaccess` bloquea su acceso por navegador.
   Aun así, la opción del punto 2 es más robusta.

4. Verifica que el archivo `.htaccess` se haya subido (a veces los clientes
   FTP ocultan los archivos que empiezan con punto).

5. Confirma que la versión de PHP en GoDaddy sea **7.4 o superior**
   (recomendado PHP 8.x), ya que el código usa `declare(strict_types=1)`
   y sintaxis moderna.

---

## ✅ Prueba del formulario

1. Abre `Contacto.html` en el navegador del sitio ya publicado.
2. Llena y envía el formulario.
3. Resultados posibles (vía parámetro `?status=` en la URL):
   - `success`        → correo enviado correctamente.
   - `error_datos`    → faltan datos obligatorios o el email no es válido.
   - `error_envio`    → fallo al enviar (revisar credenciales / configuración).

Si aparece `error_envio`, revisa el log de errores de PHP en cPanel; ahí se
registran los detalles del fallo de envío.

---
