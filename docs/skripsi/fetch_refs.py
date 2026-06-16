import urllib.request
import json
import urllib.parse
import re

refs = [
    ("Ahmad", "What Storage? An Empirical Analysis of Web Storage in the Wild"),
    ("Aiken", "Three Coefficients for Analyzing the Reliability and Validity of Ratings"),
    ("Akbari", "Higher education digital transformation implementation in Indonesia during the COVID-19 pandemic"),
    ("Al Salmi", "Comparative CSS frameworks"),
    ("Alisa", "Analisis Kualitas Pelayanan ULT (Unit Layanan Terpadu) Universitas dalam Menciptakan Pengalaman yang Memuaskan"),
    ("Andi Kambau", "Proses Transformasi Digital pada Perguruan Tinggi di Indonesia"),
    ("Anggraini", "Developing Learning Video with Addie Model on Science Class For 4 th Grade Elementary School Students"),
    ("Ardiansyah", "Transformasi Digital Perguruan Tinggi Menggunakan Prinsip Smart Education"),
    ("Ardianti", "Pengembangan Website Berbasis Model Addie Sebagai Media E-Learning Pada Pembelajaran Apresiasi Seni Lukis Di Sma"),
    ("Bashar", "Optimizing the Student Application Process with a Laravel-based System of Waziri Umaru Federal Polytechnic Birnin Kebbi: A Case Study"),
    ("Bhagaskara", "Implementation of Web Engineering in the Design and Development of the Website Portal for SMA Negeri 1 Martapura"),
    ("Biradar", "Tailwind CSS"),
    ("Bitchikashvili", "Digitalization of Management of A Higher Educational Institution, National Aandnd International Challenges and Ways of Solution"),
    ("Brooke", "SUS: A Quick and Dirty Usability Scale"),
    ("Deshpande", "Web engineering"),
    ("Dixit", "Revolutionizing Web Design with Tailwind CSS: A Comprehensive Exploration"),
    ("Febriani", "Perancangan UI / UX Aplikasi Sistem Informasi Layanan Administrasi dalam Perspektif Psikologi Menggunakan Metode Prototype Mahasiswa Universitas Bina Darma"),
    ("Ferdiansyah", "Analisis User Experience (Ux) Pada Website Universitas Singaperbangsa Karawang Menggunakan Metode System Usability Scale (SUS)"),
    ("Fernández", "Digital transformation initiatives in higher education institutions: A multivocal literature review"),
    ("Gote", "Real-Time Interactivity in Hybrid Applications With Web Sockets"),
    ("Gustiani", "Research and Development (R&D) Method as a Model Design in Educational Research and Its Alternatives"),
    ("Haryanto", "Analisis Kualitas Pelayanan Publik Unit Layanan Terpadu (Ult) Satu Pintu Kementerian Pendidikan Kebudayaan Riset Dan Teknologi Di D.I. Yogyakarta"),
    ("Henim", "Evaluasi User Experience Sistem Informasi Akademik Mahasiswa pada Perguruan Tinggi Menggunakan User Experience Questionnaire"),
    ("Huynh", "Design and Implementation of Web Application Based on MVC Laravel Architecture"),
    ("Johns", "Service Upgrade: The GovTech Approach to Citizen Centered Services"),
    ("Joseph", "Digital transformation in education: Strategies for effective implementation"),
    ("Kancherla", "Least Privilege Access for Persistent Storage Mechanisms in Web Browsers"),
    ("Karya", "Digitalization and Innovation of the Service Process: The Efforts to Improve the Quality of Higher Education Services in Kalimantan Island"),
    ("Khotimah", "Reading in The Digital Age: Electronic Storybook as a Teaching Tool for Beginning Readers"),
    ("Kodali", "Tailwind CSS Integration in Angular: A Technical Overview"),
    ("Kryszak", "Income Convergence in the Agricultural Sector in the Context of the European Union'S Common Agricultural Policy"),
    ("Latif", "Tren Modern dalam Digitalisasi Pendidikan"),
    ("Machmudi", "Analisis dan Rancang Bangun E-Learning dengan Metode ADDIE Model"),
    ("MacKlon", "A Taxonomy of Testable HTML5 Canvas Issues"),
    ("Martantoh", "Sistem Informasi Penjualan Berbasis Web Dengan Metode Addie Pada Kedai Kopi Karawang"),
    ("Maulana", "Uji Usability dan User Experience Website Sistem Informasi Akademik Universitas Terbuka (SIA UT) Berdasarkan Perspektif Mahasiswa Menggunakan Metode USE Questionnaire dan Cognitive Walkthrough"),
    ("Mundzir", "Transformasi Administrasi Pendidikan di Era Digital: Tantangan dan Peluang"),
    ("Mustopa", "Analisa Kepuasan Pengguna Website Layanan Akademik Kemahasiswaan (LYKAN) Menggunakan Metode Webqual 4.0"),
    ("Musyary", "Laravel Framework-Based Information System of the Department of Information Technology of Universitas Muhammadiyah Yogyakarta"),
    ("Naidu", "Html5 Based E-Learning Authoring To Facilitate Interactive Learning During Covid-19 Pandemic: a Review"),
    ("Nandan", "Comparison of Utility-First CSS Framework"),
    ("Nugroho", "Digitalization in Higher Education: How Information Systems Improve Operational and Strategic Performance"),
    ("Oracle", "MySQL Replication"),
    ("Poernamawatie", "Implementasi Transformasi Digital Layanan Keuangan Mahasiswa Pasca Pandemi Covid-19 di Universitas Negeri Malang"),
    ("Purwani", "Transformasi Administrasi Pendidikan untuk Mengoptimalkan Efisiensi dan Kualitas Layanan Pendidikan pada Era Digital"),
    ("Qurrata", "Website quality and user satisfaction: A higher education perspective"),
    ("Ridwan", "Usability Testing Website My UT Menggunakan Metode Post-Study System Usability Questionnaire Berdasarkan Pandangan Mahasiswa Universitas Terbuka"),
    ("Rifandi", "Website Gallery Development Using Tailwind CSS Framework"),
    ("Riyadi", "Implementasi Metode Addie Pada Sistem Informasi Pembuatan Rpp 1 Lembar Di Smk Pgri 4 Pasuruan"),
    ("Safrizal", "SIAKAD Revitalization: The Latest Solution in Answering the Challenges of Digitizing Education"),
    ("Sasmoko", "Analyzing Database Optimization Strategies in Laravel for an Enhanced Learning Management"),
    ("Sastranegara", "Implementasi User Center Design dan System Usability Scale Pada Website Sekolah Menengah Atas"),
    ("Serhiyovych", "Asynchronous Programming in Javascript: Modern Approaches and Practice"),
    ("Setiawan", "Peran software, hardware dan brainware dalam sistem informasi manajemen sekolah"),
    ("Setyaningsih", "Pelaksanaan Layanan Administrasi Kesiswaan di MAN 2 Palembang"),
    ("Shah", "Harnessing Customized Built-in Elements: Empowering Component-Based Software Engineering and Design Systems with HTML5 Web Components"),
    ("Sinaga", "The role of database system on secondary education"),
    ("Singun", "Unveiling the barriers to digital transformation in higher education institutions: a systematic literature review"),
    ("Suhartoyo", "Implementasi Fungsi Pelayanan Publik dalam Pelayanan Terpadu Satu Pintu (PTSP)"),
    ("Sukorini", "NEW ERA OF HIGHER EDUCATION: DIGITAL TRANSFORMATION AND INFORMATION SYSTEM MANAGEMENT"),
    ("Šušter", "Optimization of Mysql Database"),
    ("Taqwa", "Website-Based Academic Service Development with ADDIE Design in Higher Education"),
    ("Tumilantouw", "Strategi Pelayanan Publik Pada Unit Layanan Terpadu Universitas Negeri Manado"),
    ("Wan Ali", "Waterfall-Addie Model: an Integration of Software Development Model and Instructional Systems Design in Developing a Digital Video Learning Application"),
    ("Widiawati", "ANALISIS USABILITY WEBSITE SISTEM INFORMASI AKADEMIK STMIK usability , USE Questionnaire , Website Sistem Informasi Akademik STMIK Kharisma Makassar"),
    ("Zapata", "MySQL VS PostgreSQL: A Comparative Analysis of Relational Database Management Systems (RDBMS) Technologies Response Time in Web-based E-commerce")
]

import time

results = []
for author, title in refs:
    q = urllib.parse.quote(title)
    url = f'https://api.crossref.org/works?query.title={q}&select=title,author,published,container-title,volume,issue,page,URL&rows=1'
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'mailto:test@example.com'})
        resp = urllib.request.urlopen(req, timeout=5)
        data = json.loads(resp.read().decode())
        if data['message']['items']:
            item = data['message']['items'][0]
            if title.lower()[:20] in item.get('title', [''])[0].lower():
                item['my_query'] = title
                results.append(item)
                continue
    except Exception as e:
        pass
    results.append({'query': title, 'error': 'not found'})
    time.sleep(0.1)

open(r'c:\laragon\www\ult-fkip-unila\docs\skripsi\crossref_results.json', 'w', encoding='utf-8').write(json.dumps(results, indent=2))
print('Done!')
