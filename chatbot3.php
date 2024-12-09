<?php
header('Content-Type: application/json');

// รับข้อมูล JSON จาก JavaScript
$request = json_decode(file_get_contents('php://input'), true);
$message = $request['message'] ?? '';
$jsonData = $request['jsonData'] ?? [];

// ฟังก์ชันค้นหาคำตอบใน JSON
function findAnswer($data, $message) {
    $results = []; // เก็บข้อความทั้งหมดที่ตรงกัน

    foreach ($data as $keyword => $entries) {
        foreach ($entries as $entry) {
            // ตรวจสอบฟิลด์ทั้งหมดในแต่ละ entry
            foreach ($entry as $field => $value) {
                if (is_string($value) && stripos($value, $message) !== false) {
                    // หากข้อความตรงกัน ให้เก็บผลลัพธ์พร้อมฟิลด์และข้อความ
                    $results[] = [
                        'keyword' => $keyword,
                        'field' => $field,
                        'value' => $value
                    ];
                }
            }
        }
    }

    return $results;
}

// เรียกใช้ฟังก์ชันค้นหา
$searchResults = findAnswer($jsonData, $message);

// สร้างคำตอบ
if (!empty($searchResults)) {
    $response = "พบข้อความดังนี้:\n";
    foreach ($searchResults as $result) {
        $response .= "- [{$result['keyword']}] {$result['field']}: {$result['value']}\n";
    }
} else {
    $response = "ขอโทษครับ ผมไม่พบข้อมูลที่คุณต้องการ";
}

// ส่งคำตอบกลับ
echo json_encode(['reply' => $response]);
?>
