/* Consolidated inline JS extracted from HTML pages */

// Safe EmailJS init (guarded)
(function() {
    try {
        if (window.emailjs && typeof emailjs.init === 'function') {
            emailjs.init("9QWvlBpES51DX7O1X");
        }
    } catch (e) {
        // emailjs not available on this page yet; ignore
    }
})();

// DOM-ready animations used on about page
document.addEventListener('DOMContentLoaded', () => {
    try {
        const observerOptions = { root: null, rootMargin: '0px', threshold: 0.15 };
        const observer = new IntersectionObserver((entries, observerRef) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observerRef.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal-up').forEach((el) => { observer.observe(el); });

        document.querySelectorAll('.glass-card-premium, .aximo-team-wrap, .faq-premium-item, .intro-text-block').forEach((el, index) => {
            el.classList.add('reveal-up');
            el.style.transitionDelay = `${(index % 4) * 0.1}s`;
            observer.observe(el);
        });

        window.setTimeout(() => {
            const aboutSlider = document.querySelector('.about-working-slider');
            const aboutSwiper = document.querySelector('.about-service-slider');
            if (aboutSlider && aboutSwiper && !aboutSwiper.classList.contains('swiper-initialized')) {
                aboutSlider.classList.add('fallback-marquee');
            }
        }, 800);
    } catch (e) {
        // non-fatal: if DOM elements are not present on the page, ignore
    }
});

// Cookie notice and EmailJS form submission logic (contact page)
(function() {
    // Cookie notice
    try {
        if (!localStorage.getItem('cookie_consent_accepted')) {
            setTimeout(() => {
                const el = document.getElementById('cookie-notice');
                if (el) el.style.display = 'block';
            }, 2000);
        }
    } catch (e) {}

    window.closeCookieNotice = function() {
        try {
            const el = document.getElementById('cookie-notice');
            if (el) el.style.display = 'none';
            localStorage.setItem('cookie_consent_accepted', 'true');
        } catch (e) {}
    };

    // EmailJS form handling (if present)
    try {
        const form = document.getElementById('contact-us-form');
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const btn = document.getElementById('aximo-main-btn');
                const originalText = btn ? btn.textContent : '';
                if (btn) { btn.textContent = 'Sending...'; btn.disabled = true; }

                const formData = {
                    name: document.getElementById('name') ? document.getElementById('name').value : '',
                    email: document.getElementById('email') ? document.getElementById('email').value : '',
                    phone: document.getElementById('phone') ? document.getElementById('phone').value : '',
                    message: document.getElementById('message') ? document.getElementById('message').value : ''
                };

                if (window.emailjs && typeof emailjs.send === 'function') {
                    emailjs.send("service_4hzipjm", "template_vnt9ibw", formData)
                        .then(function() {
                            const success = document.getElementById('form-success-alert');
                            if (success) success.style.display = 'block';
                            if (form) form.reset();
                            if (btn) { btn.textContent = originalText; btn.disabled = false; }
                            setTimeout(function() { if (success) success.style.display = 'none'; }, 5000);
                        }, function(error) {
                            console.error('Error sending email:', error);
                            alert('Failed to send message. Please try again.');
                            if (btn) { btn.textContent = originalText; btn.disabled = false; }
                        });
                } else {
                    // emailjs not available; re-enable button
                    if (btn) { btn.textContent = originalText; btn.disabled = false; }
                }
            });
        }
    } catch (e) {}
})();

/* End of consolidated inline JS */
