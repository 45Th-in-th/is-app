<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่ามีการอัปโหลดไฟล์และระบุชื่อไฟล์ปลายทาง
    if (isset($_FILES['inputFile']) && isset($_POST['outputFile']) && isset($_POST['outputPath'])) {
        $inputFile = $_FILES['inputFile']['tmp_name']; // ไฟล์ต้นฉบับที่อัปโหลด
        $outputFile = $_POST['outputFile']; // ชื่อไฟล์ที่ต้องการบันทึก
        $outputPath = rtrim($_POST['outputPath'], '/') . '/'; // โฟลเดอร์ปลายทาง (ลบ "/" ด้านท้ายแล้วเพิ่มใหม่)
        $outputFullPath = $outputPath . $outputFile; // รวมเส้นทางปลายทางกับชื่อไฟล์

        // ตรวจสอบว่าโฟลเดอร์ปลายทางมีอยู่หรือไม่
        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0777, true); // สร้างโฟลเดอร์ใหม่ถ้ายังไม่มี
        }

        // ฟังก์ชันสำหรับปรับโครงสร้าง JSON
        function processJson($inputFile, $outputFullPath) {
            if (!file_exists($inputFile)) {
                return "ไฟล์ต้นฉบับไม่พบ: $inputFile";
            }

            // อ่านข้อมูล JSON
            $jsonData = json_decode(file_get_contents($inputFile), true);

            // ตรวจสอบโครงสร้าง JSON
            if (isset($jsonData['content']) && is_array($jsonData['content'])) {
                $structuredData = []; // สำหรับจัดเก็บโครงสร้างใหม่

                foreach ($jsonData['content'] as $paragraph) {
                    // ใช้ regex หรือคำสำคัญเพื่อค้นหาหัวข้อ
                    if (preg_match('/มาตรา\s*(\d+)/u', $paragraph, $matches)) {
                        $key = "มาตรา " . $matches[1];
                    } else {
                        $key = "อื่น ๆ";
                    }

                    // เพิ่มข้อมูลในโครงสร้างใหม่
                    if (!isset($structuredData[$key])) {
                        $structuredData[$key] = [];
                    }
                    $structuredData[$key][] = trim($paragraph);
                }

                // บันทึกโครงสร้างใหม่
                file_put_contents($outputFullPath, json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                return "ปรับโครงสร้าง JSON สำเร็จ! บันทึกใน: $outputFullPath";
            } else {
                return "โครงสร้าง JSON ไม่ถูกต้องหรือไม่มีข้อมูล 'content'";
            }
        }

        // ประมวลผลไฟล์
        $result = processJson($inputFile, $outputFullPath);
        echo "<p>$result</p>";
    } else {
        echo "<p>กรุณาเลือกไฟล์ต้นฉบับ ระบุชื่อไฟล์ และปลายทาง</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปรับโครงสร้าง JSON</title>
</head>
<body>
    <h1>ปรับโครงสร้าง JSON</h1>
    <form method="POST" action="" enctype="multipart/form-data">
        <label for="inputFile">เลือกไฟล์ JSON ต้นฉบับ:</label>
        <input type="file" id="inputFile" name="inputFile" accept=".json" required>
        <br><br>
        <label for="outputFile">ระบุชื่อไฟล์ JSON ที่ต้องการบันทึก (เช่น structured.json):</label>
        <input type="text" id="outputFile" name="outputFile" required>
        <br><br>
        <label for="outputPath">เลือกปลายทางโฟลเดอร์:</label>
        <input type="text" id="outputPath" name="outputPath" placeholder="เช่น /path/to/destination" required readonly>
        <button type="button" onclick="selectFolder()">เลือกโฟลเดอร์</button>
        <br><br>
        <button type="submit">ปรับโครงสร้าง JSON</button>
    </form>

    <script>
        async function selectFolder() {
            try {
                // เรียกใช้งาน Directory Picker API
                const directoryHandle = await window.showDirectoryPicker();
                const directoryPath = directoryHandle.name; // ชื่อโฟลเดอร์
                document.getElementById('outputPath').value = directoryPath;
            } catch (err) {
                console.error('Error selecting folder:', err);
            }
        }
    </script>
</body>
</html>
