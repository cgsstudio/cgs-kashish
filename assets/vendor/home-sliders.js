(function () {
  function ready(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback);
    } else {
      callback();
    }
  }

  function cleanupSwiper(swiperEl) {
    swiperEl.classList.remove("swiper-initialized", "swiper-horizontal", "swiper-free-mode", "swiper-backface-hidden");
    var wrapper = swiperEl.querySelector(":scope > .swiper-wrapper");
    if (wrapper) {
      wrapper.style.transform = "";
      wrapper.style.transitionDuration = "";
    }
    swiperEl.querySelectorAll(".swiper-slide").forEach(function (slide) {
      slide.classList.remove(
        "swiper-slide-active",
        "swiper-slide-next",
        "swiper-slide-prev",
        "swiper-slide-visible",
        "swiper-slide-fully-visible",
        "swiper-slide-duplicate"
      );
      slide.removeAttribute("aria-label");
      slide.removeAttribute("role");
    });
  }

  function swiperElements() {
    return Array.from(document.querySelectorAll(".swiper")).filter(function (el) {
      return !!el.querySelector(":scope > .swiper-wrapper") && !el.dataset.staticSwiperReady;
    });
  }

  function initSwiperSliders() {
    if (!window.Swiper) return;

    swiperElements().forEach(function (el) {
      cleanupSwiper(el);
      el.dataset.staticSwiperReady = "true";

      if (el.closest(".aximo-auto-slider-section")) {
        new Swiper(el, {
          centeredSlides: true,
          speed: 5000,
          autoplay: {
            delay: 0,
            disableOnInteraction: false,
          },
          loop: true,
          slidesPerView: "auto",
          allowTouchMove: false,
          freeMode: {
            enabled: true,
            momentum: false,
          },
        });
        return;
      }

      if (el.closest(".logo-slider-container")) {
        new Swiper(el, {
          spaceBetween: el.closest(".brandlogo-section-left") ? 20 : 40,
          loop: true,
          speed: 3500,
          autoplay: {
            delay: 1,
            disableOnInteraction: false,
            reverseDirection: !!el.closest(".brandlogo-section-left"),
          },
          breakpoints: {
            0: {
              slidesPerView: 2,
              spaceBetween: el.closest(".brandlogo-section-left") ? 10 : 20,
            },
            768: {
              slidesPerView: 3,
              spaceBetween: el.closest(".brandlogo-section-left") ? 20 : 30,
            },
            1024: {
              slidesPerView: 7,
              spaceBetween: el.closest(".brandlogo-section-left") ? 20 : 40,
            },
          },
        });
        return;
      }

      new Swiper(el, {
        loop: true,
        speed: 900,
        autoplay: {
          delay: 2500,
          disableOnInteraction: false,
        },
        slidesPerView: 1,
        spaceBetween: 24,
        breakpoints: {
          768: { slidesPerView: 2 },
          1200: { slidesPerView: 3 },
        },
      });
    });
  }

  function initTestimonialSlider() {
    var section = document.querySelector(".testimonial-section-new");
    if (!section) return;

    var wrapper = section.querySelector(".slider-wrapper-new");
    var slides = Array.from(section.querySelectorAll(".slide-new"));
    var buttons = Array.from(section.querySelectorAll(".nav-btn-new"));
    var dotsWrap = section.querySelector(".progress-dots");
    if (!wrapper || !slides.length) return;

    var index = 0;
    var timer;

    function perView() {
      if (window.innerWidth < 768) return 1;
      if (window.innerWidth < 992) return 2;
      if (window.innerWidth < 1400) return 3;
      return 4;
    }

    function maxIndex() {
      return Math.max(0, slides.length - perView());
    }

    function renderDots() {
      if (!dotsWrap) return;
      var total = maxIndex() + 1;
      dotsWrap.innerHTML = "";
      for (var i = 0; i < total; i += 1) {
        var dot = document.createElement("button");
        dot.type = "button";
        dot.className = "progress-dot-new" + (i === index ? " active" : "");
        dot.setAttribute("aria-label", "Go to testimonial " + (i + 1));
        dot.dataset.slide = String(i);
        dotsWrap.appendChild(dot);
      }
    }

    function update() {
      var visible = perView();
      index = Math.min(index, maxIndex());
      slides.forEach(function (slide) {
        slide.style.flex = "0 0 " + 100 / visible + "%";
        slide.style.maxWidth = 100 / visible + "%";
      });
      wrapper.style.transition = "transform 0.5s ease";
      wrapper.style.transform = "translateX(-" + index * (100 / visible) + "%)";
      renderDots();
    }

    function move(step) {
      var next = index + step;
      if (next > maxIndex()) next = 0;
      if (next < 0) next = maxIndex();
      index = next;
      update();
    }

    function restart() {
      window.clearInterval(timer);
      timer = window.setInterval(function () {
        move(1);
      }, 5000);
    }

    buttons.forEach(function (button, buttonIndex) {
      button.addEventListener("click", function () {
        move(buttonIndex === 0 ? -1 : 1);
        restart();
      });
    });

    if (dotsWrap) {
      dotsWrap.addEventListener("click", function (event) {
        var dot = event.target.closest(".progress-dot-new");
        if (!dot) return;
        index = Number(dot.dataset.slide || 0);
        update();
        restart();
      });
    }

    section.querySelectorAll(".read-more-btn").forEach(function (button) {
      button.addEventListener("click", function () {
        var text = button.previousElementSibling;
        if (!text) return;
        text.classList.toggle("line-clamp-5");
        button.textContent = text.classList.contains("line-clamp-5") ? "Read More" : "Show Less";
      });
    });

    window.addEventListener("resize", update);
    update();
    restart();
  }

  ready(function () {
    initSwiperSliders();
    initTestimonialSlider();
  });
})();
