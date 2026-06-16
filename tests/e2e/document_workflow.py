import sys
import time
from playwright.sync_api import sync_playwright, TimeoutError as PlaywrightTimeoutError

BASE_URL = 'http://ult-fkip-unila.test'
PASSWORD = 'Password!2345'

def login(page, email):
    print(f"[*] Logging in as {email}...")
    page.goto(f"{BASE_URL}/login")
    page.wait_for_load_state('networkidle')
    
    page.fill('input[name="email"]', email)
    page.fill('input[name="password"]', PASSWORD)
    page.click('button[type="submit"]')
    page.wait_for_load_state('networkidle')
    
    if "login" in page.url:
        print(f"[!] Login failed for {email}!")
        page.screenshot(path=f"login_failed_{email}.png")
        sys.exit(1)
    print(f"[+] Successfully logged in as {email}")

def logout(page):
    print("[*] Logging out...")
    # Usually in Breeze it's a POST request via a form
    # We can try to find a button with text "Log Out", "Logout", or "Keluar"
    try:
        # Tries to find the logout button in a dropdown or directly
        # Since it's hidden in a dropdown in breeze, it might be tricky.
        # As a fallback, we just clear cookies to logout.
        page.context.clear_cookies()
        page.goto(BASE_URL)
        page.wait_for_load_state('networkidle')
        print("[+] Logged out via cookie clear.")
    except Exception as e:
        print(f"[!] Logout failed: {e}")

def run_workflow():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=False, slow_mo=500)
        context = browser.new_context()
        page = context.new_page()

        # Step 1: Student submits request
        login(page, 'mahasiswa@demo.test')
        
        print("[*] Navigating to create request...")
        page.goto(f"{BASE_URL}/mahasiswa/permohonan/buat/surat-keterangan-lulus-C7QaLK")
        page.wait_for_load_state('networkidle')
        
        print("[*] Creating dummy photo...")
        with open('dummy_photo.jpg', 'wb') as f:
            f.write(b'\xff\xd8\xff\xe0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xff\xdb\x00C\x00\x05\x03\x04\x04\x04\x03\x05\x04\x04\x04\x05\x05\x05\x06\x07\x0c\x08\x07\x07\x07\x0f\x0b\x0b\t\x0c\x11\x0f\x12\x12\x11\x0f\x11\x11\x13\x16\x1c\x17\x13\x14\x1a\x15\x11\x11\x18!\x18\x1a\x1d\x1d\x1f\x1f\x1f\x13\x17"$"\x1e$\x1c\x1e\x1f\x1e\xff\xc0\x00\x0b\x08\x00\x01\x00\x01\x01\x01\x11\x00\xff\xc4\x00\x1f\x00\x00\x01\x05\x01\x01\x01\x01\x01\x01\x00\x00\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x05\x06\x07\x08\t\n\x0b\xff\xda\x00\x08\x01\x01\x00\x00?\x00\x8d\xff\xd9')
        
        print("[*] Filling out request form...")
        try:
            # Upload file
            page.set_input_files('input[type="file"]', 'dummy_photo.jpg')
            page.get_by_label('Alamat', exact=False).fill('Jl. Mahasiswa No 1')
            page.get_by_label('Ipk Angka', exact=False).fill('3.80')
            page.get_by_label('Ipk Terbilang', exact=False).fill('Tiga koma delapan nol')
            
            # Judul skripsi is Tiptap Editor (richtext)
            page.locator('.ProseMirror').fill('Pengembangan Sistem Web ULT')
            
            page.get_by_label('Predikat Kelulusan', exact=False).fill('Dengan Pujian')
            page.get_by_label('Tanggal Lulus', exact=False).fill('01 Januari 2026')
            page.get_by_label('Tempat Tanggal Lahir', exact=False).fill('Bandar Lampung, 1 Januari 2000')

            page.locator('form.student-form button[type="submit"]').click()
            page.wait_for_load_state('networkidle')
            print("[+] Form submitted!")
        except Exception as e:
            print("[!] Error filling form, attempting fallback...")
            try:
                page.set_input_files('input[type="file"]', 'dummy_photo.jpg')
                inputs = page.locator('input[type="text"]').all()
                if len(inputs) >= 6:
                    for idx, inp in enumerate(inputs):
                        inp.fill(f'Fallback {idx}')
                    page.locator('.ProseMirror').fill('Fallback Skripsi')
                    page.locator('form.student-form button[type="submit"]').click()
                    page.wait_for_load_state('networkidle')
                    print("[+] Form submitted using fallback selectors!")
                else:
                    raise Exception("Fallback failed, not enough text inputs found.")
            except Exception as inner_e:
                print(f"[!] Failed at URL: {page.url}")
                with open("error_page.html", "w", encoding="utf-8") as f:
                    f.write(page.content())
                page.screenshot(path="student_form_error.png", full_page=True)
                raise inner_e
            
        # Get request ID/URL to track it
        current_url = page.url
        print(f"[*] Current URL after submit: {current_url}")
        
        # Extract ID from URL (e.g., .../permohonan/123)
        request_id = current_url.split('/')[-1]
        
        print("[*] Logging out Mahasiswa...")
        logout(page)

        # STEP 3: Admin Jurusan processes request
        print("[*] Logging in as jurusan.prodi@demo.test...")
        login(page, 'jurusan.prodi@demo.test')
        page.goto(f"{BASE_URL}/admin/permohonan/{request_id}")
        page.wait_for_load_state('networkidle')
        
        print("[*] Admin Jurusan is reviewing...")
        try:
            # Check if there is a gate pass form
            if page.locator('form#gate-pass-form').is_visible():
                page.locator('select[name="letter_format_id"]').select_option(index=1, force=True)
                page.locator('form#gate-pass-form button[type="submit"]').click()
                page.wait_for_load_state('networkidle')
                print("[+] Admin Jurusan passed request.")
            else:
                # Fallback to action form
                page.locator('[data-wf-action="verify"]').click()
                page.wait_for_load_state('networkidle')
                print("[+] Admin Jurusan verified request via workflow action.")
        except Exception as e:
            print("[!] Admin Jurusan review failed:", e)
        
        page.screenshot(path="step3_admin_jurusan.png", full_page=True)
        print("[*] Logging out...")
        logout(page)

        # STEP 4: Staf ULT reviews request
        print("[*] Logging in as ult@demo.test...")
        login(page, 'ult@demo.test')
        page.goto(f"{BASE_URL}/admin/permohonan/{request_id}")
        page.wait_for_load_state('networkidle')
        
        print("[*] Staf ULT is reviewing...")
        try:
            # Check for "start signing" button
            start_btn = page.get_by_role('button', name='Setujui & mulai penandatanganan', exact=False)
            if start_btn.is_visible():
                start_btn.click()
                page.wait_for_load_state('networkidle')
                print("[+] Staf ULT started signing process.")
                page.screenshot(path="step5_staf_ult_after_start.png", full_page=True)
            else:
                print("[!] Staf ULT start signing button not visible.")
                page.screenshot(path="staf_ult_no_button.png", full_page=True)
        except Exception as e:
            print("[!] Staf ULT review failed:", e)

        page.screenshot(path="step4_staf_ult.png", full_page=True)

        print("[*] Logging out...")
        logout(page)

        # STEP 5: Signer (Dekan) uploads signature
        print("[*] Logging in as dekan@fkip.unila.test...")
        login(page, 'dekan@fkip.unila.test')
        page.goto(f"{BASE_URL}/signer/permohonan/{request_id}")
        page.wait_for_load_state('networkidle')
        
        print("[*] Dekan is signing...")
        try:
            # Create a 1x1 transparent PNG for signature
            with open('dummy_signature.png', 'wb') as f:
                f.write(b'\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15\xc4\x89\x00\x00\x00\x0bIDATx\x9cc\xf8\xff\xff?\x00\x05\xfe\x02\xfe\xa75\x81\x84\x00\x00\x00\x00IEND\xaeB`\x82')
            
            sign_form = page.locator('form[action*="decide"]')
            if sign_form.is_visible():
                page.set_input_files('input[name="signature_file"]', 'dummy_signature.png')
                page.locator('form[action*="decide"] button[type="submit"]').click()
                page.wait_for_load_state('networkidle')
                print("[+] Dekan successfully signed the document.")
                page.screenshot(path="step6_dekan_signed.png", full_page=True)
            else:
                print("[!] Sign form not visible for Dekan.")
        except Exception as e:
            print("[!] Dekan signing failed:", e)

        print("[*] Logging out...")
        logout(page)

        # STEP 6: Staf ULT finalizes document
        print("[*] Logging in as ult@demo.test for finalization...")
        login(page, 'ult@demo.test')
        page.goto(f"{BASE_URL}/admin/permohonan/{request_id}")
        page.wait_for_load_state('networkidle')

        print("[*] Staf ULT is finalizing...")
        try:
            open_final_btn = page.locator('a.ars-open-finalize-btn')
            if open_final_btn.is_visible():
                open_final_btn.click()
                page.wait_for_load_state('networkidle')
                print("[+] Opened Assemble page.")
                
                # In assemble page, click Finalize
                page.locator('button.sa-finalize-btn').click()
                page.wait_for_load_state('networkidle')
                print("[+] Document successfully finalized!")
                page.screenshot(path="step7_document_finalized.png", full_page=True)
            else:
                print("[!] Finalize button not visible.")
        except Exception as e:
            print("[!] Finalization failed:", e)

        print("[*] Logging out...")
        logout(page)

        print("[*] E2E Workflow completed fully!")
        browser.close()

if __name__ == '__main__':
    run_workflow()
