<?php
header("Content-Type: application/json");

$description = $_POST['description'] ?? '';
$descriptionLower = strtolower($description);

// Category names
$categories = [
    1 => "Food & Dining",
    2 => "Transportation",
    3 => "Housing / Rent",
    4 => "Bills & Utilities",
    5 => "Health & Personal Care",
    6 => "Education",
    7 => "Entertainment & Leisure",
    8 => "Shopping",
    9 => "Savings & Investments",
    10 => "Miscellaneous"
];

// Keyword mapping
$keywordMap = [
    "food" => 1, "lunch" => 1, "dinner" => 1, "jollibee" => 1, "mcdonalds" => 1,
    "grab" => 2, "uber" => 2, "taxi" => 2, "bus" => 2, "transport" => 2,
    "rent" => 3, "apartment" => 3,
    "electric" => 4, "water" => 4, "bill" => 4,
    "doctor" => 5, "medicine" => 5, "hospital" => 5,
    "tuition" => 6, "school" => 6, "books" => 6,
    "netflix" => 7, "movie" => 7, "game" => 7,
    "shopee" => 8, "lazada" => 8, "mall" => 8, "shopping" => 8,
    "saving" => 9, "investment" => 9, "bank" => 9
];

$foundCategoryId = 10; // default Miscellaneous
$confidence = 10;       // low confidence

foreach ($keywordMap as $keyword => $catId) {
    if (strpos($descriptionLower, $keyword) !== false) {
        $foundCategoryId = $catId;
        $confidence = 95;
        break;
    }
}

echo json_encode([
    "category_id" => $foundCategoryId,
    "category_name" => $categories[$foundCategoryId],
    "confidence" => $confidence
]);