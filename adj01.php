<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่ามีการอัปโหลดไฟล์ Input และระบุตำแหน่ง Output
    if (isset($_FILES['inputFile']) && isset($_POST['outputFile']) && isset($_POST['outputPath'])) {
        $inputFileTempPath = $_FILES['inputFile']['tmp_name']; // เส้นทางไฟล์ Input ที่อัปโหลด
        $outputPath = rtrim($_POST['outputPath'], '/') . '/'; // โฟลเดอร์ Output
        $outputFile = $_POST['outputFile']; // ชื่อไฟล์ Output
        $outputFullPath = $outputPath . $outputFile; // เส้นทางเต็มของไฟล์ Output

        // ตรวจสอบและสร้างโฟลเดอร์ Output หากยังไม่มี
        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0777, true);
        }

        // อ่านข้อมูลจากไฟล์ Input
        $jsonData = json_decode(file_get_contents($inputFileTempPath), true);

        if (isset($jsonData['content']) && is_array($jsonData['content'])) {
            $structuredData = ["laws" => []];

            foreach ($jsonData['content'] as $paragraph) {
                if (preg_match('/มาตรา\s*(\d+)/u', $paragraph, $matches)) {
                    $section = "มาตรา " . $matches[1];
                    $title = ""; // เพิ่มการดึงหัวข้อได้ถ้าจำเป็น
                    $content = trim($paragraph);

                    $structuredData["laws"][] = [
                        "section" => $section,
                        "title" => $title,
                        "content" => $content
                    ];
                }
            }

            // บันทึกโครงสร้างใหม่ในไฟล์ JSON
            file_put_contents($outputFullPath, json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            echo "<p>โครงสร้าง JSON ได้รับการปรับปรุงและบันทึกในไฟล์: $outputFullPath</p>";
        } else {
            echo "<p>โครงสร้าง JSON ไม่ถูกต้องหรือไม่มีข้อมูล 'content'</p>";
        }
    } else {
        echo "<p>กรุณาเลือกไฟล์ต้นฉบับ และระบุชื่อไฟล์ปลายทาง</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปรับโครงสร้าง JSON</title>
    <script>
        function selectFolder(inputId) {
            const fileInput = document.createElement("input");
            fileInput.type = "file";
            fileInput.webkitdirectory = true; // เปิดให้เลือกทั้งโฟลเดอร์
            fileInput.onchange = (e) => {
                const files = e.target.files;
                if (files.length > 0) {
                    document.getElementById(inputId).value = files[0].webkitRelativePath.split("/")[0];
                }
            };
            fileInput.click();
        }
    </script>
</head>
<body>
    <h1>ปรับโครงสร้าง JSON</h1>
    <form method="POST" action="" enctype="multipart/form-data">
        <label for="inputFile">เลือกไฟล์ JSON ต้นฉบับ:</label>
        <input type="file" id="inputFile" name="inputFile" accept=".json" required>
        <br><br>
        <label for="outputPath">เลือกตำแหน่งโฟลเดอร์สำหรับบันทึก Output:</label>
        <input type="text" id="outputPath" name="outputPath" placeholder="เลือกโฟลเดอร์" required readonly>
        <button type="button" onclick="selectFolder('outputPath')">เลือกโฟลเดอร์</button>
        <br><br>
        <label for="outputFile">ชื่อไฟล์ Output (เช่น structured_01.json):</label>
        <input type="text" id="outputFile" name="outputFile" required>
        <br><br>
        <button type="submit">ปรับโครงสร้าง JSON</button>
    </form>
</body>
</html>
