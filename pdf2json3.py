import pdfplumber
import json
import re
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
        result_data = {}
        current_keyword = None
        current_text = ""

        # คำสำคัญที่เกี่ยวข้องกับกฎหมายและพระราชบัญญัติ
        keywords = ["กฎหมาย", "พระราชบัญญัติ", "มาตรา ", "ข้อกำหนด", "ข้อบังคับ", "พ.ร.บ.","หมวด ",]

        # วนลูปอ่านแต่ละหน้า
        for page_num, page in enumerate(pdf.pages, start=1):
            text = page.extract_text()  # ดึงข้อความจากหน้า

            if text:
                paragraphs = text.split("\n\n")  # แยกข้อความเป็นย่อหน้า

                for paragraph in paragraphs:
                    # ตรวจสอบว่ามีคำสำคัญในย่อหน้าหรือไม่
                    found_keyword = None
                    for keyword in keywords:
                        if re.search(rf"\b{keyword}\b", paragraph):
                            found_keyword = keyword
                            break

                    if found_keyword:
                        # ถ้าเปลี่ยนคำสำคัญ ให้บันทึกข้อความสะสมลงใน `result_data`
                        if current_keyword and current_keyword != found_keyword:
                            if current_keyword not in result_data:
                                result_data[current_keyword] = []  # สร้างฟิลด์สำหรับคำสำคัญ
                            result_data[current_keyword].append({
                                "page": page_num,
                                "title": current_keyword,
                                "text": current_text.strip()
                            })
                            current_text = ""  # รีเซ็ตข้อความสะสม

                        current_keyword = found_keyword  # อัปเดตคำสำคัญปัจจุบัน

                    # สะสมข้อความในย่อหน้าปัจจุบัน
                    current_text += paragraph.strip() + "\n"

        # บันทึกข้อความที่เหลือจากคำสำคัญสุดท้าย
        if current_keyword and current_text.strip():
            if current_keyword not in result_data:
                result_data[current_keyword] = []  # สร้างฟิลด์สำหรับคำสำคัญ
            result_data[current_keyword].append({
                "page": page_num,
                "title": current_keyword,
                "text": current_text.strip()
            })

    # บันทึกข้อมูล JSON ลงในไฟล์
    with open(output_file_path, "w", encoding='utf-8') as json_file:
        json.dump(result_data, json_file, ensure_ascii=False, indent=4)

    print("PDF ถูกแปลงเป็น JSON พร้อมคำสำคัญเรียบร้อยแล้ว!")
else:
    print("ยกเลิกการบันทึกไฟล์ JSON")
