from playwright.sync_api import sync_playwright

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        
        page.goto("http://127.0.0.1:8001")
        
        # Initial check
        btn = page.locator('#backToTopBtn')
        print(f"Initial Button classes: {btn.get_attribute('class')}")
        
        # Scroll the page
        page.mouse.wheel(0, 800)
        page.wait_for_timeout(1000)
        
        print(f"Scrolled window.scrollY: {page.evaluate('window.scrollY')}")
        print(f"Button classes after scroll: {btn.get_attribute('class')}")
        browser.close()

if __name__ == '__main__':
    run()
