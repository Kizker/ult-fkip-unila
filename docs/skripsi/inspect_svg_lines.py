import xml.etree.ElementTree as ET

svg_path = r"C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\03_diagram_flowchart_dokumen.svg"
tree = ET.parse(svg_path)
root = tree.getroot()

# Register namespace
ns = {"svg": "http://www.w3.org/2000/svg"}

print("Lines found in SVG:")
for line in root.findall(".//svg:line", ns):
    print(f"line: x1={line.get('x1')}, y1={line.get('y1')}, x2={line.get('x2')}, y2={line.get('y2')}, stroke={line.get('stroke')}")

print("\nPaths found in SVG:")
for path in root.findall(".//svg:path", ns):
    print(f"path: d={path.get('d')}, stroke={path.get('stroke')}")
