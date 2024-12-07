import pdfplumber
import json

def get_table_bbox(results):
    tables_coordinates = []

    # iterate through all the detected table data
    for score, label, box in zip(results["scores"], results["labels"], results["boxes"]):
        box = [round(i, 2) for i in box.tolist()]

        # store bbox coordinates in Pascal VOC format for later use
        table_dict = {"xmin": box[0],
                      "ymin": box[1],
                      "xmax": box[2],
                      "ymax": box[3]}

        tables_coordinates.append(table_dict)

        # print prediction label, prediction confidence score, and bbox values
        print(
            f"Detected {table_detection_model.config.id2label[label.item()]} with confidence "
            f"{round(score.item(), 3)} at location {box}"
        )

    return tables_coordinates

def extract_text_from_pdf(pdf_path):
    all_text = ""
    with pdfplumber.open(pdf_path) as pdf:
        for page_num, page in enumerate(pdf.pages, start=1):
            text = page.extract_text()
            if text:
                all_text += f"Page {page_num}:\n{text}\n\n"
    return all_text

def save_as_json(data, output_path):
    with open(output_path, 'w', encoding='utf-8') as json_file:
        json.dump(data, json_file, ensure_ascii=False, indent=4)
    print(f"Data saved to {output_path} (JSON)")

def main(pdf_path, output_format="text"):
    # Extract text from the PDF
    text = extract_text_from_pdf(pdf_path)

    if output_format == "text":
        # Save as text file
        output_text_path = pdf_path.replace(".pdf", "_output.txt")
        with open(output_text_path, "w", encoding='utf-8') as text_file:
            text_file.write(text)
        print(f"Text saved to {output_text_path}")

    elif output_format == "json":
        # Prepare data for JSON
        output_json_path = pdf_path.replace(".pdf", "_output.json")
        data = {"content": text}
        save_as_json(data, output_json_path)

# Example usage
if __name__ == "__main__":
    pdf_path = "sample.pdf"  # Specify your PDF path here
    main(pdf_path, output_format="json")  # Change to "text" if you want a text file output
