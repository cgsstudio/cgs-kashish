
<?php
/**
 * Career application endpoint.
 * Sends applications and resume attachments using PHPMailer and SMTP.
 */

use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json; charset=utf-8');

const CAREER_INBOX = 'chameleogfxstudio@gmail.com';

require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

/**
 * Send a JSON error response and stop execution.
 *
 * @param string $message Error message.
 * @param int $status HTTP status code.
 * @return void
 */
function fail(string $message, int $status = 400): void
{
    http_response_code($status);

    echo json_encode([
        'ok'  => false,
        'msg' => $message,
    ]);

    exit;
}

/**
 * Clean a submitted form value.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function cleanField($value): string
{
    return trim(
        str_replace(["\r", "\n"], ' ', (string) $value)
    );
}

/**
 * Escape text before inserting it into HTML.
 *
 * @param mixed $value Value to escape.
 * @return string
 */
function escapeHtml($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Create the configured SMTP mailer.
 *
 * @param array<string, mixed> $smtpConfig SMTP configuration.
 * @return PHPMailer
 */
function newCareerMailer(array $smtpConfig): PHPMailer
{
    foreach (['host', 'username', 'password'] as $key) {
        if (
            !isset($smtpConfig[$key]) ||
            !is_string($smtpConfig[$key]) ||
            trim($smtpConfig[$key]) === ''
        ) {
            throw new RuntimeException(
                'Invalid SMTP configuration: ' . $key
            );
        }
    }

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = $smtpConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpConfig['username'];
    $mail->Password   = $smtpConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = (int) ($smtpConfig['port'] ?? 465);
    $mail->Timeout    = 25;
    $mail->CharSet    = PHPMailer::CHARSET_UTF8;

    // The sender must match the authenticated SMTP mailbox.
    $mail->setFrom(
        $smtpConfig['username'],
        'Chameleo GFX Studio Careers'
    );

    return $mail;
}

/**
 * Read and validate the private SMTP configuration.
 *
 * @return array<string, mixed>
 */
function loadCareerSmtpConfig(): array
{
    $configPath = __DIR__ . '/phpmailer/smtp-config.php';

    if (!is_file($configPath) || !is_readable($configPath)) {
        throw new RuntimeException('SMTP configuration is unavailable.');
    }

    $config = require $configPath;

    if (!is_array($config)) {
        throw new RuntimeException('SMTP configuration must return an array.');
    }

    return $config;
}

// Only accept form submissions.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    fail('Method not allowed.', 405);
}

// Honeypot: silently ignore automated submissions.
if (!empty($_POST['_hp'])) {
    echo json_encode(['ok' => true]);
    exit;
}

// Read form fields.
$name     = cleanField($_POST['fullname'] ?? '');
$phone    = cleanField($_POST['phone'] ?? '');
$emailRaw = trim((string) ($_POST['email'] ?? ''));
$email    = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
$position = cleanField($_POST['position'] ?? '');
$subject  = cleanField($_POST['subject'] ?? '');

$nameLength = function_exists('mb_strlen')
    ? mb_strlen($name, 'UTF-8')
    : strlen($name);

$subjectLength = function_exists('mb_strlen')
    ? mb_strlen($subject, 'UTF-8')
    : strlen($subject);

// Validate fields.
if ($nameLength < 2) {
    fail('Please enter your full name.');
}

if (!preg_match('/^[+]?\d[\d\s()\-]{6,18}$/', $phone)) {
    fail('Please enter a valid phone number.');
}

if ($email === false) {
    fail('Please enter a valid email address.');
}

if ($position === '') {
    fail('Please select a position.');
}

if ($subjectLength < 4) {
    fail('Please add a subject.');
}

// Validate resume upload.
$allowedExtensions = ['pdf', 'doc', 'docx', 'rtf', 'odt', 'zip'];
$maxFileSize = 5 * 1024 * 1024;

$file = $_FILES['resume'] ?? null;

if (
    !is_array($file) ||
    !isset($file['error']) ||
    $file['error'] === UPLOAD_ERR_NO_FILE
) {
    fail('Please attach your resume or CV.');
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    fail('The resume upload did not complete. Please try again.');
}

if (
    !isset($file['tmp_name'], $file['name'], $file['size']) ||
    !is_uploaded_file($file['tmp_name'])
) {
    fail('The uploaded resume is invalid.');
}

if ($file['size'] <= 0 || $file['size'] > $maxFileSize) {
    fail('The resume must be greater than 0 bytes and 5 MB or smaller.');
}

$extension = strtolower(
    pathinfo(basename($file['name']), PATHINFO_EXTENSION)
);

if (!in_array($extension, $allowedExtensions, true)) {
    fail('Resume must be PDF, DOC, DOCX, RTF, ODT, or ZIP.');
}

// Generate a safe attachment filename.
$originalName = basename($file['name']);
$attachmentName = preg_replace(
    '/[^A-Za-z0-9._-]/',
    '_',
    $originalName
);

if (!is_string($attachmentName) || $attachmentName === '') {
    $attachmentName = 'resume.' . $extension;
}

if (strlen($attachmentName) > 180) {
    $attachmentName = 'resume.' . $extension;
}

// Read the uploaded file.
$attachment = file_get_contents($file['tmp_name']);

if ($attachment === false) {
    fail('Could not read the uploaded resume. Please try again.', 500);
}

// Determine MIME type for the attachment.
$mimeType = 'application/octet-stream';

if (function_exists('finfo_open')) {
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

    if ($fileInfo !== false) {
        $detectedMime = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);

        if (is_string($detectedMime) && $detectedMime !== '') {
            $mimeType = $detectedMime;
        }
    }
}

// Load SMTP configuration outside the public form fields.
try {
    $smtpConfig = loadCareerSmtpConfig();
} catch (Throwable $exception) {
    error_log('Career SMTP configuration error: ' . $exception->getMessage());

    fail(
        'Mail service is temporarily unavailable. Please email ' .
        CAREER_INBOX . ' directly.',
        500
    );
}

// Prepare email content.
$receivedAt = date('d M Y, h:i A');
$year = date('Y');

$safeName     = escapeHtml($name);
$safeEmail    = escapeHtml($email);
$safePhone    = escapeHtml($phone);
$safePosition = escapeHtml($position);
$safeSubject  = escapeHtml($subject);
$safeFilename = escapeHtml($attachmentName);
$safeReceived = escapeHtml($receivedAt);

// Send the application to HR.
$mail = null;

try {
    $mail = newCareerMailer($smtpConfig);

    $mail->addAddress(CAREER_INBOX);
    $mail->addReplyTo($email, $name);
    $mail->isHTML(true);

    $mail->Subject = 'Career application: ' . $position . ' — ' . $name;

    $mail->Body = <<<HTML
<div style="background-color:#f4f4f4;padding:40px 20px;font-family:Arial,sans-serif;">
  <div style="max-width:600px;margin:0 auto;background-color:#0B0C10;border-radius:12px;overflow:hidden;color:#FFFFFF;">
    <div style="text-align:center;padding:40px 20px;border-bottom:1px solid rgba(255,255,255,0.05);">
      <img src="https://chameleogfxstudio.com/assets/images/chameleogfxstudiologo.webp" alt="Chameleo GFX Studio" style="max-width:250px;height:auto;">
    </div>
    <div style="padding:40px 40px 20px;">
      <h2 style="margin:0 0 20px;font-size:24px;">New Career Application</h2>
      <p style="margin:0 0 30px;color:#A0A0A0;font-size:14px;line-height:1.5;">A candidate applied through the Careers page. Resume is attached.</p>
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;">
        <tr><td style="padding:16px 0;border-bottom:1px solid #333;width:40%;color:#888;">Name</td><td style="padding:16px 0;border-bottom:1px solid #333;color:#fff;">{$safeName}</td></tr>
        <tr><td style="padding:16px 0;border-bottom:1px solid #333;color:#888;">Email</td><td style="padding:16px 0;border-bottom:1px solid #333;color:#ff6b00;"><a href="mailto:{$safeEmail}" style="color:#ff6b00;">{$safeEmail}</a></td></tr>
        <tr><td style="padding:16px 0;border-bottom:1px solid #333;color:#888;">Phone</td><td style="padding:16px 0;border-bottom:1px solid #333;color:#fff;">{$safePhone}</td></tr>
        <tr><td style="padding:16px 0;border-bottom:1px solid #333;color:#888;">Position</td><td style="padding:16px 0;border-bottom:1px solid #333;color:#fff;">{$safePosition}</td></tr>
        <tr><td style="padding:16px 0;border-bottom:1px solid #333;color:#888;">Subject</td><td style="padding:16px 0;border-bottom:1px solid #333;color:#fff;">{$safeSubject}</td></tr>
        <tr><td style="padding:16px 0;border-bottom:1px solid #333;color:#888;">Resume</td><td style="padding:16px 0;border-bottom:1px solid #333;color:#fff;">{$safeFilename}</td></tr>
        <tr><td style="padding:16px 0;color:#888;">Received</td><td style="padding:16px 0;color:#fff;">{$safeReceived}</td></tr>
      </table>
    </div>
    <div style="text-align:center;padding:30px;background-color:#07080B;color:#666;font-size:12px;">
      &copy; {$year} Chameleo GFX Studio. All rights reserved.
    </div>
  </div>
</div>
HTML;

    $mail->AltBody =
        "New career application\n" .
        "Name: {$name}\n" .
        "Phone: {$phone}\n" .
        "Email: {$email}\n" .
        "Position: {$position}\n" .
        "Subject: {$subject}\n" .
        "Resume: {$attachmentName}\n" .
        "Received: {$receivedAt}";

    $mail->addStringAttachment(
        $attachment,
        $attachmentName,
        PHPMailer::ENCODING_BASE64,
        $mimeType
    );

    $mail->send();

} catch (Throwable $exception) {
    $errorDetails = ($mail instanceof PHPMailer)
        ? $mail->ErrorInfo
        : $exception->getMessage();

    error_log('Career application email failed: ' . $errorDetails);

    fail(
        'We could not send your application right now. Please email ' .
        CAREER_INBOX . ' directly.',
        500
    );
}

// Send a confirmation to the applicant.
// HR delivery has already succeeded, so a confirmation failure
// should not cause the application to be reported as failed.
try {
    $ack = newCareerMailer($smtpConfig);

    $ack->addAddress($email, $name);
    $ack->isHTML(false);

    $ack->Subject = 'Application received — Chameleo GFX Studio';

    $ack->Body =
        "Hi {$name},\n\n" .
        "Thank you for applying for {$position}. " .
        "We have received your application and will review it " .
        "within 2–3 working days.\n\n" .
        "Warm regards,\n" .
        "Team Chameleo GFX Studio\n" .
        CAREER_INBOX;

    $ack->send();

} catch (Throwable $exception) {
    error_log(
        'Career applicant confirmation email failed: ' .
        $exception->getMessage()
    );
}

// HR application email was sent successfully.
echo json_encode(['ok' => true]);
