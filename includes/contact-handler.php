<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/email-template.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Method not allowed.']);
    exit;
}

if (($settings['enable_contact_form'] ?? '1') !== '1') {
    echo json_encode(['ok' => false, 'msg' => 'Contact form is disabled.']);
    exit;
}

$isAnonymous = !empty($_POST['anonymous']);
$message = trim($_POST['message'] ?? '');

if (empty($message)) {
    echo json_encode(['ok' => false, 'msg' => 'Message is required.']);
    exit;
}

if ($isAnonymous) {
    $name = 'Anonymous';
    $email = '';
    $subject = '';
} else {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    if (empty($name) || empty($email)) {
        echo json_encode(['ok' => false, 'msg' => 'Name and email are required.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'msg' => 'Invalid email address.']);
        exit;
    }
}

$saved = false;
if ($dbAvailable) {
    try {
        $pdo->prepare("INSERT INTO messages (name, email, subject, message, is_anonymous) VALUES (?,?,?,?,1)")
            ->execute([$name, $email, $subject, $message]);
        $saved = true;
    } catch (PDOException $e) {
        error_log('CONTACT SAVE: ' . $e->getMessage());
    }
}

$profileEmail = '';
if ($dbAvailable) {
    try {
        $p = $pdo->query("SELECT email FROM profile WHERE id = 1")->fetch();
        if ($p) $profileEmail = $p['email'];
    } catch (PDOException $e) {}
}

if ($profileEmail) {
    if ($isAnonymous) {
        $bodyHtml = '<p>Someone sent you an anonymous message:</p>'
                  . '<div class="divider"></div>'
                  . '<div class="label">Message</div><p>' . nl2br(h($message)) . '</p>'
                  . '<div class="divider"></div>'
                  . '<p style="font-size:13px;color:#a1a1a6">Sent anonymously via ' . h($_SERVER['HTTP_HOST'] ?? 'site') . ' contact form</p>';
        sendMail($profileEmail, 'Anonymous message — ' . ($_SERVER['HTTP_HOST'] ?? 'site'), emailTemplate('Anonymous Message', $bodyHtml));
    } else {
        $bodyHtml = '<p><strong>' . h($name) . '</strong> (' . h($email) . ') sent you a message:</p>'
                  . '<div class="divider"></div>'
                  . '<div class="label">Subject</div><p>' . h($subject ?: '(no subject)') . '</p>'
                  . '<div class="label">Message</div><p>' . nl2br(h($message)) . '</p>'
                  . '<div class="divider"></div>'
                  . '<p style="font-size:13px;color:#a1a1a6">Sent via ' . h($_SERVER['HTTP_HOST'] ?? 'site') . ' contact form</p>';
        sendMail($profileEmail, 'New message from ' . $name . ' — ' . ($_SERVER['HTTP_HOST'] ?? 'site'), emailTemplate('New Contact Message', $bodyHtml));
    }
}

echo json_encode(['ok' => true, 'msg' => 'Message sent successfully!']);
