<?php
require_once 'config.php';

// Auth check (admin only)
function auth_check() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : '') . 'login.php');
        exit;
    }
}

// Admin logout
function logout() {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

// Log admin activity
function log_activity($pdo, $admin_id, $action_type, $target_id, $description) {
    $stmt = $pdo->prepare('INSERT INTO activity_log (admin_id, action_type, target_id, description) VALUES (?, ?, ?, ?)');
    $stmt->execute([$admin_id, $action_type, $target_id, $description]);
}

// Send email notification to applicant
function send_notification($to_email, $to_name, $queue_code, $certificate, $event, $schedule = null, $notes = '') {
    $subject = '';
    $body = '';
    $app_name = APP_NAME;

    switch ($event) {
        case 'queued':
            $subject = "Queue Confirmation - {$queue_code}";
            $body = "
                <h2 style='color:#F5C400;'>You're in the queue!</h2>
                <p>Hi <strong>{$to_name}</strong>,</p>
                <p>Your queue number for <strong>{$certificate}</strong> certificate claim has been received.</p>
                <div style='background:#1A1A2E; padding:1.5rem; border-radius:12px; margin:1.5rem 0; text-align:center;'>
                    <p style='color:#9090B0; font-size:0.85rem; margin-bottom:0.5rem;'>YOUR QUEUE NUMBER</p>
                    <h1 style='color:#F5C400; font-size:2.5rem; letter-spacing:0.05em; margin:0;'>{$queue_code}</h1>
                </div>
                <p>Save this number to check your status at <a href='" . APP_URL . "' style='color:#F5C400;'>" . APP_URL . "</a></p>
                <p>We will notify you once your certificate is ready for pickup.</p>
            ";
            break;
        case 'approved':
            $subject = "Application Approved - {$queue_code}";
            $sched_text = $schedule ? "<p><strong>Scheduled Date:</strong> " . date('F j, Y g:i A', strtotime($schedule)) . "</p>" : '';
            $notes_text = $notes ? "<p><strong>Notes:</strong> {$notes}</p>" : '';
            $body = "
                <h2 style='color:#00C896;'>Your certificate is approved! ✅</h2>
                <p>Hi <strong>{$to_name}</strong>,</p>
                <p>Great news! Your <strong>{$certificate}</strong> certificate claim has been approved.</p>
                {$sched_text}
                {$notes_text}
                <p>Please visit the BTVLACI office on your scheduled date to claim your certificate.</p>
                <p>Queue Number: <strong style='color:#F5C400;'>{$queue_code}</strong></p>
            ";
            break;
        case 'rejected':
            $subject = "Queue Update - {$queue_code}";
            $notes_text = $notes ? "<p><strong>Reason:</strong> {$notes}</p>" : '';
            $body = "
                <h2 style='color:#FF4757;'>Queue Status Update</h2>
                <p>Hi <strong>{$to_name}</strong>,</p>
                <p>Unfortunately, your <strong>{$certificate}</strong> certificate claim could not be processed at this time.</p>
                {$notes_text}
                <p>Queue Number: <strong>{$queue_code}</strong></p>
                <p>Please contact the BTVLACI office for more information.</p>
            ";
            break;
    }

    $full_body = "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background:#F5F5F5; padding:2rem;'>
            <div style='max-width:560px; margin:0 auto; background:white; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.1);'>
                <div style='background:#1A1A2E; padding:1.5rem; text-align:center;'>
                    <h1 style='color:#F5C400; margin:0; font-size:1.25rem;'>🏗️ {$app_name}</h1>
                </div>
                <div style='padding:2rem;'>
                    {$body}
                    <hr style='border:none; border-top:1px solid #eee; margin:1.5rem 0;'>
                    <p style='color:#999; font-size:0.8rem;'>This is an automated message from {$app_name}. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
    ";

    $headers = "From: {$app_name} <" . SMTP_USER . ">\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    @mail($to_email, $subject, $full_body, $headers);
}
?>
