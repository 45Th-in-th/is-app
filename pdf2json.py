import pdfplumber
import json
import tkinter as tk
from tkinter import filedialog

# สร้างหน้าต่างหลักของ tkinter
root = tk.Tk()
root.withdraw()  # ซ่อนหน้าต่างหลัก

# เปิดหน้าต่างให้ผู้ใช้เลือกไฟล์ PDF ต้นฉบับ
pdf_file_path = filedialog.askopenfilename(title="เลือกไฟล์ PDF ต้นฉบับ", filetypes=[("PDF files", "*.pdf")])

if pdf_file_path:
    # เปิดหน้าต่างให้ผู้ใช้เลือกที่บันทึกและตั้งชื่อไฟล์ output JSON
    output_file_path = filedialog.asksaveasfilename(title="บันทึกไฟล์ JSON เป็น", defaultextension=".json", filetypes=[("JSON files", "*.json")])

    if output_file_path:
        # เปิดไฟล์ PDF ที่เลือก
        with pdfplumber.open(pdf_file_path) as pdf:
            all_text = []
            
            # วนลูปอ่านแต่ละหน้า
            for page in pdf.pages:
                text = page.extract_text()  # ดึงข้อความจากหน้า
                all_text.append(text)  # เพิ่มข้อความที่ดึงได้ลงใน list

        # แปลงข้อมูลเป็น JSON
        data = {
            "content": all_text
        }

        # บันทึกข้อมูล JSON ลงในไฟล์
        with open(output_file_path, "w", encoding='utf-8') as json_file:
            json.dump(data, json_file, ensure_ascii=False, indent=4)

        print("PDF ถูกแปลงเป็น JSON เรียบร้อยแล้ว!")
    else:
        print("ยกเลิกการบันทึกไฟล์ JSON")
else:
    print("ยกเลิกการเลือกไฟล์ PDF")
