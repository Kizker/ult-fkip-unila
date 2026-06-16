import os

html_content = """<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wireframe UI - ULT FKIP Unila</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Comic Neue', cursive; 
            background-color: #e5e5e5; 
            color: #374151; /* gray-700 */
        }
        
        /* The 'X' Box Placeholder */
        .placeholder-x {
            background: 
                linear-gradient(to top left,
                    transparent calc(50% - 1px),
                    #4b5563 calc(50% - 1px),
                    #4b5563 calc(50% + 1px),
                    transparent calc(50% + 1px)
                ),
                linear-gradient(to top right,
                    transparent calc(50% - 1px),
                    #4b5563 calc(50% - 1px),
                    #4b5563 calc(50% + 1px),
                    transparent calc(50% + 1px)
                );
            background-color: #f9fafb;
            border: 2px solid #4b5563;
            border-radius: 4px;
        }

        .wf-border { border: 2px solid #4b5563; }
        .wf-text { color: #374151; font-weight: bold; }
        .wf-bg { background-color: #ffffff; }
        .wf-bg-light { background-color: #f3f4f6; }

        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #9ca3af; border: 2px solid #e5e5e5; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        
        .browser-window {
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border: 3px solid #374151;
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
        }
    </style>
</head>
<body class="h-screen w-full flex overflow-hidden" x-data="{ currentTab: '1_beranda' }">

    <!-- Navigator Sidebar -->
    <aside class="w-64 bg-gray-200 border-r-3 border-gray-700 flex flex-col shrink-0 z-50 wf-border">
        <div class="p-5 border-b-2 border-gray-600">
            <h1 class="wf-text text-xl">Wireframe View</h1>
            <p class="text-sm font-bold text-gray-500 mt-1">ULT FKIP Unila</p>
        </div>
        
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 px-3 mt-2">1. Public Portal</div>
            <button @click="currentTab = '1_beranda'" :class="{'bg-gray-700 text-white': currentTab === '1_beranda', 'hover:bg-gray-300': currentTab !== '1_beranda'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors">
                Beranda Utama
            </button>
            <button @click="currentTab = '2_katalog'" :class="{'bg-gray-700 text-white': currentTab === '2_katalog', 'hover:bg-gray-300': currentTab !== '2_katalog'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors">
                Katalog Layanan
            </button>
            <button @click="currentTab = '3_berita'" :class="{'bg-gray-700 text-white': currentTab === '3_berita', 'hover:bg-gray-300': currentTab !== '3_berita'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors">
                Berita & Info
            </button>
            
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 px-3 mt-6">2. Authentication</div>
            <button @click="currentTab = '4_login'" :class="{'bg-gray-700 text-white': currentTab === '4_login', 'hover:bg-gray-300': currentTab !== '4_login'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors">
                Login & Register
            </button>

            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 px-3 mt-6">3. Student Portal</div>
            <button @click="currentTab = '5_student_dash'" :class="{'bg-gray-700 text-white': currentTab === '5_student_dash', 'hover:bg-gray-300': currentTab !== '5_student_dash'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors">
                Dasbor Mahasiswa
            </button>
            <button @click="currentTab = '6_student_form'" :class="{'bg-gray-700 text-white': currentTab === '6_student_form', 'hover:bg-gray-300': currentTab !== '6_student_form'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors">
                Form Pengajuan
            </button>
            <button @click="currentTab = '7_student_timeline'" :class="{'bg-gray-700 text-white': currentTab === '7_student_timeline', 'hover:bg-gray-300': currentTab !== '7_student_timeline'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors">
                Riwayat & Timeline
            </button>

            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 px-3 mt-6">4. Admin Portal</div>
            <button @click="currentTab = '8_admin_dash'" :class="{'bg-gray-700 text-white': currentTab === '8_admin_dash', 'hover:bg-gray-300': currentTab !== '8_admin_dash'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors">
                Dasbor Staff
            </button>
            <button @click="currentTab = '9_admin_review'" :class="{'bg-gray-700 text-white': currentTab === '9_admin_review', 'hover:bg-gray-300': currentTab !== '9_admin_review'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors">
                Detail Review
            </button>
            <button @click="currentTab = '10_admin_template'" :class="{'bg-gray-700 text-white': currentTab === '10_admin_template', 'hover:bg-gray-300': currentTab !== '10_admin_template'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors">
                Manajemen Template
            </button>

            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 px-3 mt-6">5. Signer Portal</div>
            <button @click="currentTab = '11_signer'" :class="{'bg-gray-700 text-white': currentTab === '11_signer', 'hover:bg-gray-300': currentTab !== '11_signer'}" class="w-full text-left px-3 py-2 rounded text-md font-bold transition-colors mb-8">
                Verifikasi Pejabat
            </button>
        </div>
    </aside>

    <!-- Main Viewport -->
    <main class="flex-1 relative flex flex-col p-4 md:p-8 overflow-hidden bg-gray-400">
        
        <!-- Balsamiq Browser Window Frame -->
        <div class="w-full h-full max-w-6xl mx-auto browser-window flex flex-col relative">
            
            <!-- Browser Header -->
            <div class="h-16 bg-gray-200 border-b-2 border-gray-600 flex flex-col shrink-0 px-2 py-1 relative">
                <div class="text-center font-bold text-gray-700 text-md leading-none mb-1">Web ULT FKIP Unila</div>
                <div class="flex items-center gap-2">
                    <div class="flex gap-1 ml-1">
                        <div class="w-5 h-5 rounded-full border-2 border-gray-600 bg-white flex items-center justify-center"><span class="text-xs font-bold leading-none -mt-0.5">&larr;</span></div>
                        <div class="w-5 h-5 rounded-full border-2 border-gray-600 bg-white flex items-center justify-center"><span class="text-xs font-bold leading-none -mt-0.5">&rarr;</span></div>
                        <div class="w-5 h-5 rounded-full border-2 border-gray-600 bg-white flex items-center justify-center font-bold text-xs"><span class="leading-none mt-0.5">C</span></div>
                    </div>
                    <div class="flex-1 h-7 bg-white border-2 border-gray-600 rounded-md px-3 flex items-center text-sm font-bold text-gray-600" x-text="'https://ult.fkip.unila.ac.id/' + currentTab.replace(/[0-9_]/g, '')"></div>
                    <div class="w-6 h-6 border-2 border-gray-600 bg-gray-300 text-gray-800 flex items-center justify-center font-bold text-sm absolute top-1 right-2">X</div>
                </div>
            </div>

            <!-- Content Area (Scrollable within the browser frame) -->
            <div class="flex-1 overflow-y-auto bg-white relative p-6">

                <!-- 1. PUBLIC: BERANDA -->
                <div x-show="currentTab === '1_beranda'" class="min-h-full flex flex-col max-w-5xl mx-auto">
                    <!-- Topnav -->
                    <div class="flex justify-between items-center mb-8 pb-4 border-b-2 border-gray-600">
                        <div class="placeholder-x w-32 h-10"></div>
                        <div class="flex gap-4">
                            <div class="wf-border rounded px-4 py-1 font-bold text-gray-700">Beranda</div>
                            <div class="px-4 py-1 font-bold text-gray-500">Layanan</div>
                            <div class="px-4 py-1 font-bold text-gray-500">Berita</div>
                            <div class="wf-border rounded px-4 py-1 font-bold bg-gray-200">Masuk</div>
                        </div>
                    </div>
                    
                    <!-- Hero Banner -->
                    <div class="placeholder-x w-full h-64 mb-8 flex items-center justify-center">
                        <div class="bg-white wf-border p-4 w-1/2 text-center shadow-lg">
                            <h2 class="font-bold text-2xl mb-2">Layanan Administrasi Terpadu</h2>
                            <p class="mb-4">Ajukan dokumen surat Anda disini secara mandiri.</p>
                            <div class="wf-border rounded-full inline-block px-6 py-2 font-bold bg-gray-200">Mulai Pengajuan</div>
                        </div>
                    </div>
                    
                    <!-- Popular Services -->
                    <h3 class="font-bold text-xl mb-4 text-center">Layanan Populer</h3>
                    <div class="grid grid-cols-3 gap-6 mb-8">
                        <div class="wf-border p-4 rounded-lg flex flex-col items-center text-center">
                            <div class="placeholder-x w-16 h-16 rounded-full mb-3"></div>
                            <h4 class="font-bold mb-2">Surat Aktif Kuliah</h4>
                            <div class="h-2 w-full bg-gray-300 mb-1"></div>
                            <div class="h-2 w-3/4 bg-gray-300"></div>
                        </div>
                        <div class="wf-border p-4 rounded-lg flex flex-col items-center text-center">
                            <div class="placeholder-x w-16 h-16 rounded-full mb-3"></div>
                            <h4 class="font-bold mb-2">Izin Penelitian</h4>
                            <div class="h-2 w-full bg-gray-300 mb-1"></div>
                            <div class="h-2 w-2/3 bg-gray-300"></div>
                        </div>
                        <div class="wf-border p-4 rounded-lg flex flex-col items-center text-center">
                            <div class="placeholder-x w-16 h-16 rounded-full mb-3"></div>
                            <h4 class="font-bold mb-2">SK Pembimbing</h4>
                            <div class="h-2 w-full bg-gray-300 mb-1"></div>
                            <div class="h-2 w-4/5 bg-gray-300"></div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="border-t-2 border-gray-600 pt-4 text-center font-bold text-gray-500">
                        [ FKIP Universitas Lampung ]
                    </div>
                </div>

                <!-- 2. PUBLIC: KATALOG -->
                <div x-show="currentTab === '2_katalog'" class="min-h-full flex flex-col max-w-5xl mx-auto">
                    <div class="flex justify-between items-center mb-8">
                        <h2 class="text-2xl font-bold">Katalog Layanan Dokumen</h2>
                        <div class="wf-border rounded-full px-4 py-1 flex items-center w-64">
                            <span class="mr-2">&#128269;</span>
                            <span class="text-gray-400">Search...</span>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="flex gap-2 mb-6 border-b-2 border-gray-600 pb-2">
                        <div class="wf-border rounded px-4 py-1 font-bold bg-gray-200">Semua</div>
                        <div class="wf-border rounded px-4 py-1 font-bold">Akademik</div>
                        <div class="wf-border rounded px-4 py-1 font-bold">Kemahasiswaan</div>
                    </div>
                    
                    <!-- List -->
                    <div class="space-y-4">
                        <div class="wf-border p-4 rounded-lg flex gap-4 items-center">
                            <div class="placeholder-x w-16 h-16 shrink-0 rounded"></div>
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">Surat Keterangan Lulus (SKL)</h3>
                                <div class="h-2 w-3/4 bg-gray-300 my-2"></div>
                                <div class="wf-border inline-block px-2 text-xs font-bold rounded bg-gray-200">Persyaratan: 3 Dokumen</div>
                            </div>
                            <div class="wf-border px-4 py-2 rounded font-bold">Buka Form &rarr;</div>
                        </div>
                        <div class="wf-border p-4 rounded-lg flex gap-4 items-center">
                            <div class="placeholder-x w-16 h-16 shrink-0 rounded"></div>
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">Cuti Akademik</h3>
                                <div class="h-2 w-2/3 bg-gray-300 my-2"></div>
                                <div class="wf-border inline-block px-2 text-xs font-bold rounded bg-gray-200">Persyaratan: 2 Dokumen</div>
                            </div>
                            <div class="wf-border px-4 py-2 rounded font-bold">Buka Form &rarr;</div>
                        </div>
                        <div class="wf-border p-4 rounded-lg flex gap-4 items-center">
                            <div class="placeholder-x w-16 h-16 shrink-0 rounded"></div>
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">Pengantar Beasiswa</h3>
                                <div class="h-2 w-4/5 bg-gray-300 my-2"></div>
                                <div class="wf-border inline-block px-2 text-xs font-bold rounded bg-gray-200">Persyaratan: 1 Dokumen</div>
                            </div>
                            <div class="wf-border px-4 py-2 rounded font-bold">Buka Form &rarr;</div>
                        </div>
                    </div>
                </div>

                <!-- 4. AUTH: LOGIN -->
                <div x-show="currentTab === '4_login'" class="min-h-full flex flex-col items-center justify-center">
                    <div class="placeholder-x w-full h-full absolute inset-0 opacity-20 z-0"></div>
                    <div class="wf-border bg-white p-8 w-[400px] z-10 shadow-lg text-center rounded-lg relative">
                        <div class="placeholder-x w-20 h-20 rounded-full mx-auto mb-4"></div>
                        <h2 class="text-2xl font-bold mb-6">SSO Login Portal</h2>
                        
                        <div class="text-left mb-4">
                            <label class="font-bold block mb-1">Email / NPM</label>
                            <div class="wf-border w-full h-10 px-3 flex items-center bg-gray-50 rounded text-gray-400">user@unila.ac.id</div>
                        </div>
                        <div class="text-left mb-6">
                            <label class="font-bold block mb-1">Password</label>
                            <div class="wf-border w-full h-10 px-3 flex items-center bg-gray-50 rounded text-gray-400">********</div>
                        </div>
                        
                        <div class="flex justify-between items-center mb-6 text-sm font-bold">
                            <div><input type="checkbox" class="mr-1"> Remember me</div>
                            <div class="underline">Lupa Password?</div>
                        </div>
                        
                        <div class="wf-border w-full py-2 bg-gray-200 font-bold rounded mb-4">Sign In</div>
                    </div>
                </div>

                <!-- 5. STUDENT: DASHBOARD -->
                <div x-show="currentTab === '5_student_dash'" class="min-h-full flex flex-col">
                    <div class="flex justify-between items-center border-b-2 border-gray-600 pb-4 mb-6">
                        <h2 class="text-2xl font-bold">Portal Mahasiswa</h2>
                        <div class="flex items-center gap-4">
                            <div class="font-bold underline">Notifikasi (2)</div>
                            <div class="placeholder-x w-10 h-10 rounded-full"></div>
                        </div>
                    </div>
                    
                    <div class="flex gap-6">
                        <div class="w-64 shrink-0">
                            <div class="wf-border p-4 text-center rounded-lg bg-gray-100 mb-6">
                                <div class="placeholder-x w-24 h-24 rounded-full mx-auto mb-2"></div>
                                <h3 class="font-bold text-lg">Andricha Dea</h3>
                                <p class="text-sm font-bold text-gray-500">NPM: 2013023021</p>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="wf-border px-4 py-2 font-bold bg-gray-200 rounded">Home</div>
                                <div class="wf-border px-4 py-2 font-bold rounded">Buat Pengajuan</div>
                                <div class="wf-border px-4 py-2 font-bold rounded">Riwayat Dokumen</div>
                            </div>
                        </div>
                        
                        <div class="flex-1">
                            <div class="placeholder-x w-full h-32 mb-6 flex items-center justify-center rounded">
                                <div class="bg-white wf-border p-2 font-bold">Welcome Banner & Stats</div>
                            </div>
                            
                            <h3 class="text-xl font-bold mb-4">Aktivitas Terakhir</h3>
                            <div class="space-y-4">
                                <div class="wf-border p-4 rounded flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold">Surat Magang</h4>
                                        <div class="h-2 w-32 bg-gray-300 mt-1"></div>
                                    </div>
                                    <div class="wf-border px-3 py-1 text-sm font-bold bg-gray-200 rounded">Sedang Diproses</div>
                                </div>
                                <div class="wf-border p-4 rounded flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold">Surat Aktif Kuliah</h4>
                                        <div class="h-2 w-48 bg-gray-300 mt-1"></div>
                                    </div>
                                    <div class="wf-border px-3 py-1 text-sm font-bold rounded flex items-center gap-2">
                                        Selesai <div class="w-4 h-4 wf-border bg-gray-200 inline-block text-center text-[10px] leading-tight">v</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 8. ADMIN: DASHBOARD -->
                <div x-show="currentTab === '8_admin_dash'" class="min-h-full flex gap-6">
                    <div class="w-56 shrink-0 border-r-2 border-gray-600 pr-6">
                        <h2 class="text-xl font-bold mb-6">ULT ADMIN</h2>
                        <div class="space-y-2 mb-8">
                            <div class="wf-border px-3 py-2 font-bold bg-gray-200 rounded">Dashboard</div>
                            <div class="wf-border px-3 py-2 font-bold rounded flex justify-between">
                                Antrian <div class="wf-border px-2 rounded bg-gray-200 text-xs flex items-center">5</div>
                            </div>
                            <div class="wf-border px-3 py-2 font-bold rounded">Template Doc</div>
                        </div>
                        <div class="placeholder-x w-full h-32 rounded flex items-center justify-center font-bold">Admin Profile</div>
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">Dashboard Operasional</h2>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4 mb-8">
                            <div class="wf-border p-4 rounded bg-gray-100">
                                <div class="font-bold text-sm text-gray-500 mb-1">MASUK (HARI INI)</div>
                                <div class="text-3xl font-bold">24</div>
                            </div>
                            <div class="wf-border p-4 rounded bg-gray-100">
                                <div class="font-bold text-sm text-gray-500 mb-1">MENUNGGU REVIEW</div>
                                <div class="text-3xl font-bold">5</div>
                            </div>
                            <div class="wf-border p-4 rounded bg-gray-100">
                                <div class="font-bold text-sm text-gray-500 mb-1">SELESAI (BULAN INI)</div>
                                <div class="text-3xl font-bold">189</div>
                            </div>
                        </div>
                        
                        <div class="wf-border rounded overflow-hidden">
                            <div class="bg-gray-200 p-3 font-bold border-b-2 border-gray-600">Daftar Antrian Review</div>
                            <div class="p-4 space-y-3">
                                <!-- Row 1 -->
                                <div class="flex gap-4 items-center border-b-2 border-dashed border-gray-400 pb-3">
                                    <div class="w-20 font-bold text-sm">REQ-01</div>
                                    <div class="flex-1 font-bold">Budi (Magang)</div>
                                    <div class="w-32 text-sm text-gray-500 font-bold">09:15 AM</div>
                                    <div class="wf-border px-4 py-1 rounded bg-gray-100 font-bold text-sm">Review</div>
                                </div>
                                <!-- Row 2 -->
                                <div class="flex gap-4 items-center border-b-2 border-dashed border-gray-400 pb-3">
                                    <div class="w-20 font-bold text-sm">REQ-02</div>
                                    <div class="flex-1 font-bold">Siti (Penelitian)</div>
                                    <div class="w-32 text-sm text-gray-500 font-bold">08:30 AM</div>
                                    <div class="wf-border px-4 py-1 rounded bg-gray-100 font-bold text-sm">Review</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Placeholder for others -->
                <div x-show="!['1_beranda', '2_katalog', '4_login', '5_student_dash', '8_admin_dash'].includes(currentTab)" class="min-h-full flex items-center justify-center flex-col text-gray-500 p-10 text-center">
                    <div class="placeholder-x w-32 h-32 mb-6"></div>
                    <h2 class="text-2xl font-bold mb-2 text-gray-700">Wireframe Kasar</h2>
                    <p class="max-w-md font-bold">Sisa dari 11 halaman lainnya mengadopsi estetika kotak "X" silang yang sama, tanpa pewarnaan, dengan font tulisan tangan.</p>
                </div>

            </div>
        </div>
    </main>

</body>
</html>
"""

with open(r'c:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\preview_11_wireframes_balsamiq.html', 'w', encoding='utf-8') as f:
    f.write(html_content)

print("Sketchy Balsamiq wireframe artifact created.")
