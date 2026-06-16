from playwright.sync_api import sync_playwright

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        page.goto("http://127.0.0.1:8001")
        
        page.wait_for_selector(".ult-hero-actions")
        
        js = """() => {
            const el = document.querySelector('.ult-hero-cta');
            const rules = window.getComputedStyle(el);
            
            // Get all stylesheets and find the rule that sets opacity
            let opacitySource = 'Not found';
            for (let sheet of document.styleSheets) {
                try {
                    for (let rule of sheet.cssRules) {
                        if (rule.selectorText && el.matches(rule.selectorText) && rule.style.opacity) {
                            opacitySource = rule.selectorText + ' { opacity: ' + rule.style.opacity + ' }';
                        }
                    }
                } catch(e) {}
            }
            return opacitySource;
        }"""
        
        print("Opacity source rule:", page.evaluate(js))
        
        browser.close()

if __name__ == '__main__':
    run()
