<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes (update the path if necessary)
require 'vendor/autoload.php'; // If installed via Composer
// require 'path/to/PHPMailer/src/PHPMailer.php'; // If manually downloaded

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate input
    $name = htmlspecialchars(trim($_POST['yourName']));
    $email = filter_var(trim($_POST['yourEmail']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['yourPhone']));
    $message = htmlspecialchars(trim($_POST['yourMessage']));

    // Error handling
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
    if (empty($message)) {
        $errors[] = "Message is required.";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
        exit;
    }

    // Configure PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Use Gmail's SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'antoninokenyando2025@gmail.com'; // Replace with your Gmail address
        $mail->Password = 'vebe ajsg wenl uzik'; // Replace with your Gmail password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress('slimmworldtechnologies21@gmail.com'); // Your email address

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Submission';
        $mail->Body = "<p><strong>Name:</strong> $name</p>
                       <p><strong>Email:</strong> $email</p>
                       <p><strong>Phone:</strong> $phone</p>
                       <p><strong>Message:</strong><br>$message</p>";

        // Send email
        $mail->send();
        echo "<p style='color: green;'>Thank you! Your message has been sent successfully.</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Oops! Something went wrong. Mailer Error: {$mail->ErrorInfo}</p>";
    }
}
?>
