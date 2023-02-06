from pdf2docx import parse

pdf_file  = 'example1.pdf'
docx_file = 'example1.docx'

# convert pdf to docx
parse(pdf_file, docx_file, start=0, end=None)