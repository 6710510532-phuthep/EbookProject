// JavaScript Interactivity for Meb Market Inspired UI + Global Dark Mode
document.addEventListener('DOMContentLoaded', function() {

    // 1. Global Dark Mode Toggle Functionality
    const darkModeBtn = document.getElementById('globalDarkModeToggle');
    const darkModeIcon = document.getElementById('darkModeIcon');

    function updateDarkModeIcon(theme) {
        if (!darkModeIcon) return;
        if (theme === 'dark') {
            darkModeIcon.className = 'fa-solid fa-sun text-warning';
        } else {
            darkModeIcon.className = 'fa-solid fa-moon text-warning';
        }
    }

    // Initialize Icon State
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    updateDarkModeIcon(currentTheme);

    if (darkModeBtn) {
        darkModeBtn.addEventListener('click', function() {
            const activeTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = activeTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('site_theme', newTheme);
            updateDarkModeIcon(newTheme);
        });
    }

    // 2. Price Calculation
    const rentDaysSelect = document.getElementById('rentDays');
    const pricePerDayInput = document.getElementById('pricePerDay');
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');

    function calculateTotalPrice() {
        if (rentDaysSelect && pricePerDayInput && totalPriceDisplay) {
            const days = parseInt(rentDaysSelect.value) || 0;
            const pricePerDay = parseFloat(pricePerDayInput.value) || 0;
            totalPriceDisplay.textContent = '฿' + (days * pricePerDay).toFixed(2);
        }
    }

    if (rentDaysSelect) {
        rentDaysSelect.addEventListener('change', calculateTotalPrice);
        calculateTotalPrice();
    }

    // 3. Search & Category Filter
    const navSearchInput = document.getElementById('navSearchInput');
    const categoryBtns = document.querySelectorAll('.meb-cat-btn');
    const novelItems = document.querySelectorAll('.novel-item');
    let currentCategory = 'all';

    function filterNovels() {
        const query = navSearchInput ? navSearchInput.value.toLowerCase().trim() : '';

        novelItems.forEach(item => {
            const title = item.getAttribute('data-title') || '';
            const author = item.getAttribute('data-author') || '';
            const category = item.getAttribute('data-category') || '';

            const matchesSearch = title.includes(query) || author.includes(query);
            const matchesCategory = (currentCategory === 'all') || (category === currentCategory);

            item.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
        });
    }

    if (navSearchInput) navSearchInput.addEventListener('input', filterNovels);

    categoryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            categoryBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.getAttribute('data-category');
            filterNovels();
        });
    });

    // 4. Reader Settings
    const readerBox = document.getElementById('readerBox');
    const novelContent = document.getElementById('novelContent');
    const fontSizeLabel = document.getElementById('fontSizeLabel');

    if (novelContent && readerBox) {
        let currentFontSize = parseInt(localStorage.getItem('reader_font_size')) || 18;
        let readerTheme = localStorage.getItem('reader_theme') || 'theme-light';

        function applyReaderSettings() {
            novelContent.style.fontSize = currentFontSize + 'px';
            if (fontSizeLabel) fontSizeLabel.textContent = currentFontSize + 'px';

            readerBox.classList.remove('theme-light', 'theme-sepia', 'theme-dark');
            readerBox.classList.add(readerTheme);
        }

        applyReaderSettings();

        document.getElementById('btnFontIncrease')?.addEventListener('click', function() {
            if (currentFontSize < 32) {
                currentFontSize += 2;
                localStorage.setItem('reader_font_size', currentFontSize);
                applyReaderSettings();
            }
        });

        document.getElementById('btnFontDecrease')?.addEventListener('click', function() {
            if (currentFontSize > 12) {
                currentFontSize -= 2;
                localStorage.setItem('reader_font_size', currentFontSize);
                applyReaderSettings();
            }
        });

        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                readerTheme = this.getAttribute('data-theme');
                localStorage.setItem('reader_theme', readerTheme);
                applyReaderSettings();
            });
        });
    }

    // 5. Countdown Timer
    const countdownElements = document.querySelectorAll('.countdown-timer');
    function updateCountdowns() {
        countdownElements.forEach(el => {
            const expireStr = el.getAttribute('data-expire');
            if (!expireStr) return;

            const expireTime = new Date(expireStr.replace(/-/g, "/")).getTime();
            const now = new Date().getTime();
            const diff = expireTime - now;

            if (diff <= 0) {
                el.textContent = 'หมดเวลาเช่า';
            } else {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                let text = 'เหลือ ';
                if (days > 0) text += days + ' วัน ';
                text += hours + ' ชม. ' + minutes + ' นาที ' + seconds + ' วง.';
                el.textContent = text;
            }
        });
    }

    if (countdownElements.length > 0) {
        updateCountdowns();
        setInterval(updateCountdowns, 1000);
    }
});