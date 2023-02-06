from pdf2docx import Converter
import re
 
def pdf_to_word(fileName):
    pdf_file = fileName

    name = re.findall(r"(.*?).",pdf_file)[0]
    docx_file = f"{name}.docx"
 
    cv = Converter(pdf_file)
    cv.convert(docx_file, start=0, end=None)
    cv.close()

pdf_to_word('example1.pdf');     