<?php
header("Content-Type: application/json");

/* =====================================================
   1. INPUT HANDLING
===================================================== */
$description = $_POST['description'] ?? '';
$descriptionLower = strtolower(trim($description));

if (empty($descriptionLower)) {
    echo json_encode(["category_id" => 10, "category_name" => "Miscellaneous", "confidence" => 0]);
    exit;
}

/* =====================================================
   2. CATEGORY MAPPING
===================================================== */
$categories = [
    1 => "Food & Dining", 2 => "Transportation", 3 => "Housing / Rent",
    4 => "Bills & Utilities", 5 => "Health & Personal Care", 6 => "Education",
    7 => "Entertainment & Leisure", 8 => "Shopping", 9 => "Savings & Investments", 
    10 => "Miscellaneous"
];

/* =====================================================
   3. STAGE 1: KEYWORD CHECK (Fast)
===================================================== */
$keywordMap = [
    "jollibee" => 1, "mcdo" => 1, "food" => 1, "starbucks" => 1,
    "grab" => 2, "angkas" => 2, "jeep" => 2, "bus" => 2,
    "meralco" => 4, "pldt" => 4, "bill" => 4, "electricity" => 4,
    "shopee" => 8, "lazada" => 8, "sm" => 8, "mall" => 8
];

$foundCategoryId = 10;
$confidence = 10;

foreach ($keywordMap as $keyword => $catId) {
    if (strpos($descriptionLower, $keyword) !== false) {
        $foundCategoryId = $catId;
        $confidence = 98;
        break;
    }
}

/* =====================================================
   4. STAGE 2: AI CHECK (Using your Curl Logic)
===================================================== */
if ($confidence < 90) {
    $apiKey = "AIzaSyAhQc_3UWHdNHiAFzLJZg_K_1zHnxIur8A";
    // Using the exact model name from your curl: gemini-flash-latest
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;

    $catList = "";
    foreach($categories as $id => $name) { $catList .= "$id: $name, "; }

    $prompt = "Categorize this expense: '$description'. Options: [$catList]. Return ONLY JSON: {\"id\": integer, \"confidence\": integer}";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
    // Crucial for XAMPP/Localhost
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $aiRaw = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Clean JSON from AI
        if (preg_match('/\{.*\}/s', $aiRaw, $matches)) {
            $aiData = json_decode($matches[0], true);
            if ($aiData && isset($aiData['id'])) {
                $foundCategoryId = (int)$aiData['id'];
                $confidence = (int)$aiData['confidence'];
            }
        }
    }
    curl_close($ch);
}

/* =====================================================
   5. FINAL OUTPUT
===================================================== */
echo json_encode([
    "category_id" => $foundCategoryId,
    "category_name" => $categories[$foundCategoryId] ?? "Miscellaneous",
    "confidence" => $confidence
]);