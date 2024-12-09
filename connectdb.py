from flask import Flask, render_template_string
import mysql.connector

app = Flask(__name__)

@app.route('/')
def index():
    output = "Starting connection...<br>"

    try:
        # สร้างการเชื่อมต่อฐานข้อมูล
        conn = mysql.connector.connect(
            host="203.131.211.13",
            user="chatbot",
            password="password",
            database="chatbot",
            port=3306
        )
        if conn.is_connected():
            output += "Connection successful!<br>"

            # สร้าง cursor เพื่อรันคำสั่ง SQL
            cursor = conn.cursor()

            # รันคำสั่ง SQL เพื่อดึงรายชื่อ tables
            cursor.execute("SHOW TABLES;")

            # แสดงผลลัพธ์
            output += "Tables in the database:<br>"
            for table in cursor.fetchall():
                output += f"- {table[0]}<br>"

            # ปิด cursor
            cursor.close()
        else:
            output += "Failed to connect!<br>"
    except mysql.connector.Error as err:
        output += f"Error: {err}<br>"
    finally:
        # ปิดการเชื่อมต่อฐานข้อมูล
        if 'conn' in locals() and conn.is_connected():
            conn.close()
            output += "Connection closed.<br>"

    return render_template_string("<html><body><h1>Database Connection</h1><p>{{ output|safe }}</p></body></html>", output=output)

if __name__ == '__main__':
    app.run(debug=True)
