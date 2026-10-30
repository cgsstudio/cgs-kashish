/* ============================================================
   CHAMELEO GFX STUDIO   JavaScript Architecture
   Modular IIFEs. No dependencies. Reduced-motion aware.
   ============================================================ */



(function () {
  "use strict";
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  document.addEventListener("DOMContentLoaded", function () {
    document.body.classList.add("loaded");
  });

  /* ---- Scroll Progress & Mouse Tracker ---- */
  var progressBar = document.getElementById("scrollProgress");
  var glowTracker = document.getElementById("glowTracker");

  if (progressBar) {
    var progressTicking = false;
    function updateProgress() {
      var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
      var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      var scrolled = height > 0 ? (winScroll / height) * 100 : 0;
      progressBar.style.width = scrolled + "%";
      progressTicking = false;
    }
    window.addEventListener("scroll", function () {
      if (progressTicking) return;
      progressTicking = true;
      requestAnimationFrame(updateProgress);
    }, { passive: true });
  }

  if (glowTracker && !reduce) {
    var mouseX = -300, mouseY = -300;
    var currentX = -300, currentY = -300;
    var glowFrame = 0;

    document.addEventListener("mousemove", function (e) {
      mouseX = e.clientX;
      mouseY = e.clientY;
      if (!glowFrame) glowFrame = requestAnimationFrame(updateGlow);
    }, { passive: true });

    function updateGlow() {
      currentX += (mouseX - currentX) * 0.12;
      currentY += (mouseY - currentY) * 0.12;
      glowTracker.style.transform = "translate3d(" + (currentX - 150) + "px, " + (currentY - 150) + "px, 0)";
      glowFrame = 0;
      if (Math.abs(mouseX - currentX) > 0.5 || Math.abs(mouseY - currentY) > 0.5) {
        glowFrame = requestAnimationFrame(updateGlow);
      }
    }
  }

  /* ---- 1. Sticky nav state ---- */
  var nav = document.getElementById("nav");
  var toTop = document.getElementById("toTop");
  var scrollTicking = false;

  function updateNavHeight() {
    if (!nav) return;
    var h = nav.offsetHeight;
    if (h) document.documentElement.style.setProperty("--nav-height", h + "px");
  }

  function onScroll() {
    var y = window.scrollY || window.pageYOffset;
    if (nav) {
      nav.classList.toggle("scrolled", y > 24);
    }
    if (toTop) toTop.classList.toggle("show", y > 500);
    scrollTicking = false;
  }
  function requestScrollUpdate() {
    if (scrollTicking) return;
    scrollTicking = true;
    requestAnimationFrame(onScroll);
  }
  requestAnimationFrame(onScroll);
  window.addEventListener("scroll", requestScrollUpdate, { passive: true });
  window.addEventListener("resize", function () {
    requestAnimationFrame(updateNavHeight);
  }, { passive: true });
  window.addEventListener("load", function () {
    requestAnimationFrame(updateNavHeight);
  });
  if (toTop) toTop.addEventListener("click", function () {
    window.scrollTo({ top: 0, behavior: reduce ? "auto" : "smooth" });
  });

  /* ---- Quote form shortcut ---- */
  var stickyQuote = document.getElementById("stickyQuoteButton");
  var heroFormPanel = document.getElementById("heroFormPanel");
  var contactFormPanel = document.getElementById("contactFormPanel");

  if (stickyQuote) {
    stickyQuote.addEventListener("click", function () {
      var targetPanel = heroFormPanel;
      if (window.scrollY > 800 && contactFormPanel) {
        targetPanel = contactFormPanel;
      }
      if (!targetPanel && heroFormPanel) targetPanel = heroFormPanel;

      if (targetPanel) {
        targetPanel.classList.add("quote-open");
        stickyQuote.setAttribute("aria-expanded", "true");
        targetPanel.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "center" });
        var firstInput = targetPanel.querySelector("input");
        if (firstInput) {
          setTimeout(function () { firstInput.focus(); }, reduce ? 0 : 350);
        }
        setTimeout(function () {
          targetPanel.classList.remove("quote-open");
        }, 1200);
      }
    });
  }

  /* ---- 2. Mega menu (hover + click + keyboard) ---- */
  var mega = document.getElementById("megaParent");
  if (mega) {
    var megaBtn = mega.querySelector("button");
    function openMega(s) { mega?.classList.toggle("open", s); megaBtn?.setAttribute("aria-expanded", s); }
    mega.addEventListener("mouseenter", function () { openMega(true); });
    mega.addEventListener("mouseleave", function () { openMega(false); });
    megaBtn.addEventListener("click", function (e) { e.preventDefault(); openMega(!mega.classList.contains("open")); });
    document.addEventListener("keydown", function (e) { if (e.key === "Escape") openMega(false); });
  }

  /* ---- 3. Mobile drawer & Cookie banner are unified and handled by navbar-footer.js ---- */

  /* ---- 4. Hero word cycler (embodies "we adapt") ---- */
  var cycle = document.getElementById("cycle");
  if (cycle && !reduce) {
    var words = ["Customers", "Leads", "Revenue", "Sign-ups", "Growth"], i = 0;
    cycle.style.transition = "opacity .25s ease, transform .25s ease";
    setInterval(function () {
      cycle.style.opacity = "0"; cycle.style.transform = "translateY(-6px)";
      setTimeout(function () {
        i = (i + 1) % words.length; cycle.textContent = words[i];
        cycle.style.opacity = "1"; cycle.style.transform = "translateY(0)";
      }, 250);
    }, 1500);
  }

  /* ---- 5. Scroll reveal (IntersectionObserver) ---- */
  var reveals = document.querySelectorAll(".reveal");
  var staggerGrids = document.querySelectorAll(".badges-grid, .svc-grid, .ind-grid, .why-list");
  var stepsWrap = document.querySelector(".steps-container") || document.querySelector(".steps");

  if ("IntersectionObserver" in window && !reduce) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add("in"); io.unobserve(en.target); }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });

    reveals.forEach(function (el) {
      if (!el.closest(".badges-grid, .svc-grid, .ind-grid, .why-list, .steps-container")) io.observe(el);
    });

    var gridIo = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          en.target.classList.add("in");
          en.target.querySelectorAll(".reveal").forEach(function (el) { el.classList.add("in"); });
          gridIo.unobserve(en.target);
        }
      });
    }, { threshold: 0.01, rootMargin: "0px 0px -6% 0px" });
    staggerGrids.forEach(function (el) { gridIo.observe(el); });

    if (stepsWrap) {
      var stepsIo = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) {
            stepsWrap.classList.add("in");
            var line = stepsWrap.querySelector(".steps-line");
            if (line) line.classList.add("in");
            stepsWrap.querySelectorAll(".step").forEach(function (step, i) {
              setTimeout(function () { step.classList.add("in"); }, i * 450);
            });
            stepsIo.unobserve(en.target);
          }
        });
      }, { threshold: 0.2 });
      stepsIo.observe(stepsWrap);
    }
  } else {
    reveals.forEach(function (el) { el.classList.add("in"); });
    staggerGrids.forEach(function (el) { el.classList.add("in"); });
    if (stepsWrap) {
      stepsWrap.classList.add("in");
      var line = stepsWrap.querySelector(".steps-line");
      if (line) line.classList.add("in");
      stepsWrap.querySelectorAll(".step").forEach(function (step) { step.classList.add("in"); });
    }
  }

  /* ---- Hero parallax on scroll ---- */
  if (!reduce) {
    var hero = document.getElementById("hero");
    var heroInner = hero ? hero.querySelector(".hero-inner") : null;
    var heroTicking = false;
    function updateHeroParallax() {
      if (!hero || !heroInner) return;
      var rect = hero.getBoundingClientRect();
      if (rect.bottom < 0 || rect.top > window.innerHeight) {
        heroTicking = false;
        return;
      }
      var progress = Math.min(1, Math.max(0, -rect.top / (rect.height * 0.8)));
      heroInner.style.transform = "translateY(" + (progress * 36) + "px)";
      heroInner.style.opacity = String(1 - progress * 0.25);
      heroTicking = false;
    }
    window.addEventListener("scroll", function () {
      if (heroTicking) return;
      heroTicking = true;
      requestAnimationFrame(updateHeroParallax);
    }, { passive: true });
  }

  /* ---- 6. Testimonials slider ---- */
  (function () {
    var track = document.getElementById("slidesTrack");
    if (!track) return;
    var slider = document.getElementById("slider");
    var slides = track.children, total = slides.length, idx = 0;
    var dotsWrap = document.getElementById("dots"), timer;
    for (var d = 0; d < total; d++) {
      var b = document.createElement("button");
      b.className = "dot-btn" + (d === 0 ? " active" : ""); b.setAttribute("role", "tab");
      b.setAttribute("aria-label", "Testimonial " + (d + 1));
      (function (n) { b.addEventListener("click", function () { go(n); restart(); }); })(d);
      dotsWrap.appendChild(b);
    }
    var dots = dotsWrap.children;
    function go(n) {
      idx = (n + total) % total;
      slider.classList.add("transitioning");
      track.style.transform = "translateX(-" + (idx * 100) + "%)";
      for (var k = 0; k < dots.length; k++) dots[k].classList.toggle("active", k === idx);
      setTimeout(function () { slider.classList.remove("transitioning"); }, 650);
    }
    var nextSlide = document.getElementById("nextSlide");
    var prevSlide = document.getElementById("prevSlide");
    if (nextSlide) nextSlide.addEventListener("click", function () { go(idx + 1); restart(); });
    if (prevSlide) prevSlide.addEventListener("click", function () { go(idx - 1); restart(); });
    function restart() { if (reduce) return; clearInterval(timer); timer = setInterval(function () { go(idx + 1); }, 5500); }
    restart();
    // pause on hover
    slider.addEventListener("mouseenter", function () { clearInterval(timer); });
    slider.addEventListener("mouseleave", restart);
  })();

  /* ---- 7. FAQ accordion ---- */
  document.querySelectorAll(".faq-item").forEach(function (item) {
    var btn = item.querySelector(".faq-q"), panel = item.querySelector(".faq-a");
    btn.addEventListener("click", function () {
      var open = item.classList.contains("open");
      // close siblings for a clean single-open accordion
      document.querySelectorAll(".faq-item.open").forEach(function (o) {
        if (o !== item) {
          o.classList.remove("open");
          var q = o.querySelector(".faq-q"); if (q) q.setAttribute("aria-expanded", "false");
          var a = o.querySelector(".faq-a"); if (a) a.style.maxHeight = null;
        }
      });
      item.classList.toggle("open", !open);
      btn.setAttribute("aria-expanded", String(!open));
      panel.style.maxHeight = open ? null : panel.scrollHeight + "px";
    });
  });

  /* ---- 8. Contact form (client-side validation + success state) removed to allow native submission to contact.php ---- */

  /* ---- 9. Active-section nav highlight ---- */
  var navLinks = document.querySelectorAll('.nav-links a[href^="#"]');
  var sections = [].slice.call(document.querySelectorAll("main section[id]"));
  if ("IntersectionObserver" in window) {
    var spy = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          navLinks.forEach(function (l) {
            var active = l.getAttribute("href") === "#" + en.target.id;
            l.classList.toggle("active", active);
            l.style.color = active ? "#fff" : "";
          });
        }
      });
    }, { rootMargin: "-45% 0px -50% 0px" });
    sections.forEach(function (s) { spy.observe(s); });
  }

  /* ---- Subtle tilt on case study cards (desktop only) ---- */
  if (!reduce && window.matchMedia("(min-width: 769px)").matches) {
    document.querySelectorAll(".case").forEach(function (card) {
      var tiltTicking = false, tiltEvent = null;
      function updateTilt() {
        if (!tiltEvent) return;
        var rect = card.getBoundingClientRect();
        var x = (tiltEvent.clientX - rect.left) / rect.width - 0.5;
        var y = (tiltEvent.clientY - rect.top) / rect.height - 0.5;
        card.style.transform = "translateY(-5px) perspective(800px) rotateX(" + (-y * 3) + "deg) rotateY(" + (x * 3) + "deg)";
        tiltTicking = false;
      }
      card.addEventListener("mousemove", function (e) {
        tiltEvent = e;
        if (tiltTicking) return;
        tiltTicking = true;
        requestAnimationFrame(updateTilt);
      }, { passive: true });
      card.addEventListener("mouseleave", function () {
        tiltEvent = null;
        tiltTicking = false;
        card.style.transform = "";
      });
    });
  }

  /* ---- 10. Bio modal (founder popups) ---- */
  (function () {
    var modal = document.getElementById("bioModal");
    if (!modal) return;
    var nameEl = document.getElementById("bioName"),
      roleEl = document.getElementById("bioRole"),
      imgEl = document.getElementById("bioImg"),
      bodyEl = document.getElementById("bioBody"),
      liEl = document.getElementById("bioLinkedIn"),
      lastFocus = null;

    function open(btn) {
      var srcTpl = document.getElementById(btn.getAttribute("data-src"));
      nameEl.textContent = btn.getAttribute("data-name");
      roleEl.innerHTML = btn.getAttribute("data-role");
      if (imgEl) {
        imgEl.src = btn.getAttribute("data-img") || "";
        imgEl.alt = btn.getAttribute("data-name") || "";
      }
      liEl.setAttribute("href", btn.getAttribute("data-li") || "#");
      bodyEl.innerHTML = "";
      if (srcTpl) bodyEl.appendChild(srcTpl.content.cloneNode(true));
      lastFocus = btn;
      modal.hidden = false;
      document.body.classList.add("modal-open");
      var mc = modal.querySelector(".modal-close"); if (mc) mc.focus();
    }
    function close() {
      modal.hidden = true;
      document.body.classList.remove("modal-open");
      if (lastFocus) lastFocus.focus();
    }
    document.querySelectorAll(".bio-btn").forEach(function (b) {
      b.addEventListener("click", function () { open(b); });
    });
    modal.querySelectorAll("[data-close]").forEach(function (el) {
      el.addEventListener("click", close);
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && !modal.hidden) close();
    });
    // simple focus trap
    modal.addEventListener("keydown", function (e) {
      if (e.key !== "Tab") return;
      var f = modal.querySelectorAll('button, a[href]');
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });
  })();
})();






/* ---- Cookie Consent Logic ---- */
(function () {
  var cookieConsent = document.getElementById("cookieConsent");
  if (cookieConsent) {
    if (!localStorage.getItem("cgs_cookie_consent")) {
      setTimeout(function () {
        cookieConsent.classList.add("show");
      }, 1500); // Show after 1.5 seconds on first load
    }

    var acceptBtn = document.getElementById("cookieAccept");
    if (acceptBtn) {
      acceptBtn.addEventListener("click", function () {
        localStorage.setItem("cgs_cookie_consent", "accepted");
        cookieConsent.classList.remove("show");
      });
    }

    var rejectBtn = document.getElementById("cookieReject");
    if (rejectBtn) {
      rejectBtn.addEventListener("click", function () {
        localStorage.setItem("cgs_cookie_consent", "rejected");
        cookieConsent.classList.remove("show");
      });
    }

    var cancelBtn = document.getElementById("cookieCancel");
    if (cancelBtn) {
      cancelBtn.addEventListener("click", function () {
        cookieConsent.classList.remove("show");
      });
    }
  }
})();

/* ---- Blog Slider Infinite Loop Auto-Play ---- */
(function () {
  var blogSlider = document.querySelector(".blog-slider");
  var dots = document.querySelectorAll(".blog-slider-dots .dot");
  if (!blogSlider) return;

  var originalCards = Array.from(blogSlider.children);
  var numOriginals = originalCards.length;

  // Clone cards to create enough content for an infinite loop effect
  originalCards.forEach(function (c) {
    blogSlider.appendChild(c.cloneNode(true));
  });

  var scrollTimer;
  var currentIndex = 0;

  function updateDots(index) {
    if (!dots || dots.length === 0) return;
    var activeIndex = index % numOriginals;
    dots.forEach(function (dot, i) {
      dot.classList.toggle("active", i === activeIndex);
    });
  }

  function startScroll() {
    scrollTimer = setInterval(function () {
      var card = blogSlider.firstElementChild;
      if (!card) return;
      var cardWidth = card.offsetWidth;
      var gap = 30;
      var scrollAmount = cardWidth + gap;

      currentIndex++;
      var nextPos = blogSlider.scrollLeft + scrollAmount;

      if (currentIndex >= numOriginals) {
        blogSlider.style.scrollBehavior = "auto";
        blogSlider.scrollLeft = 0;
        currentIndex = 0;

        setTimeout(function () {
          currentIndex++;
          blogSlider.style.scrollBehavior = "smooth";
          blogSlider.scrollLeft = scrollAmount;
          updateDots(currentIndex);
        }, 50);
      } else {
        blogSlider.style.scrollBehavior = "smooth";
        blogSlider.scrollLeft = nextPos;
        updateDots(currentIndex);
      }
    }, 3500);
  }

  // Initialize
  updateDots(currentIndex);
  startScroll();

  blogSlider.addEventListener("mouseenter", function () { clearInterval(scrollTimer); });
  blogSlider.addEventListener("mouseleave", function () { startScroll(); });
  blogSlider.addEventListener("touchstart", function () { clearInterval(scrollTimer); }, { passive: true });
  blogSlider.addEventListener("touchend", function () { startScroll(); }, { passive: true });

  // Handle dot clicks
  if (dots) {
    dots.forEach(function (dot, i) {
      dot.addEventListener("click", function () {
        clearInterval(scrollTimer);
        var card = blogSlider.firstElementChild;
        if (!card) return;
        var cardWidth = card.offsetWidth;
        var scrollAmount = cardWidth + 30;

        currentIndex = i;
        blogSlider.style.scrollBehavior = "smooth";
        blogSlider.scrollLeft = currentIndex * scrollAmount;
        updateDots(currentIndex);

        startScroll(); // restart timer
      });
    });
  }

  // Handle manual scroll updating dots
  blogSlider.addEventListener("scroll", function () {
    if (blogSlider.style.scrollBehavior === "auto") return; // ignore reset jumps
    var card = blogSlider.firstElementChild;
    if (!card) return;
    var scrollAmount = card.offsetWidth + 30;
    var index = Math.round(blogSlider.scrollLeft / scrollAmount);
    updateDots(index);
  }, { passive: true });
})();
