from playwright.sync_api import sync_playwright

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        page.goto("http://127.0.0.1:8001")
        
        # Wait for the hero section
        page.wait_for_selector(".ult-hero-actions")
        
        button = page.locator(".ult-hero-cta").first
        
        if button.count() == 0:
            print("Button not found in DOM!")
            return
            
        print("Button bounding box:", button.bounding_box())
        print("Button text:", button.inner_text())
        
        styles = button.evaluate("el => window.getComputedStyle(el).cssText")
        display = button.evaluate("el => window.getComputedStyle(el).display")
        opacity = button.evaluate("el => window.getComputedStyle(el).opacity")
        visibility = button.evaluate("el => window.getComputedStyle(el).visibility")
        
        print("display:", display)
        print("opacity:", opacity)
        print("visibility:", visibility)
        
        browser.close()

if __name__ == '__main__':
    run()
