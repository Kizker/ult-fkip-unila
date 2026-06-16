import docx
from docx.enum.chart import XL_CHART_TYPE
from docx.chart.data import CategoryChartData

doc = docx.Document()
chart_data = CategoryChartData()
chart_data.categories = ['Materi', 'Media', 'Sistem']
chart_data.add_series('Saran dan Masukan', (2, 2, 3))

try:
    doc.add_chart(XL_CHART_TYPE.COLUMN_CLUSTERED, chart_data)
    doc.save('test_chart.docx')
    print("SUCCESS")
except Exception as e:
    print("ERROR:", e)
