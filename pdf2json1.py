
# from pdf2image import convert_from_path
from PIL import Image
import os
import tkinter as tk
from tkinter import filedialog
from fpdf import FPDF
import json
import pytesseract

# กำหนดที่ตั้งของ tesseract.exe (ต้องเปลี่ยนให้ตรงกับที่ติดตั้งไว้)
pytesseract.pytesseract.tesseract_cmd = r'C:\Program Files\Tesseract-OCR\tesseract.exe'

# ฟังก์ชันสำหรับแปลง PDF เป็นภาพและทำ OCR
def pdf_to_text(pdf_path):
    # แปลง PDF เป็นภาพ (หนึ่งหน้าใน PDF หนึ่งภาพ)
    images = convert_from_path(pdf_path)

    all_text = ""
    for i, image in enumerate(images):
        # บันทึกแต่ละภาพชั่วคราวก่อนที่จะทำ OCR
        image_path = f'page_{i+1}.png'
        image.save(image_path, 'PNG')

        # ทำ OCR กับภาพและแปลงเป็นข้อความ
        text = pytesseract.image_to_string(Image.open(image_path), lang='tha')  # ถ้าเป็นภาษาไทยให้ใช้ lang='tha'
        all_text += text

        # ลบไฟล์ภาพชั่วคราวหลังทำ OCR เสร็จ
        os.remove(image_path)
    
    return all_text

# ฟังก์ชันบันทึกข้อความเป็น PDF
def save_as_pdf(output_file_path, text):
    pdf = FPDF()
    pdf.add_page()
    pdf.set_auto_page_break(auto=True, margin=15)
    pdf.set_font("Arial", size=12)
    pdf.multi_cell(0, 10, text)
    pdf.output(output_file_path)
    print(f"ข้อความถูกบันทึกลงใน {output_file_path} เรียบร้อยแล้ว (PDF)")

# ฟังก์ชันบันทึกข้อความเป็น JSON
def save_as_json(output_file_path, text):
    data = {"content": text}
    with open(output_file_path, "w", encoding='utf-8') as json_file:
        json.dump(data, json_file, ensure_ascii=False, indent=4)
    print(f"ข้อความถูกบันทึกลงใน {output_file_path} เรียบร้อยแล้ว (JSON)")

# ฟังก์ชันบันทึกข้อความเป็นไฟล์ TXT
def save_as_txt(output_file_path, text):
    with open(output_file_path, "w", encoding='utf-8') as text_file:
        text_file.write(text)
    print(f"ข้อความถูกบันทึกลงใน {output_file_path} เรียบร้อยแล้ว (TXT)")

# ฟังก์ชันเลือกไฟล์ PDF ต้นฉบับและที่เก็บไฟล์ output
def main():
    # สร้างหน้าต่างของ tkinter
    root = tk.Tk()
    root.withdraw()  # ซ่อนหน้าต่างหลัก

    # ให้ผู้ใช้เลือกไฟล์ PDF ต้นฉบับ
    pdf_file_path = filedialog.askopenfilename(title="เลือกไฟล์ PDF ต้นฉบับ", filetypes=[("PDF files", "*.pdf")])
    if not pdf_file_path:
        print("ยกเลิกการเลือกไฟล์ PDF")
        return

    # ให้ผู้ใช้เลือกประเภทไฟล์ output
    file_type = filedialog.asksaveasfilename(title="บันทึกไฟล์เป็น", defaultextension=".txt", filetypes=[("PDF files", "*.pdf"), ("JSON files", "*.json"), ("Text files", "*.txt")])
    if not file_type:
        print("ยกเลิกการบันทึกไฟล์")
        return

    # เรียกฟังก์ชัน pdf_to_text เพื่อแปลง PDF เป็นข้อความ
    text_from_pdf = pdf_to_text(pdf_file_path)

    # ตรวจสอบชนิดไฟล์และบันทึกตามชนิดไฟล์นั้นๆ
    if file_type.endswith(".pdf"):
        save_as_pdf(file_type, text_from_pdf)
    elif file_type.endswith(".json"):
        save_as_json(file_type, text_from_pdf)
    elif file_type.endswith(".txt"):
        save_as_txt(file_type, text_from_pdf)
    else:
        print("ไม่รองรับชนิดไฟล์นี้")

# เรียกใช้งานฟังก์ชันหลัก
if __name__ == "__main__":
    main()
