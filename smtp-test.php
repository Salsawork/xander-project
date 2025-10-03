<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'notification@ptbmn.co.id';
    $mail->Password = 'Bmn05notif!';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('notification@ptbmn.co.id', 'PT BMN');
    $mail->addAddress('salsabilaazkaputri@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Tes Email Hostinger';
    $mail->Body    = 'Berhasil kirim email dari PHP langsung.';

    $mail->send();
    echo "Email terkirim!\n";
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}\n";
}
