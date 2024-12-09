<?php
header('Content-Type: application/json');

// รับข้อมูล JSON จาก JavaScript
$request = json_decode(file_get_contents('php://input'), true);
$message = $request['message'] ?? '';
$jsonData = $request['jsonData'] ?? [];

// ฟังก์ชันค้นหาคำตอบใน JSON
function findAnswer($data, $message) {
    $results = []; // เก็บข้อความทั้งหมดที่ตรงกัน

    // แยกข้อความของผู้ใช้เป็นคำ ๆ เพื่อค้นหา
    $keywords = array_filter(explode(' ', $message), fn($word) => !empty(trim($word))); // ตัดคำที่ว่างเปล่าออก
    $totalKeywords = count($keywords);

    foreach ($data as $entry) {
        $matchedKeywords = []; // เก็บคำที่ตรงกันใน entry นี้

        // ตรวจสอบฟิลด์ทุกฟิลด์ของ JSON
        foreach ($entry as $field => $value) {
            if (is_string($value)) {
                foreach ($keywords as $keyword) {
                    if (stripos($value, $keyword) !== false) {
                        $matchedKeywords[] = $keyword; // เก็บคำที่ตรงกัน
                    }
                }
            }
        }

        // หากพบคำที่ตรงใน entry นี้ ให้บันทึกพร้อมจำนวนคำที่ตรงกัน
        if (!empty($matchedKeywords)) {
            $results[] = [
                'page' => $entry['page'] ?? 'N/A',
                'line' => $entry['line'] ?? 'N/A',
                'description' => $entry['description'] ?? 'N/A',
                'matched_keywords' => $matchedKeywords,
                'match_count' => count($matchedKeywords) // นับจำนวนคำที่ตรงกัน
            ];
        }
    }

    // จัดเรียงผลลัพธ์ตามจำนวนคำที่ตรงกัน (มากไปน้อย)
    usort($results, function ($a, $b) {
        return $b['match_count'] - $a['match_count'];
    });

    // แยกข้อความที่ตรงทั้งหมดคำ และข้อความที่ตรงบางคำ
    $exactMatches = array_filter($results, fn($result) => $result['match_count'] === $totalKeywords);
    $partialMatches = array_filter($results, fn($result) => $result['match_count'] < $totalKeywords);

    // หากมีข้อความที่ตรงทั้งหมดคำ ให้แสดงทั้งหมด
    if (!empty($exactMatches)) {
        return $exactMatches;
    }

    // หากไม่มีข้อความที่ตรงทั้งหมดคำ ให้แสดงข้อความที่ตรงบางคำ (ไม่เกิน 7 ข้อความ)
    return array_slice($partialMatches, 0, 5);
}

// เรียกใช้ฟังก์ชันค้นหา
$searchResults = findAnswer($jsonData, $message);

// สร้างคำตอบ
if (!empty($searchResults)) {
    $response = "พบข้อมูลดังนี้:\n";
    foreach ($searchResults as $result) {
        $response .= "- [Page: {$result['page']}, Line: {$result['line']}] คำที่พบ: " . implode(', ', $result['matched_keywords']) . "\n";
        $response .= "  ข้อความ: {$result['description']} (จำนวนคำที่ตรงกัน: {$result['match_count']})\n";
    }
} else {
    $response = "ขอโทษครับ ผมไม่พบข้อมูลที่คุณต้องการ";
}

// ส่งคำตอบกลับ
echo json_encode(['reply' => $response]);
?>
