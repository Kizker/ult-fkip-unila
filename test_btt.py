from playwright.sync_api import sync_playwright
import time

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        
        page.goto("http://127.0.0.1:8001")
        page.evaluate("window.scrollTo(0, 800)")
        time.sleep(1)
        
        scroll_y = page.evaluate("window.scrollY")
        print(f"window.scrollY is: {scroll_y}")
        
        btn = page.locator('#backToTopBtn')
        print(f"Button classes: {btn.get_attribute('class')}")
        browser.close()

if __name__ == '__main__':
    run()
