from playwright.sync_api import sync_playwright

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        
        page.goto("http://127.0.0.1:8001")
        
        # Dispatch mouse wheel scroll to simulate user scroll
        page.mouse.wheel(0, 800)
        page.wait_for_timeout(1000)
        
        # Check which elements have scrollHeight > clientHeight and scrollTop > 0
        scroll_info = page.evaluate('''() => {
            const getScrollInfo = (el, name) => ({
                name,
                scrollTop: el.scrollTop,
                scrollHeight: el.scrollHeight,
                clientHeight: el.clientHeight
            });
            return [
                getScrollInfo(document.documentElement, 'html'),
                getScrollInfo(document.body, 'body'),
                getScrollInfo(document.querySelector('.page-public-site'), '.page-public-site')
            ];
        }''')
        print(f"Scroll Info: {scroll_info}")
        
        btn = page.locator('#backToTopBtn')
        print(f"Button classes: {btn.get_attribute('class')}")
        browser.close()

if __name__ == '__main__':
    run()
