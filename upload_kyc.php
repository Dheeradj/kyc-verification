<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

function generateToken($clientId, $base64Secret, $expireMinutes) {
    $secretKey = base64_decode($base64Secret);
    $now = new DateTime(null, new DateTimeZone('UTC'));
    $expiration = $now->add(new DateInterval('PT' . $expireMinutes . 'M'));
    $payload = [
        'iss' => $clientId,
        'sub' => $clientId,
        'aud' => 'BWS',
        'iat' => $now->getTimestamp(),
        'exp' => $expiration->getTimestamp()
    ];

    $header = [
        'alg' => 'HS512',
        'typ' => 'JWT'
    ];
    return JWT::encode($payload, $secretKey, 'HS512', null, $header);
}

$data = json_decode(file_get_contents('php://input'), true);

$photo1Base64 = $data['photo1'] ?? '';
$photo2Base64 = $data['photo2'] ?? '';
$idPhotoBase64 = $data['id_photo'] ?? '';

// Function to remove the data URL prefix
function removeDataUrlPrefix($base64String) {
    if (strpos($base64String, 'base64,') !== false) {
        return explode('base64,', $base64String)[1];
    }
    return $base64String;
}

$photo1Base64 = removeDataUrlPrefix($photo1Base64);
$photo2Base64 = removeDataUrlPrefix($photo2Base64);
$idPhotoBase64 = removeDataUrlPrefix($idPhotoBase64);

if ($photo1Base64 && $photo2Base64 && $idPhotoBase64) {
    $image1 = base64_decode($photo1Base64);
    $image2 = base64_decode($photo2Base64);
    $image3 = base64_decode($idPhotoBase64);

    $image1Path = tempnam(sys_get_temp_dir(), 'photo1_');
    $image2Path = tempnam(sys_get_temp_dir(), 'photo2_');
    $image3Path = tempnam(sys_get_temp_dir(), 'id_photo_');

    file_put_contents($image1Path, $image1);
    file_put_contents($image2Path, $image2);
    file_put_contents($image3Path, $image3);
} else {
    die("Error: Missing image data.");
}

$image1Encoded = base64_encode($image1);
$image2Encoded = base64_encode($image2);
$image3Encoded = base64_encode($image3);

$payload = [
    'liveImages' => [
        ['image' => $image1Encoded, 'tags' => []],
        ['image' => $image2Encoded, 'tags' => []]
    ],
    'photo' => $image3Encoded,
    'disableLivenessDetection' => false
];

$jsonPayload = json_encode($payload);

$debugFilePath = __DIR__ . '/debug_payload.json';
file_put_contents($debugFilePath, $jsonPayload);
// echo "Debug payload saved to: $debugFilePath\n";

$clientId = "678e2a6f9e01eb7b8fe7e411";
$base64Secret = "1MvNI6RtscVjy6k/29uXROwx8kFi+g/7kO+uR/vxrdG6wpIN8KvkGjJoD4HnL5MvD2z+wUrsG+3neGrBYX9o9w==";
$expireMinutes = 60;

try {
    $token = generateToken($clientId, $base64Secret, $expireMinutes);
    file_put_contents('debug_jwt_token.txt', $token); // Debugging JWT
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error generating JWT token: ' . $e->getMessage()]);
    exit;
}

$endpoint = "https://grpc.bws-eu.bioid.com/api/v1/photoverify";

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $token
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);

$response = curl_exec($ch);

if ($response === false) {
    $error = curl_error($ch);
    file_put_contents('debug_curl_error.txt', $error);
    http_response_code(500);
    echo json_encode(['error' => 'cURL request failed: ' . $error]);
    exit;
}

$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
file_put_contents('debug_http_status.txt', $httpStatusCode);

if ($httpStatusCode !== 200) {
    file_put_contents('debug_curl_response_error.txt', $response);
    http_response_code($httpStatusCode);
    echo json_encode(['error' => 'API call failed.', 'response' => $response]);
    exit;
}

$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

file_put_contents('debug_payload_sent.txt', json_encode($payload, JSON_PRETTY_PRINT));


curl_close($ch);

if ($httpStatusCode === 200) {
    // Assuming the response from the API is JSON formatted
    echo json_encode(['success' => true, 'message' => 'Verification succeeded', 'data' => json_decode($response)]);
} else {
    echo json_encode(['success' => false, 'message' => 'Verification failed', 'error' => $response]);
}
?>