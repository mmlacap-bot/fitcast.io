<?php
// contactus.php — with Gmail SMTP, PDF + CSV logging, CSRF protection
session_start();
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    die('Please run "composer require phpmailer/phpmailer dompdf/dompdf"');
}
require_once $autoload;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

// === CONFIGURATION ===
$admin_email = 'mauriceab2004@gmail.com'; // <-- replace with your Gmail
$gmail_username = 'mauriceab2004@gmail.com'; // <-- same Gmail
$gmail_app_password = 'zkhx decl sodu vpwf'; // <-- your app password (keep private!)
$storage_file = __DIR__ . '/contacts.csv';

// === INITIAL SETUP ===
$errors = [];
$success = false;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// === HANDLE FORM SUBMISSION ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf'])) {
        $errors[] = 'Invalid form submission.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name === '') $errors[] = 'Name is required.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if ($subject === '') $errors[] = 'Subject is required.';
        if ($message === '') $errors[] = 'Message is required.';
        if (mb_strlen($message) > 5000) $errors[] = 'Message is too long.';

        if (empty($errors)) {
            $record = [
                'timestamp' => date('c'),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
            ];

            // === Save to CSV ===
            $fp = @fopen($storage_file, 'a');
            if ($fp) {
                if (filesize($storage_file) === 0) {
                    fputcsv($fp, array_keys($record));
                }
                $row = $record;
                $row['message'] = str_replace(["\r\n", "\r", "\n"], '\\n', $row['message']);
                fputcsv($fp, $row);
                fclose($fp);
            }

            // === Generate PDF ===
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $dompdf = new Dompdf($options);
            $html = "<h2>Contact Message</h2>
            <p><strong>Time:</strong> " . e($record['timestamp']) . "</p>
            <p><strong>IP:</strong> " . e($record['ip']) . "</p>
            <p><strong>Name:</strong> " . e($record['name']) . "</p>
            <p><strong>Email:</strong> " . e($record['email']) . "</p>
            <p><strong>Subject:</strong> " . e($record['subject']) . "</p>
            <hr><p>" . nl2br(e($record['message'])) . "</p>";
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();
            $pdf = tempnam(sys_get_temp_dir(), 'msg_') . '.pdf';
            file_put_contents($pdf, $dompdf->output());

            // === Send via Gmail SMTP ===
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $gmail_username;
                $mail->Password = $gmail_app_password;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom($gmail_username, 'Website Contact Form');
                $mail->addAddress($admin_email, 'Admin');
                $mail->addReplyTo($email, $name);

                // Content
                $mail->isHTML(false);
                $mail->Subject = "[Contact] $subject";
                $mail->Body = "Name: $name\nEmail: $email\n\nMessage:\n$message";

                if (file_exists($pdf)) {
                    $mail->addAttachment($pdf, 'message.pdf');
                }

                $mail->send();
                $success = true;
            } catch (Exception $e) {
                $errors[] = 'Mail error: ' . $mail->ErrorInfo;
            }

            if (file_exists($pdf)) unlink($pdf);
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Contact Us</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body {
    background: #f3f6fb;
    font-family: "Segoe UI", Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 40px 16px;
  }
  .container {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    padding: 30px 40px;
    width: 100%;
    max-width: 600px;
    animation: fadeIn 0.5s ease;
  }
  h1 {
    text-align: center;
    color: #2b3e5e;
    margin-bottom: 25px;
  }
  .field { margin-bottom: 18px; }
  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
  }
  input[type="text"], input[type="email"], textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd3e1;
    border-radius: 10px;
    font-size: 15px;
    transition: 0.2s;
  }
  input:focus, textarea:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
    outline: none;
  }
  textarea { min-height: 150px; resize: vertical; }
  .btn {
    display: block;
    width: 100%;
    padding: 12px;
    background: linear-gradient(90deg,#007bff,#0056d2);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
  }
  .btn:hover {
    background: linear-gradient(90deg,#0056d2,#003da6);
  }
  .errors, .success {
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 18px;
  }
  .errors { background: #ffe6e6; color: #a40000; }
  .success { background: #e6ffe9; color: #067a00; }
  small { display: block; text-align: center; color: #777; margin-top: 16px; }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>
</head>
<body>
<div class="container">
  <h1>📬 Contact Us</h1>

  <?php if (!empty($errors)): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="success">✅ Thank you! Your message has been successfully sent.</div>
  <?php endif; ?>

  <form method="post" action="">
    <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
    <div class="field">
      <label for="name">Full Name</label>
      <input id="name" name="name" type="text" required value="<?php echo e($_POST['name'] ?? ''); ?>">
    </div>
    <div class="field">
      <label for="email">Email Address</label>
      <input id="email" name="email" type="email" required value="<?php echo e($_POST['email'] ?? ''); ?>">
    </div>
    <div class="field">
      <label for="subject">Subject</label>
      <input id="subject" name="subject" type="text" required value="<?php echo e($_POST['subject'] ?? ''); ?>">
    </div>
    <div class="field">
      <label for="message">Your Message</label>
      <textarea id="message" name="message" required><?php echo e($_POST['message'] ?? ''); ?></textarea>
    </div>
    <button type="submit" class="btn">Send Message</button>
  </form>

  <small>🗂️ Messages are saved in: <?php echo e(basename($storage_file)); ?></small>
</div>
</body>
</html>
