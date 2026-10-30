document.addEventListener('DOMContentLoaded', () => {
  const faqList = document.getElementById('service-faq-list');
  if (faqList) {
    const setAnswerHeight = (card) => {
      const answer = card.querySelector('.about-faq-answer');
      if (!answer) return;
      answer.style.maxHeight = card.classList.contains('is-open')
        ? `${answer.scrollHeight}px`
        : '0px';
    };

    faqList.querySelectorAll('.about-faq-trigger').forEach((trigger) => {
      trigger.addEventListener('click', () => {
        const card = trigger.closest('.about-faq-card');
        const isOpen = card.classList.contains('is-open');

        faqList.querySelectorAll('.about-faq-card').forEach((item) => {
          item.classList.remove('is-open');
          const btn = item.querySelector('.about-faq-trigger');
          if (btn) btn.setAttribute('aria-expanded', 'false');
          setAnswerHeight(item);
        });

        if (!isOpen) {
          card.classList.add('is-open');
          trigger.setAttribute('aria-expanded', 'true');
          setAnswerHeight(card);
        }
      });
    });

    const firstCard = faqList.querySelector('.about-faq-card');
    if (firstCard) {
      firstCard.classList.add('is-open');
      const firstBtn = firstCard.querySelector('.about-faq-trigger');
      if (firstBtn) firstBtn.setAttribute('aria-expanded', 'true');
      requestAnimationFrame(() => setAnswerHeight(firstCard));
    }

    window.addEventListener('resize', () => {
      faqList.querySelectorAll('.about-faq-card.is-open').forEach(setAnswerHeight);
    });
  }

  const revealItems = document.querySelectorAll('.service-page-body .reveal-up');
  if (revealItems.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            obs.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );
    revealItems.forEach((el) => observer.observe(el));
  }

  const marquee = document.querySelector('.service-page-body .about-marquee-section .aximo-auto-slider');
  if (marquee && typeof Swiper !== 'undefined') {
    new Swiper(marquee, {
      slidesPerView: 'auto',
      spaceBetween: 24,
      loop: true,
      speed: 4500,
      autoplay: { delay: 0, disableOnInteraction: false },
      freeMode: true,
      allowTouchMove: true,
    });
  }
});
