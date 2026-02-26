<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendRegistrationEmail($email, $username) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'estatehub.6361@gmail.com'; // Replace with your Gmail
        $mail->Password = 'puqaettjesvaazwc'; // Replace with your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('estatehub.6361@gmail.com', 'Estate Hub');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Estate Hub';
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #0ef; color: #1f293a; padding: 20px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Welcome to Estate Hub!</h1>
                    </div>
                    <div class='content'>
                        <p>Hello $username,</p>
                        <p>Thank you for registering with Estate Hub. We're excited to have you on board!</p>
                        <p>You can now log in to your account and start exploring our properties.</p>
                        <p>If you have any questions, feel free to contact our support team.</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message, please do not reply.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}

function sendPasswordRecoveryEmail($email, $username, $password) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'estatehub.6361@gmail.com'; // TODO: Replace with your Gmail
        $mail->Password = 'puqaettjesvaazwc'; // TODO: Replace with your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('estatehub.6361@gmail.com', 'Estate Hub');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password - Estate Hub';
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background: #1f293a; color: white; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #0ef; color: #1f293a; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 20px; background: #2c4766; border-radius: 0 0 10px 10px; }
                    .password-box { 
                        background: #1f293a; 
                        padding: 15px; 
                        border: 2px solid #0ef; 
                        border-radius: 5px; 
                        margin: 20px 0; 
                        text-align: center;
                        font-size: 20px;
                        color: #0ef;
                    }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Your Password</h1>
                    </div>
                    <div class='content'>
                        <p>Hello {$username},</p>
                        <p>As requested, here is your current password:</p>
                        <div class='password-box'>
                            {$password}
                        </div>
                        <p>For security reasons, we recommend changing your password after logging in.</p>
                        <p>If you did not request this information, please secure your account immediately.</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message, please do not reply.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
} 