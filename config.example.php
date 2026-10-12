<?php
/**
 * ============================================================================
 *  PLANTILLA DE CONFIGURACIÓN - FREDMEL
 * ============================================================================
 *
 *  Copia este archivo como "config.php" y rellena los valores reales.
 *  Este archivo (config.example.php) SÍ puede subirse a repositorios
 *  porque NO contiene credenciales reales.
 * ============================================================================
 */

return [
    // Cuenta de correo que ENVÍA los mensajes (remitente SMTP).
    'smtp_username' => 'tu-correo@gmail.com',

    // Contraseña de aplicación de Gmail (NO tu contraseña normal).
    // Genera una en: https://myaccount.google.com/apppasswords
    'smtp_password' => 'xxxx xxxx xxxx xxxx',

    // Servidor SMTP y puerto. Para Gmail dejar como está.
    'smtp_host'     => 'smtp.gmail.com',
    'smtp_port'     => 587,

    // Correo que RECIBE las consultas del formulario de contacto.
    'contact_recipient' => 'destino@ejemplo.com',
];
