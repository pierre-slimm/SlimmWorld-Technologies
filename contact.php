<?php

// **Database Configuration**
$dbHost     = "localhost";          // Hostname of your PostgreSQL server
$dbPort     = "5432";               // Default PostgreSQL port
$dbName     = "contact";            // Name of your PostgreSQL database
$dbUser     = "postgres";         // Database username
$dbPassword = "xxxxxxx";       // Database password

// **Email Configuration**
$recipientEmail = "slimmworldtechnologies21@gmail.com";
$subject = "New Contact Form Submission";

// **Google reCAPTCHA Secret Key**
$recaptchaSecret = "YOUR_SECRET_KEY_HERE"; // Replace with your actual secret key

// **Establish Database Connection**
try {
    // Data Source Name (DSN) for PostgreSQL
    $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName;";
    
    // Create a new PDO instance
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exceptions for errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Fetch associative arrays
    ]);
} catch (PDOException $e) {
    // Handle connection error
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Internal Server Error. Please try again later."
    ]);
    exit; // Terminate script execution
}

// **Handle Form Submission**
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // **Retrieve and Sanitize Form Inputs**
    $yourName = sanitizeInput($_POST['yourName'] ?? '');
    $yourEmail = sanitizeInput($_POST['yourEmail'] ?? '');
    $yourPhone = sanitizeInput($_POST['yourPhone'] ?? '');
    $yourMessage = sanitizeInput($_POST['yourMessage'] ?? '');
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // **Initialize an Errors Array**
    $errors = [];

    // **Validate Required Fields**
    if (empty($yourName)) {
        $errors[] = "Name is required.";
    }

    if (empty($yourEmail)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($yourEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($yourMessage)) {
        $errors[] = "Message is required.";
    }

    // **Verify reCAPTCHA**
    if (empty($recaptchaResponse)) {
        $errors[] = "reCAPTCHA verification failed. Please try again.";
    } else {
        $recaptchaSuccess = verifyReCAPTCHA($recaptchaResponse, $recaptchaSecret);
        if (!$recaptchaSuccess) {
            $errors[] = "reCAPTCHA verification failed. Please try again.";
        }
    }

    // **Check for Errors and Process Form**
    if (empty($errors)) {
        // **Start a Transaction** (Optional but recommended)
        try {
            $pdo->beginTransaction();

            // **Insert Form Data into PostgreSQL Database**
            $insertQuery = "INSERT INTO contacts (name, email, phone, message, submitted_at) 
                            VALUES (:name, :email, :phone, :message, NOW())";
            
            $stmt = $pdo->prepare($insertQuery);
            $stmt->execute([
                ':name' => $yourName,
                ':email' => $yourEmail,
                ':phone' => $yourPhone,
                ':message' => $yourMessage
            ]);

            // **Commit the Transaction**
            $pdo->commit();
        } catch (PDOException $e) {
            // **Rollback the Transaction in Case of Error**
            $pdo->rollBack();
            error_log("Database insertion failed: " . $e->getMessage());
            echo json_encode([
                "status" => "error",
                "message" => "There was an error processing your request. Please try again later."
            ]);
            exit; // Terminate script execution
        }

        // **Prepare the Email Content**
        $emailContent = "You have received a new message from your website contact form.\n\n";
        $emailContent .= "Here are the details:\n";
        $emailContent .= "Name: $yourName\n";
        $emailContent .= "Email: $yourEmail\n";
        $emailContent .= "Phone: $yourPhone\n\n";
        $emailContent .= "Message:\n$yourMessage\n";

        // **Set Email Headers**
        $headers = "From: $yourName <$yourEmail>\r\n";
        $headers .= "Reply-To: $yourEmail\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // **Send the Email**
        $mailSent = mail($recipientEmail, $subject, $emailContent, $headers);

        if ($mailSent) {
            // Success Response
            echo json_encode([
                "status" => "success",
                "message" => "Thank you! Your message has been sent successfully."
            ]);
        } else {
            // Email Sending Failed
            echo json_encode([
                "status" => "error",
                "message" => "Sorry, there was an error sending your message. Please try again later."
            ]);
        }
    } else {
        // Return Errors
        echo json_encode([
            "status" => "error",
            "message" => implode(" ", $errors)
        ]);
    }
} else {
    // Invalid Request Method
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method."
    ]);
}

// **Function to Sanitize Inputs**
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    // Convert special characters to HTML entities to prevent XSS
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// **Function to Verify reCAPTCHA Response**
function verifyReCAPTCHA($response, $secret) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    
    // Make and decode POST request:
    $data = [
        'secret' => $secret,
        'response' => $response
    ];

    // Using cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $result = curl_exec($ch);
    curl_close($ch);

    if ($result === false) {
        return false;
    }

    $resultData = json_decode($result, true);
    return isset($resultData['success']) && $resultData['success'] === true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
} else {
    // Return 405 Method Not Allowed
    http_response_code(405);
    echo "Method Not Allowed";
}

?>