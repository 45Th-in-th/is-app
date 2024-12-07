<?php
// กำหนดส่วนหัวให้ส่งข้อมูลเป็น JSON
header('Content-Type: application/json');

// รับข้อมูลที่ส่งมาจาก JavaScript
$request = json_decode(file_get_contents('php://input'), true);

// ตรวจสอบข้อความและสร้างการตอบกลับแบบง่าย
if (isset($request['message'])) {
    $userMessage = strtolower(trim($request['message']));
    $responseMessage = "";

    // ตอบข้อความตามข้อความที่ผู้ใช้ส่งมา
    if ($userMessage == "สวัสดี" || $userMessage == "สวัสดี") {
        $responseMessage = "สวัสดีครับ วันนี้มีอะไรให้ผมช่วยครับ?";
    } elseif ($userMessage == "คุณสบายดีไหม" || $userMessage == "สบายดีไหม") {
        $responseMessage = "ผมเป็น chatbot วันนี้ผมสบายดี!";
    } else {
        $responseMessage = "ขอโทษครับ ผมไม่เข้าใจคำถามคุณ?";
    }

    // ส่งการตอบกลับกลับไป
    echo json_encode(['reply' => $responseMessage]);
} else {
    echo json_encode(['reply' => "No message received."]);
}
?>
