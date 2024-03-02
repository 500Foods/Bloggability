#!/usr/bin/env php
<?php

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load the configuration file
$constantsFile = 'bloggability.json';
$constantsData = file_get_contents($constantsFile);
$constants = json_decode($constantsData, true);

// Get the required configuration values5
$serverHost = strtoupper(explode('.',gethostname())[0]);
$serverTitle = $constants['Bloggability Title'];
$serverDescription = $constants['Bloggability Description'];
$adminMail = $constants['Bloggability Administrator E-Mail'];
$adminName = $constants['Bloggability Administrator Name'];
$smtpHost = $constants['Bloggability SMTP Host'];
$smtpPort = $constants['Bloggability SMTP Port'];
$smtpUser = $constants['Bloggability SMTP User'];
$smtpPass = $constants['Bloggability SMTP Pass'];
$smtpFrom = $constants['Bloggability SMTP From'];
$smtpName = $constants['Bloggability SMTP Name'];
$logFile = $constants['Bloggability Keystore Log'];

// Read the last 50 lines of the log file
$lines = array_slice(file($logFile), -50);
$logLines = implode("\n", $lines);

// Create the email header 
$subject = "[".$serverHost."] ".$serverTitle." Keystore Log as of ".gmdate('Y-M-d');
$headers = "From: $smtpName <$smtpFrom>\r\n";
$headers .= "Reply-To: $adminName <$adminMail>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Create the email message body
$message = <<<HTMLEMAIL
<html>
  <body>
    <h5>$serverDescription</h5>
    <pre style='font-size:14px; line-height:0.5;'>
$logLines
    </pre>
  </body>
</html>
HTMLEMAIL;

// Set up SMTP credentials
$smtpAuth = "PLAIN";
$smtpSender = "From: $smtpName <$smtpFrom>";

// Send the email
$mail = new PHPMailer;
$mail->Sender = $smtpFrom;
$mail->setFrom($smtpFrom, $smtpName);
$mail->addAddress($adminMail, $adminName);
$mail->Subject = $subject;
$mail->Body = $message;
$mail->isHTML(true);
$mail->isSMTP();
$mail->Host = $smtpHost;
$mail->Port = $smtpPort;
$mail->SMTPAuth = true;
$mail->Username = $smtpUser;
$mail->Password = $smtpPass;
$mail->SMTPSecure = 'tls';

if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo .PHP_EOL;
} else {
    echo "Email with log entries sent successfully.". PHP_EOL;
}
?>
