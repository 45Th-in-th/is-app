<?php
// กำหนดส่วนหัวให้ส่งข้อมูลเป็น JSON
header('Content-Type: application/json');

// รับข้อมูลที่ส่งมาจาก JavaScript
$request = json_decode(file_get_contents('php://input'), true);

// ฟังก์ชันสำหรับโหลดข้อความจาก JSON ไฟล์
function loadResponses($folderPath) {
    $responses = [];
    $files = glob($folderPath . '/*.json'); // ดึงรายชื่อไฟล์ JSON ทั้งหมดในโฟลเดอร์

    foreach ($files as $file) {
        $jsonData = json_decode(file_get_contents($file), true);
        if ($jsonData) {
            $responses = array_merge($responses, $jsonData); // รวมข้อมูล JSON จากทุกไฟล์
        }
    }

    return $responses;
}

// โหลดข้อความตอบกลับจากโฟลเดอร์
$responseFolder = __DIR__ . '/data/การบริหาร/พรบ'; // ระบุเส้นทางไปยังโฟลเดอร์ JSON
$responses = loadResponses($responseFolder);

// ตรวจสอบข้อความและสร้างการตอบกลับ
if (isset($request['message'])) {
    $userMessage = strtolower(trim($request['message'])); // ข้อความจากผู้ใช้
    $responseMessage = "ขอโทษครับ ผมไม่เข้าใจคำถามของคุณ"; // ข้อความเริ่มต้น

    // ค้นหาคำตอบใน JSON
    foreach ($responses as $key => $value) {
        if (strpos($userMessage, $key) !== false) {
            $responseMessage = $value; // หากพบคำตอบใน JSON
            break;
        }
    }

    // ส่งการตอบกลับกลับไป
    echo json_encode(['reply' => $responseMessage]);
} else {
    echo json_encode(['reply' => "No message received."]);
}
?>
