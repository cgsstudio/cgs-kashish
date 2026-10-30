<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if it's the audit form or contact form by checking for 'improvement' field
    $isAuditForm = isset($_POST['improvement']) && !empty($_POST['improvement']);

    if ($isAuditForm) {
        // Audit form fields (from home2.html)
        $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
        $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
        $phone = isset($_POST['number']) ? htmlspecialchars(trim($_POST['number'])) : '';
        $city = isset($_POST['city']) ? htmlspecialchars(trim($_POST['city'])) : '';
        $industry = isset($_POST['industry']) ? htmlspecialchars(trim($_POST['industry'])) : '';
        $company = $city;
        $improvement = htmlspecialchars(trim($_POST['improvement']));
        $comment = isset($_POST['comment']) ? htmlspecialchars(trim($_POST['comment'])) : '';
        $service = $improvement;
        $message = $comment;
        $formType = 'Website Audit Request';
    } else {
        // Contact form fields
        $name = isset($_POST['f-name']) ? htmlspecialchars(trim($_POST['f-name'])) : '';
        $email = isset($_POST['f-email']) ? filter_var(trim($_POST['f-email']), FILTER_SANITIZE_EMAIL) : '';
        $phone = isset($_POST['f-phone']) ? htmlspecialchars(trim($_POST['f-phone'])) : '';
        $company = isset($_POST['f-company']) ? htmlspecialchars(trim($_POST['f-company'])) : '';
        $service = isset($_POST['f-service']) ? htmlspecialchars(trim($_POST['f-service'])) : '';
        $message = isset($_POST['f-message']) ? htmlspecialchars(trim($_POST['f-message'])) : '';
        $improvement = '';
        $comment = '';
        $formType = 'Contact Form';
    }

    $mail = new PHPMailer(true);

    try {

        // SMTP Configuration
        $mail->isSMTP();
        $smtpConfig = require __DIR__ . '/phpmailer/smtp-config.php';
        $mail->Host = $smtpConfig['host'];
        $mail->SMTPAuth = true;

        $mail->Username = $smtpConfig['username'];
        $mail->Password = $smtpConfig['password'];

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender
        $mail->setFrom('info@chameleogfxstudio.com', 'Chameleo GFX Studio');

        // Where you receive emails
        $mail->addAddress('info@chameleogfxstudio.com');

        // Reply to customer if provided
        if (!empty($email)) {
            $mail->addReplyTo($email, $name);
        }

        // Email
        $mail->isHTML(true);

        $mail->Subject = "New {$formType} - {$company}";

        $mail->Body = "
        <div style='background-color: #f4f4f4; padding: 40px 20px; font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #0B0C10; border-radius: 12px; overflow: hidden; color: #FFFFFF;'>
                
                <!-- Logo Section -->
                <div style='text-align: center; padding: 40px 20px; border-bottom: 1px solid rgba(255,255,255,0.05);'>
                    <img src='https://chameleogfxstudio.com/assets/images/chameleogfxstudiologo.webp' alt='Chameleo GFX Studio' style='max-width: 250px; height: auto;' />
                </div>
                
                <!-- Content Section -->
                <div style='padding: 40px 40px 20px 40px;'>
                    <h2 style='margin: 0 0 20px 0; font-size: 24px; font-weight: bold;'>New {$formType}</h2>
                    <p style='margin: 0 0 30px 0; color: #A0A0A0; font-size: 14px; line-height: 1.5;'>A new inquiry has been submitted through the Chameleo GFX Studio website. Here are the details:</p>

                    <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse: collapse; font-size: 14px;'>
                        <tr>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); width: 40%; color: #888888; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; font-weight: bold;'>Name</td>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #FFFFFF;'>{$name}</td>
                        </tr>
                        <tr>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #888888; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; font-weight: bold;'>Email</td>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #ff6b00;'><a href='mailto:{$email}' style='color: #ff6b00; text-decoration: none;'>{$email}</a></td>
                        </tr>";

        if ($isAuditForm) {
            $mail->Body .= "
                        <tr>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #888888; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; font-weight: bold;'>Company</td>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #FFFFFF;'>{$company}</td>
                        </tr>
                        <tr>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #888888; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; font-weight: bold;'>Improvement</td>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #FFFFFF;'>{$improvement}</td>
                        </tr>
                        <tr>
                            <td style='padding: 16px 0; color: #888888; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; font-weight: bold; vertical-align: top;'>Comment</td>
                            <td style='padding: 16px 0; color: #FFFFFF; line-height: 1.5;'>" . nl2br($comment) . "</td>
                        </tr>";
        } else {
            $mail->Body .= "
                        <tr>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #888888; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; font-weight: bold;'>Phone</td>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #FFFFFF;'>{$phone}</td>
                        </tr>
                        <tr>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #888888; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; font-weight: bold;'>Company</td>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #FFFFFF;'>{$company}</td>
                        </tr>
                        <tr>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #888888; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; font-weight: bold;'>Service</td>
                            <td style='padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.05); color: #FFFFFF;'>{$service}</td>
                        </tr>
                        <tr>
                            <td style='padding: 16px 0; color: #888888; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; font-weight: bold; vertical-align: top;'>Message</td>
                            <td style='padding: 16px 0; color: #FFFFFF; line-height: 1.5;'>" . nl2br($message) . "</td>
                        </tr>";
        }

        $mail->Body .= "
                    </table>
                </div>
                
                <!-- Footer Section -->
                <div style='text-align: center; padding: 30px; background-color: #07080B; color: #666666; font-size: 12px;'>
                    &copy; " . date('Y') . " Chameleo GFX Studio. All rights reserved.
                </div>
            </div>
        </div>
        ";

        if ($isAuditForm) {
            $mail->AltBody =
                "Form Type: Website Audit Request\n
                Name: $name\n
                Phone: $phone\n
                City: $city\n
                Industry: $industry\n
                Improvement: $improvement\n
                Comment:\n$comment";
        } else {
            $mail->AltBody =
                "Form Type: Contact Form\n
                Name: $name\n
                Email: $email\n
                Phone: $phone\n
                Company: $company\n
                Service: $service\n
                Message:\n$message";
        }

        $mail->send();

        // Redirect with form type parameter
        $redirectUrl = $isAuditForm ? '/thank-you/?type=audit' : '/thank-you/?type=contact';
        header("Location: $redirectUrl");
        exit();

    } catch (Exception $e) {
        echo "<div style='font-family: Arial; padding: 20px; border: 1px solid #ccc; background: #ffebeb; color: #a00; border-radius: 5px; margin: 20px auto; max-width: 600px;'>";
        echo "<h2>Sorry, your message could not be sent.</h2>";
        echo "<p><strong>Error Details:</strong> " . $mail->ErrorInfo . "</p>";
        echo "<p><a href='index' style='color: #a00; font-weight: bold;'>&larr; Go Back</a></p>";
        echo "</div>";
    }

}
?>
