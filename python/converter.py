from pdf2docx import Converter

pdf_file  = 'example1.pdf'
docx_file = 'example1.docx'

# convert pdf to docx
cv = Converter(pdf_file)
cv.convert(docx_file, start=0, end=None)
cv.close()