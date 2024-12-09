
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สอมธ. Chatbot</title>
    <link rel="stylesheet" href="css/style.css">
   
</head>
<body>

<div class="chat-container">
    <div class="header">
        <img src="images/Logo.png" alt="Logo" class="logo">
        <h2>สอมธ. Chatbot</h2>
        <button id="choose-json-btn" class="choose-json-btn">เลือกไฟล์ JSON</button>
        <input type="file" id="json-file-input" accept=".json" style="display: none;" />
        <span id="json-file-name" class="json-file-name">ยังไม่ได้เลือกไฟล์</span>
    </div>
    <div id="chat-box" class="chat-box"></div>
    <div class="input-container">
        <input type="text" id="user-input" placeholder="พิมพ์คำถามของท่าน...">
        <button id="send-btn">ส่งคำถาม</button>
    </div>
    <div id="suggested-questions-container">
        <h3>คำแนะนำสำหรับคำถาม:</h3>
        <ul id="suggested-questions"></ul>
    </div>
</div>

<script>
    const chatBox = document.getElementById("chat-box");
    const userInput = document.getElementById("user-input");
    const sendBtn = document.getElementById("send-btn");
    const chooseJsonBtn = document.getElementById("choose-json-btn");
    const jsonFileInput = document.getElementById("json-file-input");
    const jsonFileName = document.getElementById("json-file-name");
    const suggestedQuestions = document.getElementById("suggested-questions");

    let currentJsonData = null; // ตัวแปรเก็บข้อมูล JSON ที่เลือก

    // ฟังก์ชันเลือกไฟล์ JSON
    chooseJsonBtn.addEventListener("click", () => {
        jsonFileInput.click(); // เปิดหน้าต่างเลือกไฟล์
    });

    jsonFileInput.addEventListener("change", (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    currentJsonData = JSON.parse(e.target.result);
                    jsonFileName.textContent = `ไฟล์ที่เลือก: ${file.name}`; // แสดงชื่อไฟล์
                    alert("ไฟล์ JSON ถูกโหลดสำเร็จ!");

                    // ดึงคำแนะนำจากไฟล์ JSON
                    displaySuggestedQuestions(currentJsonData);
                } catch (error) {
                    jsonFileName.textContent = "ไฟล์ JSON ไม่ถูกต้อง"; // แจ้งข้อผิดพลาด
                    alert("ไฟล์ JSON ไม่ถูกต้อง");
                }
            };
            reader.readAsText(file);
        } else {
            jsonFileName.textContent = "ยังไม่ได้เลือกไฟล์"; // กรณีไม่ได้เลือกไฟล์
        }
    });

    // ฟังก์ชันแสดงคำแนะนำคำถาม
    const displaySuggestedQuestions = (jsonData) => {
        suggestedQuestions.innerHTML = ""; // ล้างรายการเก่า
        Object.keys(jsonData).forEach((keyword) => {
            const li = document.createElement("li");
            li.textContent = `ลองถามเกี่ยวกับ "${keyword}"`;
            suggestedQuestions.appendChild(li);
        });
    };

    // ฟังก์ชันส่งข้อความ
    const sendMessage = () => {
        const message = userInput.value.trim();
        if (message !== "") {
            addMessage(message, "user-message");
            fetchChatbotResponse(message);
            userInput.value = "";
        }
    };

    // กดปุ่ม Enter เพื่อส่งข้อความ
    userInput.addEventListener("keypress", (event) => {
        if (event.key === "Enter") {
            sendMessage();
        }
    });

    // กดปุ่มส่งข้อความ
    sendBtn.addEventListener("click", sendMessage);

    // เพิ่มข้อความในกล่องแชท
    const addMessage = (text, className) => {
        const messageDiv = document.createElement("div");
        messageDiv.className = `message ${className}`;
        messageDiv.innerText = text;
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    };

    // ส่งข้อความไปยัง PHP และรับคำตอบ
    const fetchChatbotResponse = (userMessage) => {
        if (!currentJsonData) {
            addMessage("กรุณาเลือกไฟล์ JSON ก่อน", "bot-message");
            return;
        }

        fetch("chatbot3.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ message: userMessage, jsonData: currentJsonData }),
        })
            .then((response) => response.json())
            .then((data) => {
                addMessage(data.reply, "bot-message");
            })
            .catch((error) => {
                console.error("Error:", error);
                addMessage("ขอโทษครับ ระบบเกิดปัญหา", "bot-message");
            });
    };
</script>
</body>
</html>
