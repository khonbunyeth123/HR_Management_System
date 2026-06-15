<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;
    private string $encryption;

    public function __construct()
    {
        $this->host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $this->port       = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $this->username   = $_ENV['MAIL_USERNAME'] ?? '';
        $this->password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->fromEmail  = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com';
        $this->fromName   = $_ENV['MAIL_FROM_NAME'] ?? 'Employee Management System';
        $this->encryption = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS;
    }

    /**
     * Send a password reset email.
     */
    public function sendResetLink(string $toEmail, string $resetLink): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = $this->encryption;
            $mail->Port       = $this->port;

            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            
            $body = "
                <h2>Password Reset Request</h2>
                <p>We received a request to reset your password. Click the button below to set a new password:</p>
                <p style='margin: 30px 0;'>
                    <a href='{$resetLink}' style='background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Reset Password</a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p><a href='{$resetLink}'>{$resetLink}</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request a password reset, please ignore this email.</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>This is an automated message, please do not reply.</p>
            ";

            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            return $mail->send();
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }
}
