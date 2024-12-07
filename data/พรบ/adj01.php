<?php
// อ่านไฟล์ JSON ต้นฉบับ
$inputFile = '01.json'; // ชื่อไฟล์ต้นฉบับ
$outputFile = 'structured_laws.json'; // ชื่อไฟล์ที่บันทึกใหม่

$jsonData = json_decode(file_get_contents($inputFile), true);

if (isset($jsonData['content']) && is_array($jsonData['content'])) {
    $structuredData = ["laws" => []];

    foreach ($jsonData['content'] as $paragraph) {
        if (preg_match('/มาตรา\s*(\d+)/u', $paragraph, $matches)) {
            $section = "มาตรา " . $matches[1];
            $title = ""; // คุณสามารถเพิ่มการดึงหัวข้อได้โดยการเพิ่มตรรกะเพิ่มเติม
            $content = trim($paragraph);

            // สร้าง keywords จากเนื้อหาหรือปรับเอง
            $keywords = ["มาตรา", $matches[1]];

            $structuredData["laws"][] = [
                "section" => $section,
                "title" => $title,
                "content" => $content,
                "keywords" => $keywords
            ];
        }
    }

    // บันทึกโครงสร้างใหม่ในไฟล์ JSON
    file_put_contents($outputFile, json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo "ปรับโครงสร้าง JSON สำเร็จ! บันทึกในไฟล์: $outputFile";
} else {
    echo "โครงสร้าง JSON ไม่ถูกต้องหรือไม่มีข้อมูล 'content'";
}
