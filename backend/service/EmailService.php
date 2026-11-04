<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";

class EmailService {

    public function enviarRecuperacao($destinatario, $token) {
        $mail = new PHPMailer(true);

        try {
            //configurar SMTP
            $mail->isSMTP();

            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';
            $mail->Timeout = 15;

            $mail->Host = getenv("SMTP_HOST");
            $mail->SMTPAuth = true;
            $mail->Username = getenv("SMTP_USER");
            $mail->Password = getenv("SMTP_PASSWORD");
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) getenv("SMTP_PORT");

            //configurar mensagem
            $mail->CharSet = "UTF-8";
            $mail->setFrom(
                getenv("SMTP_FROM"),
                getenv("SMTP_FROM_NAME")
            ); 

            $mail->addAddress($destinatario);

            $mail->isHTML(true);
            $mail->Subject = "Recuperacao de senha - Legados Culturais";
            
            $link = "https://legados-culturais-react.onrender.com/resetar-senha?token=" . urlencode($token);

            $mail->Body = "
                <h2>Recuperação de senha</h2>
                <p>Recebemos uma solicitação para redefinir sua senha.</p>
                <p>Clique no link abaixo para continuar:</p>
                <a href='$link'>Redefinir minha senha</a>
                <p>Este link expira em 30 minutos.</p>
                <p>Se você não solicitou a recuperação, ignore este e-mail.</p>
            ";

            $mail->AltBody = "
                Para redefinir sua senha, acesse:
                $link

                Este link expira em 30 minutos.
            ";

            error_log("4 - Iniciando envio SMTP");
            $resultado = $mail->send();
            error_log("5 - Resultado do envio: " . var_export($resultado, true));
            return $resultado;

            //return $mail->send();

        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $mail->ErrorInfo);
            error_log("Detalhes PHPMailer: " . $mail->ErrorInfo);
            return false;
        }
    }
}