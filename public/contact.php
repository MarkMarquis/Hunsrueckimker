<?php
declare(strict_types=1);

use App\ContactForm;
use App\Csrf;

/** @var App\App $app */
$app = require __DIR__ . '/boot.php';

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ./#kontakt');
    exit;
}

if (!Csrf::check()) {
    $result = ['ok' => false, 'errors' => [], 'message' => 'Ihre Sitzung ist abgelaufen. Bitte laden Sie die Seite neu.'];
} else {
    $result = (new ContactForm($app))->handle($_POST);
}

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($result['ok'] ? 200 : 422);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

// Fallback ohne JavaScript: Ergebnis + Eingaben in der Session merken und zurück
$result['old'] = $result['ok'] ? [] : array_intersect_key($_POST, array_flip(['name', 'email', 'phone', 'subject', 'message', 'type', 'res_date', 'res_time', 'persons', 'area']));
$_SESSION['contact_result'] = $result;
header('Location: ./#kontakt');
