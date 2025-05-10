<?php
header('Content-Type: application/json');

function getYouTubeAudioUrl($videoUrl) {
    // Validate YouTube URL
    if (!preg_match('/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/', $videoUrl)) {
        return ['error' => 'Invalid YouTube URL'];
    }

    // Use youtube-dl to extract audio URL
    $command = escapeshellcmd("yt-dlp -f best[ext=mp4] --get-thumbnail --get-url" . escapeshellarg($videoUrl) . " 2>&1");
    $output = shell_exec($command);
    return(['error' => $output]);

    // Check for errors
    if ($output === null) {
        return ['error' => $output];
    }

    // Trim and validate the output
    $output = explode("\n", $output);
    $audioUrl = trim($output[0]);
    $thumbnailUrl = trim($output[1]);
    if (empty($audioUrl) || !filter_var($audioUrl, FILTER_VALIDATE_URL)) {
        return ['error' => 'Could not extract audio URL'];
    }

    return ['audio_url' => $audioUrl, 'thumbnail_url' => $thumbnailUrl];
}

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['url'])) {
    $videoUrl = $_GET['url'];
    echo json_encode(getYouTubeAudioUrl($videoUrl));
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['url'])) {
        echo json_encode(getYouTubeAudioUrl($data['url']));
        exit;
    }
}

// If no valid request
http_response_code(400);
echo json_encode(['error' => 'Invalid request. Provide YouTube URL via GET or POST']);
?>
