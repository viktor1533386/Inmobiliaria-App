<?php
// ============================================================
//  CORE: Mailer – Envío de correos usando PHPMailer
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once APP_ROOT . '/core/phpmailer/Exception.php';
require_once APP_ROOT . '/core/phpmailer/PHPMailer.php';
require_once APP_ROOT . '/core/phpmailer/SMTP.php';

class Mailer {

    // Configuración SMTP estática para este proyecto
    private static string $host = 'smtp.gmail.com';
    private static string $username = 'viktor16412@gmail.com'; // Correo que envía
    private static string $password = 'sjbd xaqh khsx cwpe';     // Contraseña de aplicación
    private static string $fromName = 'Hogar Ideal Perú';
    private static int $port = 587;

    /**
     * Enviar un correo electrónico básico.
     *
     * @param string $to Email del destinatario
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo del correo en HTML
     * @return bool True si se envió, false si falló
     */
    public static function send(string $to, string $subject, string $body): bool {
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host       = self::$host;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::$username;
            $mail->Password   = self::$password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = self::$port;
            $mail->Timeout    = 10; // Timeout de 10 segundos
            $mail->CharSet    = 'UTF-8';

            // Remitente y destinatario
            $mail->setFrom(self::$username, self::$fromName);
            $mail->addAddress($to);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Logear error si es necesario
            error_log("No se pudo enviar el correo a $to. Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
