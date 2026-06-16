from playwright.sync_api import sync_playwright

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        page.goto("http://127.0.0.1:8001")
        
        # turn on dark mode if possible (maybe by clicking the moon icon)
        page.click("button.btn-theme-toggle, .btn-theme-mode, [aria-label='Toggle Dark Mode'], i.fa-moon") # guess selector
        page.wait_for_timeout(1000)
        
        js = """() => {
            const btn1 = document.querySelector('.ult-hero-cta');
            const btn2 = document.querySelector('.ult-hero-cta-secondary');
            return {
                btn1_bg: window.getComputedStyle(btn1).backgroundColor,
                btn2_bg: window.getComputedStyle(btn2).backgroundColor
            };
        }"""
        print(page.evaluate(js))
        browser.close()

if __name__ == '__main__':
    run()
