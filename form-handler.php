<?php
require_once __DIR__ . '/config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Settings.php';
require_once CORE_PATH . '/CMS.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Honeypot — bots fill the hidden website field, humans don't
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

$formId = (int) ($_POST['form_id'] ?? 0);
if (!$formId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid form.']);
    exit;
}

$form = CMS::getForm($formId);
if (!$form) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Form not found.']);
    exit;
}

$fields = json_decode($form['fields'] ?? '[]', true) ?: [];

// Validate and collect
$data       = [];
$errors     = [];
$emailValue = null;

foreach ($fields as $field) {
    $name     = $field['name']     ?? '';
    $label    = $field['label']    ?? $name;
    $type     = $field['type']     ?? 'text';
    $required = !empty($field['required']);

    if ($type === 'checkbox') {
        $value = isset($_POST[$name]) ? '1' : '';
    } else {
        $value = trim($_POST[$name] ?? '');
    }

    if ($required && $value === '') {
        $errors[$name] = $label . ' is required.';
        continue;
    }

    if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $errors[$name] = 'Please enter a valid email address.';
        continue;
    }

    $data[$name] = $value;

    if ($type === 'email' && $value !== '') {
        $emailValue = $value;
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Save submission to DB
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
CMS::saveFormSubmission($formId, $data, $ip);

// Email notification
$notifyEmail = $form['notify_email'] ?? '';
if ($notifyEmail) {
    $siteName = Settings::get('site_name', 'Website');
    $subject  = $form['notify_subject'] ?: '[' . $siteName . '] New submission: ' . $form['name'];
    $body     = "New submission from: {$form['name']}\n";
    $body    .= str_repeat('-', 40) . "\n\n";
    foreach ($data as $key => $val) {
        $fieldLabel = $key;
        foreach ($fields as $f) {
            if (($f['name'] ?? '') === $key) {
                $fieldLabel = $f['label'] ?? $key;
                break;
            }
        }
        $body .= "{$fieldLabel}: {$val}\n";
    }
    $body .= "\n" . str_repeat('-', 40) . "\n";
    $body .= "Submitted: " . date('Y-m-d H:i:s') . "\n";
    $body .= "IP: {$ip}\n";

    $host    = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $headers = "From: noreply@{$host}\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    @mail($notifyEmail, $subject, $body, $headers);
}

// Mailchimp subscription
$mailchimpListId = $form['mailchimp_list_id'] ?? '';
if ($mailchimpListId && $emailValue) {
    $apiKey = Settings::get('mailchimp_api_key', '');
    $dc     = Settings::get('mailchimp_dc',      '');
    if ($apiKey && $dc) {
        $url     = "https://{$dc}.api.mailchimp.com/3.0/lists/{$mailchimpListId}/members";
        $payload = json_encode(['email_address' => $emailValue, 'status' => 'subscribed']);
        $ch      = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode('anystring:' . $apiKey),
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}

$redirectUrl = $form['redirect_url'] ?? '';
echo json_encode([
    'success'  => true,
    'message'  => $form['success_message'] ?: 'Thank you! Your message has been sent.',
    'redirect' => $redirectUrl ?: null,
]);
