<?php
// กำหนดส่วนหัวให้ส่งข้อมูลเป็น JSON
header('Content-Type: application/json');

// รับข้อมูลที่ส่งมาจาก JavaScript
$request = json_decode(file_get_contents('php://input'), true);

// ตรวจสอบข้อความที่ได้รับ
if (isset($request['message'])) {
    $userMessage = strtolower(trim($request['message'])); // ข้อความที่ผู้ใช้ส่งมา
    $responseMessage = "ขอโทษครับ ผมไม่เข้าใจคำถามของคุณ"; // ข้อความตอบกลับเริ่มต้น

    // ระบุเส้นทาง JSON ไฟล์
    $jsonFile = __DIR__ . '/data/การบริหาร/พรบ/01.json'; // เปลี่ยนเป็นไฟล์ JSON ของคุณ
    if (file_exists($jsonFile)) {
        // อ่านข้อมูลจาก JSON ไฟล์
        $jsonData = json_decode(file_get_contents($jsonFile), true);

        // ตรวจสอบและค้นหาใน JSON
        if (isset($jsonData['content']) && is_array($jsonData['content'])) {
            foreach ($jsonData['content'] as $paragraph) {
                // ตรวจสอบว่ามีข้อความคำถามของผู้ใช้อยู่ในย่อหน้า
                if (strpos(strtolower($paragraph), $userMessage) !== false) {
                    $responseMessage = $paragraph; // หากพบข้อความ ให้ใช้ย่อหน้านั้นเป็นคำตอบ
                    break;
                }
            }
        }
    } else {
        $responseMessage = "ไม่พบไฟล์ข้อมูลที่ระบุ";
    }

    // ส่งข้อความตอบกลับ
    echo json_encode(['reply' => $responseMessage]);
} else {
    echo json_encode(['reply' => "No message received."]);
}
?>
