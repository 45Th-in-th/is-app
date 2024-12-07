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
        </div>
        <div id="chat-box" class="chat-box"></div>
        <div class="input-container">
            <input type="text" id="user-input" placeholder="พิมพ์คำถามของท่าน...">
            <button id="send-btn">ส่งคำถาม</button>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById("chat-box");
        const userInput = document.getElementById("user-input");
        const sendBtn = document.getElementById("send-btn");

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
            fetch("chatbot2.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ message: userMessage }),
            })
                .then(response => response.json())
                .then(data => {
                    addMessage(data.reply, "bot-message");
                })
                .catch(error => {
                    console.error("Error:", error);
                    addMessage("ขอโทษครับ ระบบเกิดปัญหา", "bot-message");
                });
        };
    </script>
</body>
</html>
