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
        result_data = []

        # คำสำคัญที่เกี่ยวข้องกับกฎหมายและพระราชบัญญัติ
        keywords = ["กฎหมาย", "พระราชบัญญัติ", "มาตรา", "ข้อกำหนด", "ข้อบังคับ", "พ.ร.บ."]

        # วนลูปอ่านแต่ละหน้า
        for page_num, page in enumerate(pdf.pages, start=1):
            text = page.extract_text()  # ดึงข้อความจากหน้า

            if text:
                lines = text.split("\n")  # แยกข้อความเป็นบรรทัด

                for line_num, line in enumerate(lines, start=1):
                    found_keywords = [keyword for keyword in keywords if re.search(rf"\b{keyword}\b", line)]

                    if found_keywords:
                        # เพิ่มข้อมูลใน result_data
                        result_data.append({
                            "page": page_num,
                            "keyword": ", ".join(found_keywords),  # รวมคำสำคัญที่พบในบรรทัดนั้น
                            "line": line_num,
                            "description": line.strip()
                        })

    # บันทึกข้อมูล JSON ลงในไฟล์
    with open(output_file_path, "w", encoding='utf-8') as json_file:
        json.dump(result_data, json_file, ensure_ascii=False, indent=4)

    print("PDF ถูกแปลงเป็น JSON พร้อมคำสำคัญเรียบร้อยแล้ว!")
else:
    print("ยกเลิกการบันทึกไฟล์ JSON")
