<?php
// echo '<pre>';print_r($_POST);die;
$logFile = __DIR__ . '/../configs/logs/events_log.log';

if (
    isset($_POST['event']) && $_POST['event'] !== '' &&
    isset($_POST['event_type']) && $_POST['event_type'] !== ''
) {
    // Ensure log directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }

    // Ensure log file exists
    if (!file_exists($logFile)) {
        touch($logFile);
        chmod($logFile, 0664);
    }

    // Prepare log entry
    $postData = $_POST;
    $postData['logged_at'] = date('Y-m-d H:i:s');

    // Convert to single-line JSON
    $logLine = json_encode($postData, JSON_UNESCAPED_SLASHES) . PHP_EOL;

    // Append safely
    file_put_contents(
        $logFile,
        $logLine,
        FILE_APPEND | LOCK_EX
    );

    echo json_encode([
        'status' => 'success',
        'message' => 'Event logged'
    ]);
    exit;
} else {
    echo "You are not authorized here!";
}