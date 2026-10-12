<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

const CONTACT_PAGE = 'Contacto.html';

/**
 * Rutas donde se buscará el archivo de configuración con las credenciales.
 * Se usa la primera que exista. El orden va de la más segura (fuera de la
 * carpeta pública) a la de respaldo (junto al proyecto).
 *
 * En GoDaddy, lo recomendable es subir config.php UN NIVEL ARRIBA de
 * public_html. Si lo haces, la primera ruta de la lista lo encontrará.
 */
const CONFIG_PATHS = [
    __DIR__ . '/../config.php',   // Fuera de public_html (más seguro).
    __DIR__ . '/config.php',      // Junto al proyecto (protegido por .htaccess).
];

function redirectToContact(string $status): void
{
    header('Location: ' . CONTACT_PAGE . '?status=' . rawurlencode($status), true, 303);
    exit;
}

/**
 * Carga la configuración de forma segura.
 * Prioridad:
 *   1. Variables de entorno (si están definidas en el servidor).
 *   2. Archivo config.php (fuera o dentro del proyecto).
 */
function loadConfig(): array
{
    $config = [
        'smtp_username'     => getenv('SMTP_USERNAME') ?: '',
        'smtp_password'     => getenv('SMTP_PASSWORD') ?: '',
        'smtp_host'         => getenv('SMTP_HOST') ?: '',
        'smtp_port'         => getenv('SMTP_PORT') ?: '',
        'contact_recipient' => getenv('CONTACT_RECIPIENT') ?: '',
    ];

    // Si falta algo en las variables de entorno, se completa con config.php.
    foreach (CONFIG_PATHS as $path) {
        if (is_file($path)) {
            $fileConfig = require $path;
            if (is_array($fileConfig)) {
                foreach ($fileConfig as $key => $value) {
                    if (($config[$key] ?? '') === '' && $value !== '') {
                        $config[$key] = $value;
                    }
                }
            }
            break;
        }
    }

    // Valores por defecto razonables.
    $config['smtp_host'] = $config['smtp_host'] !== '' ? $config['smtp_host'] : 'smtp.gmail.com';
    $config['smtp_port'] = (int) ($config['smtp_port'] !== '' ? $config['smtp_port'] : 587);

    return $config;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . CONTACT_PAGE, true, 303);
    exit;
}

$nombre = trim((string) ($_POST['nombre'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$telefono = trim((string) ($_POST['telefono'] ?? ''));
$asunto = trim((string) ($_POST['asunto'] ?? ''));
$mensaje = trim((string) ($_POST['mensaje'] ?? ''));

if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $mensaje === '') {
    redirectToContact('error_datos');
}

// Las credenciales se cargan desde variables de entorno o desde config.php,
// nunca escritas directamente en este archivo.
$config = loadConfig();
$smtpUsername = (string) $config['smtp_username'];
$smtpPassword = (string) $config['smtp_password'];
$contactRecipient = (string) $config['contact_recipient'];

if (!filter_var($smtpUsername, FILTER_VALIDATE_EMAIL) || $smtpPassword === '' || !filter_var($contactRecipient, FILTER_VALIDATE_EMAIL)) {
    error_log('FREDMEL SMTP is not configured: set a valid sender email, app password and recipient (config.php or env vars).');
    redirectToContact('error_envio');
}

$areas = [
    'defensa-fiscal' => 'Derecho Fiscal',
    'registro-marca' => 'Registro de Marca (IMPI)',
    'corporativo' => 'Derecho Mercantil',
    'civil-familia' => 'Derecho Civil y Familia',
    'laboral' => 'Derecho Laboral',
    'otro' => 'Otro Asunto',
];
$asuntoLegible = $areas[$asunto] ?? 'Consulta general';

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$nombreHtml = $escape($nombre);
$emailHtml = $escape($email);
$telefonoHtml = $escape($telefono !== '' ? $telefono : 'No proporcionado');
$asuntoHtml = $escape($asuntoLegible);
$mensajeHtml = nl2br($escape($mensaje), false);

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = (string) $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) $config['smtp_port'];
    $mail->CharSet = PHPMailer::CHARSET_UTF8;

    $mail->setFrom($smtpUsername, 'Sitio Web FREDMEL');
    $mail->addAddress($contactRecipient, 'FREDMEL Abogados');
    $mail->addReplyTo($email, $nombre);

    $mail->isHTML(true);
    $mail->Subject = 'Nueva Consulta Web: ' . $asuntoLegible;
    $mail->Body = "<!doctype html><html lang=\"es\"><body style=\"font-family:Arial,sans-serif;padding:20px;background:#f4f4f4\">
      <div style=\"max-width:600px;margin:auto;background:#fff;padding:30px;border-radius:8px;border-top:4px solid #c5a059\">
        <h2 style=\"color:#1a1a1a;margin-top:0\">Nueva consulta recibida desde la web</h2>
        <hr style=\"border:0;border-top:1px solid #eee\">
        <p><strong>Cliente:</strong> {$nombreHtml}</p>
        <p><strong>Correo:</strong> <a href=\"mailto:{$emailHtml}\">{$emailHtml}</a></p>
        <p><strong>Teléfono:</strong> {$telefonoHtml}</p>
        <p><strong>Área de interés:</strong> {$asuntoHtml}</p>
        <hr style=\"border:0;border-top:1px solid #eee\">
        <p><strong>Mensaje:</strong></p>
        <div style=\"background:#fafafa;padding:15px;border-left:3px solid #c5a059;margin-top:10px;color:#333\">{$mensajeHtml}</div>
      </div>
    </body></html>";
    $mail->AltBody = "Nueva consulta recibida desde FREDMEL\n\nCliente: {$nombre}\nCorreo: {$email}\nTeléfono: " . ($telefono !== '' ? $telefono : 'No proporcionado') . "\nÁrea de interés: {$asuntoLegible}\n\nMensaje:\n{$mensaje}";

    $mail->send();
    redirectToContact('success');
} catch (Exception $exception) {
    error_log('FREDMEL contact email failed: ' . $mail->ErrorInfo);
    redirectToContact('error_envio');
}
