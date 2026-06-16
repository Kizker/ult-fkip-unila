from playwright.sync_api import sync_playwright

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        page.goto("http://127.0.0.1:8001")
        
        page.wait_for_selector(".ult-hero-actions")
        
        # Get all styles applied to the element
        js = """() => {
            const el = document.querySelector('.ult-hero-cta');
            const rules = window.getMatchedCSSRules ? window.getMatchedCSSRules(el) : [];
            let opacityRule = 'none found';
            // Polyfill or just check inline style
            return el.style.opacity || 'computed: ' + window.getComputedStyle(el).opacity;
        }"""
        
        print("Opacity source:", page.evaluate(js))
        
        browser.close()

if __name__ == '__main__':
    run()
