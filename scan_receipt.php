<?php
// 1. Load Dependencies (Patterned after your local_categorize.php)
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

header("Content-Type: application/json");

/* =====================================================
   1. INPUT HANDLING
===================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['receipt'])) {
    echo json_encode(["success" => false, "error" => "No receipt uploaded via POST."]);
    exit;
}

$file = $_FILES['receipt'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "error" => "Upload Error Code: " . $file['error']]);
    exit;
}

/* =====================================================
   2. PREPARE API DATA
===================================================== */
$apiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? null;

// Gigamit ang Gemini 3 Flash (v1beta) - Ang pinakabag-o para sa Vision
// Gamiton nato ang 'gemini-1.5-flash' kay mao ni ang naay "Vision" support sa v1beta
// Gamita ang 'gemini-flash-latest' kay kini automatic mo-point sa pinakabag-o nga model (Gemini 3 Flash) nga naay Vision support.
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;
$imageData = base64_encode(file_get_contents($file['tmp_name']));
$mimeType = $file['type'];

// Listahan sa categories aron synchronized sa imong Database/UI
$catList = "1: Food & Dining, 2: Transportation, 3: Housing / Rent, 4: Bills & Utilities, 5: Health & Personal Care, 6: Education, 7: Entertainment & Leisure, 8: Shopping, 9: Savings & Investments, 10: Miscellaneous";

$prompt = "Analyze this receipt image. Extract data and return ONLY a raw JSON object:
{
  \"description\": \"Store name or main items\",
  \"amount\": total_amount_as_number,
  \"payment_method_id\": (1:Cash, 4:GCash, 5:Maya, 6:Bank Transfer, 7:Online),
  \"category_id\": integer_based_on_list,
  \"category_name\": \"name_from_list\"
}
Categories: [$catList]. Strictly no markdown tags.";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt],
                [
                    "inline_data" => [
                        "mime_type" => $mimeType,
                        "data" => $imageData
                    ]
                ]
            ]
        ]
    ]
];

/* =====================================================
   3. CURL EXECUTION (With SSL Fix)
===================================================== */
$ch = curl_init($apiUrl);
$certPath = __DIR__ . '/includes/cacert.pem'; 

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// SSL Security Handling
if (file_exists($certPath)) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_CAINFO, $certPath);
} else {
    // Fallback kon wala ang cert file para dili mag-error 0
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

/* =====================================================
   4. RESPONSE HANDLING
===================================================== */
if ($httpCode === 200) {
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $aiRaw = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Limpyohan ang output (Regex to extract JSON)
        if (preg_match('/\{.*\}/s', $aiRaw, $matches)) {
            $aiData = json_decode($matches[0], true);
            if ($aiData) {
                $aiData['success'] = true;
                echo json_encode($aiData);
            } else {
                echo json_encode(["success" => false, "error" => "Failed to parse AI JSON."]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "AI did not return valid JSON format."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Unexpected API response structure."]);
    }
} else {
    // Diri nimo makita ang tinuod nga message kon mag-403 o 404
    $errorMsg = json_decode($response, true);
    echo json_encode([
        "success" => false, 
        "error" => "API Error: " . $httpCode,
        "message" => $errorMsg['error']['message'] ?? "Unknown Error",
        "curl_error" => $curlError
    ]);
}