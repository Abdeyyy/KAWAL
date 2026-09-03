<?php
// api.php - Backend API for processing wacana and connecting to Gemini API
require_once 'config.php';

// Set response header to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Hanya menerima request POST'
    ]);
    exit;
}

// Read inputs (support JSON body or standard form POST)
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    $data = $_POST;
}

$text_input = isset($data['text_input']) ? trim($data['text_input']) : '';
$whatsapp_number = isset($data['whatsapp_number']) ? trim($data['whatsapp_number']) : 'simulator_user';

if (empty($text_input)) {
    echo json_encode([
        'success' => false,
        'message' => 'Input teks (text_input) tidak boleh kosong'
    ]);
    exit;
}

// 1. Resolve User ID from WhatsApp Number
$user_id = null;
try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE whatsapp_number = ?");
    $stmt->execute([$whatsapp_number]);
    $user = $stmt->fetch();

    if ($user) {
        $user_id = $user['id'];
    } else {
        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (whatsapp_number, role) VALUES (?, 'user')");
        $stmt->execute([$whatsapp_number]);
        $user_id = $pdo->lastInsertId();
    }
} catch (PDOException $e) {
    // Log error internally and continue
    error_log("Database error in user resolution: " . $e->getMessage());
}

// 2. Perform AI Analysis (Gemini API or Mock Fallback)
$ai_status = 'meragukan';
$ai_explanation = '';
$is_mock = true;

if (!empty(GEMINI_API_KEY)) {
    // Setup Gemini API cURL call
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . GEMINI_API_KEY;

    $systemInstruction = "Kamu adalah KAWAL, asisten pelindung keluarga dari hoaks. Analisis teks yang dikirimkan untuk menentukan apakah itu hoaks, fakta, atau meragukan.
    Format respons harus berupa JSON murni dengan format:
    {
      \"status\": \"hoaks\" | \"fakta\" | \"meragukan\",
      \"explanation\": \"<penjelasan klarifikasi>\"
    }
    Ketentuan penjelasan:
    - Gunakan bahasa Indonesia yang sangat santun, hangat, dan tidak menggurui.
    - Sapa dengan panggilan kekeluargaan yang hangat (seperti Om, Tante, Bapak, Ibu, Kakak).
    - Awali penjelasan dengan label klarifikasi yang tebal, contoh:
      - Jika hoaks: \"*⚠️ KLARIFIKASI KAWAL*\"
      - Jika fakta: \"*✅ VERIFIKASI KAWAL*\"
      - Jika meragukan: \"*ℹ️ INFORMASI KAWAL*\"
    - Sisipkan emoji agar terasa teduh, menyejukkan, dan menjaga keharmonisan keluarga.
    - Jangan tambahkan teks markdown ```json atau karakter lain di luar objek JSON tersebut.";

    $payload = [
        "contents" => [
            ["parts" => [["text" => $text_input]]]
        ],
        "systemInstruction" => [
            "parts" => [["text" => $systemInstruction]]
        ],
        "generationConfig" => [
            "responseMimeType" => "application/json",
            "temperature" => 0.2
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $responseDecoded = json_decode($response, true);
        
        // Parse Gemini Response Structure
        if (isset($responseDecoded['candidates'][0]['content']['parts'][0]['text'])) {
            $rawAiText = trim($responseDecoded['candidates'][0]['content']['parts'][0]['text']);
            $aiJson = json_decode($rawAiText, true);
            
            if ($aiJson && isset($aiJson['status']) && isset($aiJson['explanation'])) {
                $ai_status = strtolower($aiJson['status']);
                $ai_explanation = $aiJson['explanation'];
                $is_mock = false;
                
                // Validate status enum
                if (!in_array($ai_status, ['hoaks', 'fakta', 'meragukan'])) {
                    $ai_status = 'meragukan';
                }
            }
        }
    } else {
        error_log("Gemini API call failed. HTTP Code: $httpCode. Curl Error: $curlError. Response: $response");
    }
}

// 3. Fallback to Rule-Based Mock Analyzer if Gemini is not set or failed
if ($is_mock) {
    $lowerText = strtolower($text_input);
    
    // Keyword rules for mock categorization
    $hoaxKeywords = ['hadiah', 'menang', 'link', 'kuota gratis', 'bantuan sosial', 'bansos', 'dana hibah', 'covid', 'virus', 'vaksin', 'pulsa gratis', 'giveaway'];
    $factKeywords = ['resmi', 'pemerintah', 'website resmi', 'kemkes', 'kemkominfo', 'presiden', 'deklarasi'];
    
    $containsHoax = false;
    foreach ($hoaxKeywords as $kw) {
        if (strpos($lowerText, $kw) !== false) {
            $containsHoax = true;
            break;
        }
    }
    
    $containsFact = false;
    foreach ($factKeywords as $kw) {
        if (strpos($lowerText, $kw) !== false) {
            $containsFact = true;
            break;
        }
    }
    
    if ($containsHoax) {
        $ai_status = 'hoaks';
        $ai_explanation = "*⚠️ KLARIFIKASI KAWAL*\n\nHalo Om/Tante/Bapak/Ibu tercinta... 😊\n\nKawal baru saja menganalisis informasi tentang hal tersebut. Setelah diteliti lebih lanjut, kabar ini sepertinya kurang valid dan berpotensi merupakan HOAKS atau upaya penipuan. \n\nModus seperti ini sering menyebarkan tautan palsu untuk mengambil data penting kita. Mohon agar tidak disebarkan dulu ke grup keluarga ya Om/Tante, demi keamanan kita bersama. \n\nSemoga kita semua selalu dilindungi dari info bohong. Tetap semangat dan sehat selalu ya! 🌸✨\n\n*(Catatan: Sistem berjalan dalam Demo Mode)*";
    } elseif ($containsFact) {
        $ai_status = 'fakta';
        $ai_explanation = "*✅ VERIFIKASI KAWAL*\n\nHalo Om/Tante/Bapak/Ibu... 😊\n\nKawal sudah memeriksa berita tersebut. Kabar baiknya, informasi ini merupakan FAKTA yang bersumber dari lembaga resmi tepercaya. \n\nSilakan dibagikan agar keluarga kita juga mendapat info yang benar. Terima kasih sudah menyaring sebelum membagikan! Sehat selalu ya Om/Tante! 💙";
    } else {
        $ai_status = 'meragukan';
        $ai_explanation = "*ℹ️ INFORMASI KAWAL*\n\nHalo Om/Tante/Bapak/Ibu... 😊\n\nKawal sudah membaca informasinya. Saat ini status info tersebut masih MERAGUKAN karena belum ada konfirmasi resmi dari pihak berwenang.\n\nUntuk sementara, lebih baik kita tidak terburu-buru menyebarkannya ya Om/Tante, agar tidak menimbulkan kepanikan di grup. Kawal akan terus memantau info ini. Tetap teduh dan jaga kesehatan! 🙏✨\n\n*(Catatan: Sistem berjalan dalam Demo Mode)*";
    }
}

// 4. Log the query to wacana_logs
try {
    $stmt = $pdo->prepare("INSERT INTO wacana_logs (user_id, text_input, ai_analysis, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $text_input, $ai_explanation, $ai_status]);
} catch (PDOException $e) {
    error_log("Failed to insert wacana log: " . $e->getMessage());
}

// 5. Return Output
echo json_encode([
    'success' => true,
    'status' => $ai_status,
    'explanation' => $ai_explanation,
    'mode' => $is_mock ? 'mock' : 'ai'
]);
?>
