<?php
// contactsitesupport.php
// Simple Standalone Contact Form (No Moodle)

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if (!empty($name) && !empty($email) && !empty($message)) {
        // Change this to your real support email
        $to = "support@example.com";  

        $subject = "New Support Message from $name";
        $body = "You have received a new message from your contact form.\n\n"
              . "Name: $name\n"
              . "Email: $email\n\n"
              . "Message:\n$message\n";

        $headers = "From: $email\r\n"
                 . "Reply-To: $email\r\n"
                 . "X-Mailer: PHP/" . phpversion();

        // Try to send email
        if (mail($to, $subject, $body, $headers)) {
            echo "<p style='color: green; text-align: center;'>✅ Your message has been sent successfully!</p>";
        } else {
            echo "<p style='color: red; text-align: center;'>❌ Failed to send message. Please try again later.</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center;'>⚠️ All fields are required.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Site Support</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px auto;
            max-width: 500px;
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        button {
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        p {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Contact Site Support</h2>
    <form method="post" action="">
        <label for="name">Your Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Your Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="message">Message:</label>
        <textarea id="message" name="message" rows="6" required></textarea>

        <button type="submit">Send Message</button>
    </form>
</body>
</html>
