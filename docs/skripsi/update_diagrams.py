import re
import os

file_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\generate_monochrome_diagrams.py'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Remove Titles
content = re.sub(r'<!-- Title -->\s*<text[^>]*>.*?</text>', '', content)

# 2. Increase Font Sizes
replacements = {
    'font-size="10px"': 'font-size="14px"',
    'font-size="11px"': 'font-size="15px"',
    'font-size="11.5px"': 'font-size="16px"',
    'font-size="12px"': 'font-size="17px"',
    'font-size="13px"': 'font-size="18px"',
    'font-size="14px"': 'font-size="19px"'
}

for old, new in replacements.items():
    content = content.replace(old, new)

# 3. Increase Use Case Ellipse rx to prevent overflow, and slightly shift centers
content = content.replace('rx="95" ry="20"', 'rx="120" ry="25"')
# Also adjust the x-position of usecase text so it doesn't get messed up if they are centered, 
# wait, <text x="100" y="24" ... text-anchor="middle"> which is exactly the center of ellipse cx="100".
# so just expanding rx="120" makes the ellipse wider, text remains centered. Perfect.

# ERD Table width: <rect x="0" y="0" width="180" height="150" ...
# increase widths from 180 to 220, 200 to 240, etc to give more room for larger fonts
content = re.sub(r'<rect x="0" y="0" width="180"', '<rect x="0" y="0" width="220"', content)
content = re.sub(r'<rect x="0" y="0" width="200"', '<rect x="0" y="0" width="240"', content)
content = re.sub(r'<rect x="0" y="0" width="220"', '<rect x="0" y="0" width="260"', content)

# Update x2 coordinate of lines in ERD tables accordingly (e.g. x2="180" -> x2="220")
content = re.sub(r'x2="180" y2="(\d+)" stroke="#dddddd"', r'x2="220" y2="\1" stroke="#dddddd"', content)
content = re.sub(r'x2="200" y2="(\d+)" stroke="#dddddd"', r'x2="240" y2="\1" stroke="#dddddd"', content)
content = re.sub(r'x2="220" y2="(\d+)" stroke="#dddddd"', r'x2="260" y2="\1" stroke="#dddddd"', content)

# Update text-anchor="end" x coordinates (e.g. x="172" -> x="212", x="192" -> x="232")
content = re.sub(r'x="172" y="(\d+)" font-family', r'x="212" y="\1" font-family', content)
content = re.sub(r'x="192" y="(\d+)" font-family', r'x="232" y="\1" font-family', content)
content = re.sub(r'x="212" y="(\d+)" font-family', r'x="252" y="\1" font-family', content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print('File updated successfully.')
