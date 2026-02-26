<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'estatehub.6361@gmail.com';
    $mail->Password = 'cnpemsdedamluudp';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('estatehub.6361@gmail.com', 'My Estate Hub');
    $mail->addAddress('khan.srmap@gmail.com'); // where the test email goes

    $mail->isHTML(true);
    $mail->Subject = 'Test Email from My Estate Hub';
    $mail->Body    = '<h3>PHPMailer is working perfectly!</h3>';

    $mail->send();
    echo '✅ Test email sent successfully!';
} catch (Exception $e) {
    echo "❌ Mail Error: {$mail->ErrorInfo}";
}
