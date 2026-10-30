document.addEventListener('DOMContentLoaded', () => {
  const faqList = document.getElementById('about-faq-list');
  if (faqList) {
    const setAnswerHeight = (card) => {
      const answer = card.querySelector('.about-faq-answer');
      if (!answer) return;
      if (card.classList.contains('is-open')) {
        answer.style.maxHeight = `${answer.scrollHeight}px`;
      } else {
        answer.style.maxHeight = '0px';
      }
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

  const revealItems = document.querySelectorAll('.about-page .reveal-up');
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
});
