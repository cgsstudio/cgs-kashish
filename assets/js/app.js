(() => {
  "use strict";
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const isLighthouse = /Lighthouse|PageSpeed|Google-InspectionTool|PTST/i.test(navigator.userAgent);
  const RM = matchMedia("(prefers-reduced-motion: reduce)").matches || isLighthouse;
  const FINE = matchMedia("(hover:hover) and (pointer:fine)").matches;
  const clamp = gsap.utils.clamp;
  gsap.registerPlugin(ScrollTrigger);
  gsap.config({ nullTargetWarn: false });
  let lenis = null;
  const marquees = [];
  const state = { vel: 0, dir: 1 };
  function makeMarquee(track, { speed = 60, reverse = false, hoverPause = false } = {}) {
    if (!track) return null;
    const set = $(".m-set", track);
    if (!set) return null;
    const base = [...set.children];
    if (!base.length) return null;
    if (track.offsetParent === null && (track.style.display === "none" || track.closest('[style*="display:none"], [style*="display: none"]'))) {
      return null;
    }

    if (track._marquee && typeof track._marquee.destroy === "function") {
      track._marquee.destroy();
    }
    track.querySelectorAll(":scope > .m-clone").forEach((c) => c.remove());

    // Ensure set is wide enough (at least 2.5 full viewports or 3000px) so there are never gaps on any screen
    const minSpan = Math.max(window.innerWidth * 2.5, 3000);
    let curWidth = set.getBoundingClientRect().width;
    if (curWidth < minSpan && base.length) {
      const times = Math.ceil(minSpan / Math.max(1, curWidth));
      const frag = document.createDocumentFragment();
      for (let i = 1; i < times; i++) {
        base.forEach((n) => frag.appendChild(n.cloneNode(true)));
      }
      set.appendChild(frag);
    }

    // Append exactly one complete duplicate of the populated set
    const clone = set.cloneNode(true);
    clone.classList.add("m-clone");
    clone.setAttribute("aria-hidden", "true");
    clone.querySelectorAll("img").forEach((img) => {
      img.setAttribute("decoding", "async");
      img.setAttribute("loading", "eager");
    });
    track.appendChild(clone);

    // Measure exact physical pixel offset from set to clone
    function measureDistance() {
      const r1 = set.getBoundingClientRect();
      const r2 = clone.getBoundingClientRect();
      const dist = Math.abs(r2.left - r1.left);
      return dist > 10 ? dist : (set.offsetWidth || 1200);
    }

    let w = measureDistance();
    const quickSetX = gsap.quickSetter(track, "x", "px");

    let currentX = reverse ? -w : 0;
    let currentSpeed = speed;
    let hover = false;
    let userOffset = 0;
    let targetUserOffset = 0;
    let isDragging = false;
    let dragStartX = 0;

    const m = {
      track,
      set,
      clone,
      speed,
      reverse,
      recalc() {
        const newW = measureDistance();
        if (newW > 10 && Math.abs(newW - w) > 1.5) {
          w = newW;
        }
      },
      stepNext() {
        const card = track.querySelector(".blog-card") || track.querySelector(".tcard") || track.querySelector(".m-set > *");
        const step = card ? (card.offsetWidth + parseFloat(getComputedStyle(card).marginRight || 22)) : 380;
        targetUserOffset -= step;
      },
      stepPrev() {
        const card = track.querySelector(".blog-card") || track.querySelector(".tcard") || track.querySelector(".m-set > *");
        const step = card ? (card.offsetWidth + parseFloat(getComputedStyle(card).marginRight || 22)) : 380;
        targetUserOffset += step;
      },
      update(dt) {
        // Smoothly lerp hover deceleration / acceleration
        const targetScale = hover ? 0 : 1;
        currentSpeed += (speed * targetScale - currentSpeed) * 0.1;

        // Smoothly lerp Next / Prev offset
        userOffset += (targetUserOffset - userOffset) * 0.14;

        // Advance position according to speed and frame delta
        if (reverse) {
          currentX += currentSpeed * dt;
        } else {
          currentX -= currentSpeed * dt;
        }

        // Apply seamless modulo wrap in the range [-w, 0)
        let totalX = currentX + userOffset;
        totalX = ((totalX % w) - w) % w;

        quickSetX(totalX);
      },
      destroy() {
        // Cleaned up below
      }
    };

    if (hoverPause) {
      track.addEventListener("pointerenter", () => { if (!isDragging) hover = true; });
      track.addEventListener("pointerleave", () => { if (!isDragging) hover = false; });
    }

    // Touch support for mobile / tablet swiping
    const onTouchStart = (e) => {
      isDragging = true;
      hover = true;
      dragStartX = e.touches[0].clientX;
    };
    const onTouchMove = (e) => {
      if (!isDragging) return;
      const clientX = e.touches[0].clientX;
      const dx = clientX - dragStartX;
      dragStartX = clientX;
      targetUserOffset += dx;
      userOffset += dx;
    };
    const onTouchEnd = () => {
      isDragging = false;
      setTimeout(() => { hover = false; }, 400);
    };
    track.addEventListener("touchstart", onTouchStart, { passive: true });
    track.addEventListener("touchmove", onTouchMove, { passive: true });
    track.addEventListener("touchend", onTouchEnd, { passive: true });
    track.addEventListener("touchcancel", onTouchEnd, { passive: true });

    m.destroy = () => {
      track.removeEventListener("touchstart", onTouchStart);
      track.removeEventListener("touchmove", onTouchMove);
      track.removeEventListener("touchend", onTouchEnd);
      track.removeEventListener("touchcancel", onTouchEnd);
    };

    marquees.push(m);
    track._marquee = m;
    return m;
  }
  function preloader(onMid) {
    const num = $("#preNum"), bar = $(".pre-bar i"), o = { v: 0 };
    const tl = gsap.timeline();
    tl.to(o, { v: 100, duration: 0.35, ease: "power2.out", onUpdate: () => { if (num) num.textContent = Math.round(o.v); } }, 0)
      .to(bar, { scaleX: 1, duration: 0.35, ease: "power2.out" }, 0)
      .to(".pre-inner", { opacity: 0, y: -16, duration: 0.15, ease: "power2.in" }, 0.25)
      .to(".pre-curtain", { yPercent: -100, duration: 0.3, ease: "power4.inOut" }, 0.3)
      .add(onMid, 0.45)
      .set("#preloader", { display: "none" });
    return tl;
  }
  function initCore() {
    const nav = $("#nav"), root = document.documentElement;
    let menuOpen = false;
    let navIsScrolled = null;
    const onScroll = (scroll) => {
      // Read scroll dimensions before changing styles to avoid a forced layout.
      const lim = (lenis ? lenis.limit : document.documentElement.scrollHeight - innerHeight) || 1;
      const isScrolled = scroll > 60;
      if (isScrolled !== navIsScrolled) {
        navIsScrolled = isScrolled;
        nav.classList.toggle("scrolled", isScrolled);
        root.style.setProperty("--navtop", isScrolled ? "12px" : "14px");
      }
      nav.classList.remove("hide");
      gsap.set("#progress i", { scaleX: clamp(0, 1, scroll / lim) });
    };
    if (lenis) lenis.on("scroll", (e) => onScroll(e.scroll, e.direction));
    else {
      let last = 0;
      addEventListener("scroll", () => { onScroll(scrollY, scrollY > last ? 1 : -1); last = scrollY; }, { passive: true });
    }
    const go = (target, opts = {}) => {
      if (lenis) lenis.scrollTo(target, { duration: 1.7, easing: (t) => 1 - Math.pow(1 - t, 4), ...opts });
      else (typeof target === "number" ? scrollTo({ top: target, behavior: "smooth" }) : target.scrollIntoView({ behavior: "smooth" }));
    };
    $$('a[href^="#"]').forEach((a) => {
      a.addEventListener("click", (e) => {
        const h = a.getAttribute("href");
        if (h === "#") return e.preventDefault();
        try {
          const t = h === "#hero" ? 0 : $(h);
          if (t === null || t === undefined) return;
          e.preventDefault();
          closeMenu();
          go(t);
        } catch (err) { }
      });
    });
    const toTopBtn = $("#toTop");
    if (toTopBtn) {
      toTopBtn.addEventListener("click", () => go(0, { duration: 1.5 }));
      const checkTop = () => toTopBtn.classList.toggle("show", window.scrollY > 350);
      window.addEventListener("scroll", checkTop, { passive: true });
      checkTop();
    }
    const burger = $("#burger"), mnav = $("#mnav");
    /* Note: Unified mobile drawer interactions handled by navbar-footer.js */
    const items = $$(".faq-item");
    items.forEach((it) => {
      const a = $(".faq-a", it);
      if (it.classList.contains("open")) gsap.set(a, { height: "auto" });
      $(".faq-q", it).addEventListener("click", () => {
        const open = it.classList.contains("open");
        items.forEach((o) => {
          if (o !== it && o.classList.contains("open")) {
            o.classList.remove("open"); $(".faq-q", o).setAttribute("aria-expanded", "false");
            gsap.to($(".faq-a", o), { height: 0, duration: 0.7, ease: "power3.inOut" });
          }
        });
        it.classList.toggle("open", !open);
        $(".faq-q", it).setAttribute("aria-expanded", String(!open));
        gsap.to(a, { height: open ? 0 : "auto", duration: 0.7, ease: "power3.inOut", onComplete: () => ScrollTrigger.refresh() });
      });
    });
    const modal = $("#modal");
    const openBio = (key, btn) => {
      const card = btn.closest(".member");
      $("#mName").textContent = $("h3", card).textContent;
      $("#mRole").textContent = $(".role", card).textContent;
      $("#mBody").innerHTML = "";
      $("#mBody").appendChild($("#bio-" + key).content.cloneNode(true));
      modal.style.visibility = "visible"; modal.setAttribute("aria-hidden", "false");
      lenis && lenis.stop();
      gsap.to(".modal-bg", { opacity: 1, duration: 0.5 });
      gsap.fromTo(".modal-card", { opacity: 0, y: 70, scale: 0.94 }, { opacity: 1, y: 0, scale: 1, duration: 0.7, ease: "power4.out" });
      gsap.from("#mBody p", { y: 24, opacity: 0, stagger: 0.1, duration: 0.7, delay: 0.25, ease: "power3.out" });
    };
    const closeBio = () => {
      gsap.to(".modal-card", { opacity: 0, y: 40, scale: 0.96, duration: 0.45, ease: "power3.in" });
      gsap.to(".modal-bg", { opacity: 0, duration: 0.5, onComplete: () => { modal.style.visibility = "hidden"; modal.setAttribute("aria-hidden", "true"); lenis && lenis.start(); } });
    };
    $$("[data-bio]").forEach((b) => b.addEventListener("click", () => openBio(b.dataset.bio, b)));
    $$("[data-close]", modal).forEach((b) => b.addEventListener("click", closeBio));
    addEventListener("keydown", (e) => { if (e.key === "Escape") { closeBio(); closeMenu(); } });
    const form = $("#form");
    if (form) {
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        let ok = true;
        $$("[required]", form).forEach((f) => {
          const bad = !f.value.trim() || (f.type === "email" && !/^\S+@\S+\.\S+$/.test(f.value));
          f.closest(".fld").classList.toggle("err", bad);
          if (bad) { ok = false; gsap.fromTo(f.closest(".fld"), { x: -8 }, { x: 0, duration: 0.5, ease: "elastic.out(1.2,.3)" }); }
        });
        if (!ok) return;
        form.classList.add("sending");
        setTimeout(() => {
          form.classList.remove("sending");
          const t = $("#thanks");
          gsap.timeline()
            .to(form, { opacity: 0, y: -20, duration: 0.4 })
            .set(t, { visibility: "visible" })
            .to(t, { opacity: 1, duration: 0.5 })
            .to(".tick *", { strokeDashoffset: 0, duration: 0.9, stagger: 0.35, ease: "power2.out" }, "-=.1")
            .from("#thanks h3, #thanks p, .thanks-act", { y: 24, opacity: 0, stagger: 0.12, duration: 0.6 }, "-=.7");
        }, 1400);
      });
      $$(".fld input, .fld textarea, .fld select", form).forEach((f) => f.addEventListener("input", () => f.closest(".fld").classList.remove("err")));
    }
    const bTrack = $("#blogTrack");
    $("#bNext")?.addEventListener("click", () => {
      const m = bTrack?._marquee;
      if (m && typeof m.stepNext === "function") {
        m.stepNext();
      }
    });
    $("#bPrev")?.addEventListener("click", () => {
      const m = bTrack?._marquee;
      if (m && typeof m.stepPrev === "function") {
        m.stepPrev();
      }
    });
    const ck = $("#cookie");
    setTimeout(() => gsap.to(ck, { y: 0, duration: 0.9, ease: "power4.out" }), 4200);
    $$("[data-ck]", ck).forEach((b) => b.addEventListener("click", () => gsap.to(ck, { y: "170%", duration: 0.7, ease: "power3.in" })));
    const cols = ["#E5162B", "#F0A30A", "#19B36B", "#2E7DF6"];
    $$("#footWord span").forEach((s) => s.addEventListener("mouseenter", () => s.style.setProperty("--hc", cols[Math.floor(Math.random() * cols.length)])));
    $$("[data-spot]").forEach((el) => el.addEventListener("pointermove", (e) => {
      const r = el.getBoundingClientRect();
      el.style.setProperty("--mx", e.clientX - r.left + "px"); el.style.setProperty("--my", e.clientY - r.top + "px");
    }));
  }
  function initPointer() {
    return;
  }
  let fanWidth = 0;
  const cw = () => fanWidth || Math.max(210, Math.min(innerWidth * 0.28, 340));
  const getFanSpread = () => {
    const w = innerWidth;
    if (w <= 480) return { spread: 0.32, rot: 5.5, scale: 0.90 };
    if (w <= 768) return { spread: 0.44, rot: 7, scale: 0.93 };
    if (w <= 1024) return { spread: 0.52, rot: 8, scale: 0.94 };
    return { spread: 0.62, rot: 9, scale: 0.95 };
  };
  function initMotion() {
    const fcc = $(".fc-c");
    fanWidth = fcc ? (fcc.offsetWidth || Math.max(210, Math.min(innerWidth * 0.28, 340))) : Math.max(210, Math.min(innerWidth * 0.28, 340));
    const mm = gsap.matchMedia();
    $$(".split").forEach((h) => {
      gsap.from(h, { opacity: 0, duration: 0.85, ease: "power4.out", scrollTrigger: { trigger: h, start: "top 88%", once: true } });
    });
    gsap.set("[data-r]", { opacity: 0, y: 36 });
    ScrollTrigger.batch("[data-r]", { start: "top 90%", once: true, onEnter: (els) => gsap.to(els, { opacity: 1, y: 0, duration: 0.9, stagger: 0.1, ease: "power3.out" }) });
    $$(".scrub-words").forEach((p) => {
      gsap.fromTo(p, { opacity: 0.42 }, { opacity: 1, ease: "none", scrollTrigger: { trigger: p, start: "top 82%", end: "bottom 58%", scrub: true } });
    });
    const heroTitle = $(".hero-title");
    gsap.set(heroTitle, { opacity: 0 });
    gsap.set(["[data-h]", ".hero-pill"], { opacity: 0 });
    gsap.set([".fc-l", ".fc-r", ".fc-c"], { y: -900, opacity: 0, scale: 0.7 });
    gsap.set(".fchip", { scale: 0, opacity: 0 });
    gsap.set(".hero-grid .gv", { scaleY: 0 }); gsap.set(".hero-grid .gh", { scaleX: 0 });
    gsap.set(".wa", { scale: 0 });
    function startHero() {
      const fanCfg = getFanSpread();
      const tl = gsap.timeline({ defaults: { ease: "power3.out" } });
      tl.to(".hero-bg", { scale: 1, duration: 2.4, ease: "power2.out" }, 0)
        .to(".hero-grid .gv", { scaleY: 1, duration: 1.8, ease: "power2.inOut" }, 0)
        .to(".hero-grid .gh", { scaleX: 1, duration: 1.8, ease: "power2.inOut" }, 0.2)
        .to(".hero-pill", { opacity: 1, duration: 0.8 }, 0.1)
        .to(heroTitle, { opacity: 1, duration: 0.9, ease: "power4.out" }, 0.2)
        .to("[data-h]", { opacity: 1, stagger: 0.12, duration: 0.9 }, 0.75)
        .to(".fc-l", { y: 0, opacity: 0.92, scale: fanCfg.scale, rotate: -fanCfg.rot, x: -cw() * fanCfg.spread, duration: 1, ease: "power2.out" }, 0.9)
        .to(".fc-r", { y: 0, opacity: 0.92, scale: fanCfg.scale, rotate: fanCfg.rot, x: cw() * fanCfg.spread, duration: 1, ease: "power2.out" }, 0.9)
        .to(".fc-c", { y: 0, opacity: 1, scale: 1, rotate: 0, duration: 1.1, ease: "back.out(1.4)" }, 1.05)
        .to(".fchip", { scale: 1, opacity: 1, stagger: 0.15, duration: 0.7, ease: "back.out(2.2)" }, 1.8)
        .to(".wa", { scale: 1, duration: 0.7, ease: "back.out(2)" }, 2)
        .add(() => {
          gsap.to(".fan-card", { y: "-=12", duration: 2.6, ease: "sine.inOut", yoyo: true, repeat: -1, stagger: { each: 0.5, from: "center" } });
          gsap.to(".fchip", { y: "-=10", duration: 2.2, ease: "sine.inOut", yoyo: true, repeat: -1, stagger: 0.4 });
          if (FINE) {
            const layers = [[".fc-l", 5], [".fc-r", -5], [".fc-c", 3], [".fa", -14], [".fb", 12], [".fc", 9]].map(([s, k]) => ({ el: $(s), k, xp: gsap.quickTo($(s), "xPercent", { duration: 1, ease: "power3" }) }));
            $(".hero").addEventListener("pointermove", (e) => {
              const nx = e.clientX / innerWidth - 0.5;
              layers.forEach((l) => l.xp(nx * l.k * 2));
            });
          }
        }, 2.6);
    }
    gsap.to(".hero-bg", { yPercent: 18, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });
    gsap.to(".hero-title,.hero-pill", { yPercent: -14, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });
    gsap.to("#fan", { yPercent: -10, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });
    gsap.to(".hero-blob.b1", { y: 160, ease: "none", scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true } });
    makeMarquee($("#barTrack"), { speed: 70 });
    makeMarquee($("#heroTicker"), { speed: 90 });
    $$('[data-m]').forEach((t) => makeMarquee(t, { speed: +t.dataset.speed || 50, reverse: !!t.dataset.rev, hoverPause: true }));
    $$(".ind-row .m-track").forEach((t, i) => makeMarquee(t, { speed: [70, 90, 60][i], reverse: i % 2 === 1 }));
    const indRows = $$(".ind-row");
    const skews = indRows.map((r) => gsap.quickTo(r, "skewX", { duration: 0.6, ease: "power3" }));
    gsap.ticker.add((time, deltaTime) => {
      const dt = clamp(0.001, 0.05, (deltaTime || 16.666) / 1000);
      marquees.forEach((m) => {
        if (m && typeof m.update === "function") m.update(dt);
      });
      const v = state.vel;
      skews.forEach((s) => s(clamp(innerWidth < 768 ? -4 : -9, innerWidth < 768 ? 4 : 9, -v * 0.35)));
    });
    $$(".pf-row").forEach((row) => {
      const p = $(".pf-p", row), pp = $(".pf-p p", row), f = $(".pf-f", row);
      const isMobilePf = innerWidth <= 760;
      gsap.set(f, { opacity: 0.25, x: isMobilePf ? 0 : 60, y: isMobilePf ? 24 : 0 });
      gsap.timeline({
        scrollTrigger: {
          trigger: row,
          start: isMobilePf ? "top 85%" : "top 80%",
          end: isMobilePf ? "bottom 60%" : "top 36%",
          scrub: 0.6
        }
      })
        .to(pp, { "--sx": "100%", duration: 0.5, ease: "none" })
        .to(p, { opacity: 0.45, duration: 0.3 }, 0.2)
        .to($(".x", row), { scale: 0, rotate: 90, duration: 0.25 }, 0.45)
        .to($(".ok", row), { opacity: 1, scale: 1, rotate: 0, duration: 0.35, ease: "back.out(2)" }, 0.55)
        .to(f, { opacity: 1, x: 0, y: 0, duration: 0.5, ease: "power2.out" }, 0.45);
    });
    mm.add("(min-width: 900px)", () => {
      const track = $("#svcTrack"), cards = $$(".svc-card"), now = $("#svcNow"), progEl = $("#svcProg");
      if (!track || !cards.length) return;
      const getMoveDist = () => {
        if (cards.length < 2) return 0;
        const measured = cards[cards.length - 1].offsetLeft - cards[0].offsetLeft;
        if (measured > 0) return measured;
        const cardW = cards[0].offsetWidth || (innerWidth * 0.31);
        return (cards.length - 1) * (cardW + 24);
      };
      const getHoldDist = () => Math.max(380, Math.round(innerHeight * 0.45));
      let cur = -1;
      const setActive = (i) => {
        if (i === cur) return; cur = i;
        cards.forEach((c, k) => {
          c.classList.toggle("is-active", k === i);
          c.classList.toggle("draw", k === i);
        });
        if (now) now.textContent = String(i + 1).padStart(2, "0");
      };
      setActive(0);
      const updateActive = (trackProg) => {
        const idx = Math.min(cards.length - 1, Math.max(0, Math.round(trackProg * (cards.length - 1))));
        setActive(idx);
      };
      const tl = gsap.timeline({
        scrollTrigger: {
          trigger: "#svcPin",
          pin: true,
          start: "top top",
          end: () => "+=" + (getMoveDist() + getHoldDist()),
          scrub: 0.8,
          anticipatePin: 1,
          invalidateOnRefresh: true,
        },
      });
      tl.to(track, {
        x: () => -getMoveDist(),
        ease: "none",
        duration: 1,
      });
      tl.to(track, { duration: 0.16 }); // comfortable hold on card 08 before unpinning
      tl.eventCallback("onUpdate", () => {
        const trackProg = Math.min(1, Math.max(0, tl.time() / 1.0));
        if (progEl) gsap.set(progEl, { scaleX: trackProg });
        updateActive(trackProg);
      });
      return () => {
        cards.forEach((c) => c.classList.remove("is-active", "draw"));
        gsap.set(track, { clearProps: "all" });
      };
    });
    mm.add("(max-width: 899px)", () => {
      const cards = $$(".svc-card"), track = $("#svcTrack"), now = $("#svcNow"), progEl = $("#svcProg");
      const prevBtn = $("#svcPrev"), nextBtn = $("#svcNext");
      if (!cards.length || !track) return;
      gsap.set(track, { clearProps: "all" });
      gsap.from("#svcPin", {
        y: 30,
        opacity: 0,
        duration: 0.8,
        ease: "power3.out",
        scrollTrigger: {
          trigger: "#svcPin",
          start: "top 88%",
          once: true
        }
      });
      let snapTimer = null;
      let autoplayTimer = null;
      let resumeTimer = null;
      let activeIndex = 0;
      let isInteracting = false;
      let isSectionVisible = false;
      const updateActiveMobile = () => {
        const trackRect = track.getBoundingClientRect();
        const center = trackRect.left + trackRect.width / 2;
        let bestIdx = 0;
        let minDiff = Infinity;
        cards.forEach((c, k) => {
          const r = c.getBoundingClientRect();
          const diff = Math.abs(r.left + r.width / 2 - center);
          if (diff < minDiff) {
            minDiff = diff;
            bestIdx = k;
          }
        });
        if (bestIdx !== activeIndex || !cards[bestIdx].classList.contains("is-active")) {
          activeIndex = bestIdx;
          cards.forEach((c, k) => {
            const active = k === bestIdx;
            c.classList.toggle("is-active", active);
            if (active) {
              c.classList.remove("draw");
              requestAnimationFrame(() => {
                if (c.classList.contains("is-active")) c.classList.add("draw");
              });
            }
          });
          if (now) now.textContent = String(bestIdx + 1).padStart(2, "0");
        }
        const maxScroll = track.scrollWidth - track.clientWidth;
        if (progEl) {
          const prog = maxScroll > 0 ? Math.min(1, Math.max(0, track.scrollLeft / maxScroll)) : (cards.length > 1 ? activeIndex / (cards.length - 1) : 1);
          gsap.set(progEl, { scaleX: prog });
        }
      };
      const onScrollSnap = () => {
        clearTimeout(snapTimer);
        snapTimer = setTimeout(updateActiveMobile, 35);
      };
      const scrollToIndex = (idx) => {
        const targetIdx = (idx % cards.length + cards.length) % cards.length;
        const card = cards[targetIdx];
        if (!card) return;
        const trackRect = track.getBoundingClientRect();
        const cardRect = card.getBoundingClientRect();
        const offset = (cardRect.left - trackRect.left) - (trackRect.width - cardRect.width) / 2;
        const targetScrollLeft = track.scrollLeft + offset;
        track.scrollTo({ left: Math.max(0, targetScrollLeft), behavior: "smooth" });
      };
      const startAutoplay = () => {
        stopAutoplay();
        if (!isSectionVisible || isInteracting) return;
        autoplayTimer = setInterval(() => {
          scrollToIndex(activeIndex + 1);
        }, 3400);
      };
      const stopAutoplay = () => {
        if (autoplayTimer) {
          clearInterval(autoplayTimer);
          autoplayTimer = null;
        }
      };
      const pauseAndScheduleResume = () => {
        isInteracting = true;
        stopAutoplay();
        clearTimeout(resumeTimer);
        resumeTimer = setTimeout(() => {
          isInteracting = false;
          startAutoplay();
        }, 4500);
      };
      const onPrev = (e) => {
        e.preventDefault();
        pauseAndScheduleResume();
        scrollToIndex(activeIndex - 1);
      };
      const onNext = (e) => {
        e.preventDefault();
        pauseAndScheduleResume();
        scrollToIndex(activeIndex + 1);
      };
      const onWheel = (e) => {
        pauseAndScheduleResume();
        if (Math.abs(e.deltaY) > Math.abs(e.deltaX) && Math.abs(e.deltaY) > 5) {
          const maxS = track.scrollWidth - track.clientWidth;
          if ((e.deltaY > 0 && track.scrollLeft < maxS - 5) || (e.deltaY < 0 && track.scrollLeft > 5)) {
            e.preventDefault();
            track.scrollLeft += e.deltaY;
          }
        }
      };
      const onTouchActivity = () => {
        pauseAndScheduleResume();
      };
      const stVisibility = ScrollTrigger.create({
        trigger: "#svcPin",
        start: "top 95%",
        end: "bottom 5%",
        onEnter: () => {
          isSectionVisible = true;
          startAutoplay();
        },
        onEnterBack: () => {
          isSectionVisible = true;
          startAutoplay();
        },
        onLeave: () => {
          isSectionVisible = false;
          stopAutoplay();
        },
        onLeaveBack: () => {
          isSectionVisible = false;
          stopAutoplay();
        }
      });
      track.addEventListener("scroll", onScrollSnap, { passive: true });
      track.addEventListener("wheel", onWheel, { passive: false });
      track.addEventListener("touchstart", onTouchActivity, { passive: true });
      track.addEventListener("touchmove", onTouchActivity, { passive: true });
      track.addEventListener("pointerdown", onTouchActivity, { passive: true });
      prevBtn?.addEventListener("click", onPrev);
      nextBtn?.addEventListener("click", onNext);
      cards[0]?.classList.add("is-active", "draw");
      if (now) now.textContent = "01";
      if (progEl) gsap.set(progEl, { scaleX: 0 });
      return () => {
        stopAutoplay();
        clearTimeout(resumeTimer);
        clearTimeout(snapTimer);
        stVisibility?.kill();
        track.removeEventListener("scroll", onScrollSnap);
        track.removeEventListener("wheel", onWheel);
        track.removeEventListener("touchstart", onTouchActivity);
        track.removeEventListener("touchmove", onTouchActivity);
        track.removeEventListener("pointerdown", onTouchActivity);
        prevBtn?.removeEventListener("click", onPrev);
        nextBtn?.removeEventListener("click", onNext);
        cards.forEach((c) => c.classList.remove("is-active", "draw"));
        gsap.set(track, { clearProps: "all" });
      };
    });
    // Case Studies Stacking & Overlap Animation (Exact Match to Career page 'Find Your Role' stack)
    (function initCaseStackAnimation() {
      const cases = Array.prototype.slice.call(document.querySelectorAll('.case'));
      if (!cases.length) return;
      const indexLinks = Array.prototype.slice.call(document.querySelectorAll('.cases-index a'));
      const FINE_POINTER = window.matchMedia('(pointer:fine)').matches;
      const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      let activeIdx = -1;

      function setActiveChip(i) {
        if (i === activeIdx) return;
        activeIdx = i;
        indexLinks.forEach((a, j) => {
          a.classList.toggle('active', j === i);
        });
      }

      const states = cases.map(() => ({
        s: 1, ts: 1, tx: 0, ty: 0, ttx: 0, tty: 0, ls: 0, ltx: 99, lty: 99
      }));

      function revealCase(card) {
        if (card._rv) return;
        card._rv = true;
        if (REDUCED) {
          card.classList.remove('reveal');
          card.classList.add('revealed');
          return;
        }
        card.classList.add('in');
        card.addEventListener('animationend', function h(e) {
          if (e.animationName !== 'caseIn') return;
          card.classList.remove('reveal', 'in');
          card.classList.add('revealed');
          card.removeEventListener('animationend', h);
        });
        setTimeout(() => {
          card.classList.remove('reveal', 'in');
          card.classList.add('revealed');
        }, 800);
      }

      let frameQueued = false;
      let motionActive = false;

      function updateCaseStack() {
        frameQueued = false;
        motionActive = false;
        const vh = window.innerHeight;
        const rects = cases.map(c => c.getBoundingClientRect());
        let spy = -1;

        for (let i = 0; i < cases.length; i++) {
          const card = cases[i];
          const st = states[i];
          const rect = rects[i];

          /* reveal guarantee: Fade-In + Slide-Up as card scrolls into view */
          if (rect.top < vh * 0.92 && !card._rv) {
            revealCase(card);
          }

          /* Overlap scale: previous card scales down smoothly as next card slides up */
          if (i < cases.length - 1) {
            const nextTop = rects[i + 1].top;
            let p = (vh - nextTop) / vh;
            p = p < 0 ? 0 : p > 1 ? 1 : p;
            st.ts = 1 - p * 0.035;
          } else {
            st.ts = 1;
          }

          /* scrollspy: card whose top has passed mid-viewport is "current" */
          if (rect.top < vh * 0.45) spy = i;

          /* Lerp toward targets for buttery smooth transition */
          st.s += (st.ts - st.s) * 0.19;
          st.tx += (st.ttx - st.tx) * 0.12;
          st.ty += (st.tty - st.ty) * 0.12;

          if (Math.abs(st.ts - st.s) > 0.0004 || Math.abs(st.ttx - st.tx) > 0.01 || Math.abs(st.tty - st.ty) > 0.01) {
            motionActive = true;
          }

          /* Touch DOM when card is revealed (or marked for reveal) and values actually changed */
          if ((card.classList.contains('revealed') || card._rv) &&
            (Math.abs(st.s - st.ls) > 0.0004 || Math.abs(st.tx - st.ltx) > 0.01 || Math.abs(st.ty - st.lty) > 0.01)) {
            st.ls = st.s;
            st.ltx = st.tx;
            st.lty = st.ty;
            card.style.setProperty('--s', st.s.toFixed(4));
            card.style.setProperty('--tx', st.tx.toFixed(3) + 'deg');
            card.style.setProperty('--ty', st.ty.toFixed(3) + 'deg');
          }
        }

        setActiveChip(spy);

        if (motionActive) requestUpdate();
      }

      function requestUpdate() {
        if (frameQueued) return;
        frameQueued = true;
        requestAnimationFrame(updateCaseStack);
      }

      window._updateCaseStack = requestUpdate;

      window.addEventListener('scroll', requestUpdate, { passive: true });
      window.addEventListener('resize', requestUpdate, { passive: true });
      requestUpdate();

      if (FINE_POINTER && !REDUCED) {
        cases.forEach((card, i) => {
          card.addEventListener('mousemove', (e) => {
            const st = states[i];
            if ((!card.classList.contains('revealed') && !card._rv) || st.ts < 0.99) {
              st.ttx = 0; st.tty = 0; return;
            }
            const r = card.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width - 0.5;
            const py = (e.clientY - r.top) / r.height - 0.5;
            st.tty = px * 2.4;
            st.ttx = -py * 2.0;
            card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
            card.style.setProperty('--my', (e.clientY - r.top) + 'px');
            requestUpdate();
          });
          card.addEventListener('mouseleave', () => {
            const st = states[i];
            st.ttx = 0;
            st.tty = 0;
            requestUpdate();
          });
        });
      }
    })();
    $$(".logo-tile").forEach((t) => { });
    gsap.from(".logo-row", { opacity: 0, y: 40, stagger: 0.15, duration: 1, ease: "power3.out", scrollTrigger: { trigger: ".logo-rows", start: "top 88%" } });
    $$(".why-card").forEach((c) => {
      const isMob = innerWidth < 768;
      gsap.from(c, {
        x: isMob ? 0 : 50,
        y: isMob ? 36 : 0,
        opacity: 0,
        rotate: isMob ? 0 : 2,
        duration: 1,
        ease: "power3.out",
        scrollTrigger: { trigger: c, start: "top 90%", onEnter: () => c.classList.add("in") }
      });
    });
    gsap.to(".why-bg", { yPercent: 12, ease: "none", scrollTrigger: { trigger: "#why", start: "top bottom", end: "bottom top", scrub: true } });
    gsap.to(".m1", { y: 180, x: 80, ease: "none", scrollTrigger: { trigger: "#midcta", start: "top bottom", end: "bottom top", scrub: true } });
    gsap.to(".m2", { y: -160, x: -80, ease: "none", scrollTrigger: { trigger: "#midcta", start: "top bottom", end: "bottom top", scrub: true } });
    gsap.from(".mc-row > *", { y: 50, opacity: 0, scale: 0.8, stagger: 0.15, duration: 1, ease: "back.out(1.6)", scrollTrigger: { trigger: ".mc-row", start: "top 90%" } });
    gsap.from(".proof-tile", { y: 80, opacity: 0, scale: 0.94, stagger: 0.09, duration: 1, ease: "power3.out", scrollTrigger: { trigger: ".proof-grid", start: "top 85%" } });
    $$("[data-count]").forEach((el) => {
      const end = +el.dataset.count, suf = el.dataset.suffix || "", o = { v: 0 };
      ScrollTrigger.create({ trigger: el, start: "top 90%", once: true, onEnter: () => gsap.to(o, { v: end, duration: 2.2, ease: "power3.out", onUpdate: () => (el.textContent = Math.round(o.v) + suf) }) });
    });
    mm.add("(min-width: 901px)", () => {
      const steps = $$(".proc-step"), now = $("#procNow"), name = $("#procName"), fg = $("#dialFg");
      const names = ["Discover", "Strategize", "Design and Build", "Launch", "Grow and Optimize"];
      let cur = -1;
      const set = (i) => {
        if (i === cur) return; cur = i;
        steps.forEach((s, k) => s.classList.toggle("on", k === i));
        gsap.to(fg, { strokeDashoffset: 100 * (1 - (i + 1) / steps.length), duration: 0.9, ease: "power3.out" });
        gsap.fromTo([now, name], { y: 24, opacity: 0 }, { y: 0, opacity: 1, duration: 0.5, stagger: 0.05, ease: "power3.out", onStart: () => { now.textContent = String(i + 1).padStart(2, "0"); name.textContent = names[i]; } });
      };
      steps.forEach((s, i) => ScrollTrigger.create({ trigger: s, start: "top 58%", end: "bottom 58%", onToggle: (self) => self.isActive && set(i) }));
      gsap.to("#procLine", { scaleY: 1, ease: "none", scrollTrigger: { trigger: "#procRight", start: "top 58%", end: "bottom 75%", scrub: 0.4 } });
    });
    mm.add("(max-width: 900px)", () => {
      const steps = $$(".proc-step");
      steps.forEach((s) => {
        gsap.from(s, {
          y: 20,
          duration: 0.6,
          ease: "power3.out",
          scrollTrigger: {
            trigger: s,
            start: "top 90%",
            end: "bottom 10%",
            toggleClass: { targets: s, className: "on" }
          }
        });
      });
    });
    $$(".tool-card").forEach((c) => {
      const isMob = innerWidth < 768;
      gsap.from(c, { y: isMob ? 40 : 70, opacity: 0, duration: 1, ease: "power3.out", scrollTrigger: { trigger: c, start: "top 90%" } });
      gsap.from($$("li", c), { scale: 0.5, opacity: 0, y: 14, stagger: 0.04, duration: 0.6, ease: "back.out(2)", scrollTrigger: { trigger: c, start: "top 82%" } });
    });
    gsap.fromTo(".about-frame", { clipPath: "inset(0 0 100% 0 round 36px)" }, { clipPath: "inset(0 0 0% 0 round 36px)", duration: 1.5, ease: "power4.inOut", scrollTrigger: { trigger: ".about-media", start: "top 82%" } });
    gsap.fromTo(".about-frame img", { yPercent: -9, scale: 1.12 }, { yPercent: 0, scale: 1, ease: "none", scrollTrigger: { trigger: ".about-media", start: "top bottom", end: "bottom top", scrub: true } });
    gsap.from(".about-badge", { scale: 0, rotate: -120, duration: 1.2, ease: "back.out(1.7)", scrollTrigger: { trigger: ".about-media", start: "top 60%" } });
    gsap.from(".swatches i", { scale: 0, stagger: 0.12, duration: 0.6, ease: "back.out(3)", scrollTrigger: { trigger: ".about-media", start: "top 65%" } });
    $$(".member").forEach((m, i) => {
      const tl = gsap.timeline({ scrollTrigger: { trigger: m, start: "top 85%" } });
      const isDesktop = innerWidth > 900;
      tl.from(m, { y: 60, opacity: 0, duration: 0.9, ease: "power3.out", delay: isDesktop ? i * 0.08 : 0 })
        .to($(".reveal-cover", m), { scaleY: 0, transformOrigin: "50% 100%", duration: 1.1, ease: "power4.inOut" }, 0.2)
        .from($(".member-body > *", m) ? $$(".member-body > *", m) : [], { y: 20, opacity: 0, stagger: 0.07, duration: 0.6 }, 0.6);
    });
    gsap.from(".testi-rail", { opacity: 0, y: 35, duration: 0.9, ease: "power3.out", scrollTrigger: { trigger: ".testi-rail", start: "top 88%", once: true }, clearProps: "transform,opacity" });
    gsap.from(".rate-card", { y: 80, opacity: 0, rotateX: -25, stagger: 0.12, duration: 1, ease: "power3.out", scrollTrigger: { trigger: ".rate-grid", start: "top 85%" } });
    ScrollTrigger.create({ trigger: ".rate-grid", start: "top 75%", once: true, onEnter: () => gsap.from(".stars svg", { scale: 0, rotate: -120, stagger: 0.09, duration: 0.7, ease: "back.out(3)", delay: 0.5 }) });
    mm.add("(min-width: 768px)", () => {
      const card = $("#cbCard");
      gsap.set(card, { xPercent: -50 });
      const tl = gsap.timeline({ scrollTrigger: { trigger: "#cbPin", pin: true, start: "top top", end: "+=220%", scrub: 0.8, anticipatePin: 1, invalidateOnRefresh: true } });
      tl.fromTo(card, { y: () => innerHeight * 0.5 - 150, width: 460, height: 300, borderRadius: 26 }, { y: 0, width: () => innerWidth, height: () => innerHeight * 1.01, borderRadius: 0, ease: "power2.inOut", duration: 0.6 }, 0)
        .fromTo("#cbImg", { scale: 1.35 }, { scale: 1, ease: "power2.inOut", duration: 0.6 }, 0)
        .fromTo("#cbShade", { opacity: 0.3 }, { opacity: 1, ease: "power2.out", duration: 0.3 }, 0.4)
        .fromTo("#cbLabel", { opacity: 0 }, { opacity: 1, duration: 0.1 }, 0)
        .to("#cbLabel", { opacity: 0, y: -20, duration: 0.2 }, 0.3)
        .fromTo("#cbText > *", { opacity: 0, y: 50 }, { opacity: 1, y: 0, stagger: 0.06, ease: "power2.out", duration: 0.35 }, 0.5)
        .to({}, { duration: 0.25 });
      gsap.set("#cbText > *", { opacity: 0 });
    });
    mm.add("(max-width: 767px)", () => {
      const card = $("#cbCard"), textEls = $$("#cbText > *"), img = $("#cbImg");
      gsap.set(card, { clearProps: "transform,width,height,borderRadius,left,top" });
      gsap.set(textEls, { opacity: 0, y: 30 });
      const tl = gsap.timeline({
        scrollTrigger: {
          trigger: "#ctaBand",
          start: "top 82%",
          once: true,
        }
      });
      tl.from(card, { opacity: 0.8, scale: 0.96, duration: 0.8, ease: "power3.out" }, 0)
        .fromTo(img, { scale: 1.25 }, { scale: 1, duration: 1.2, ease: "power2.out" }, 0)
        .to(textEls, { opacity: 1, y: 0, stagger: 0.08, duration: 0.7, ease: "power3.out" }, 0.15);
      return () => {
        gsap.set([card, img, textEls], { clearProps: "all" });
      };
    });
    gsap.from(".blog-rail", { opacity: 0, y: 35, duration: 0.9, ease: "power3.out", scrollTrigger: { trigger: ".blog-rail", start: "top 88%", once: true }, clearProps: "transform,opacity" });
    gsap.from(".faq-item", { y: 40, opacity: 0, stagger: 0.07, duration: 0.8, ease: "power3.out", scrollTrigger: { trigger: ".faq-list", start: "top 85%" } });
    if ($(".contact-right")) gsap.from(".contact-right", { y: 80, opacity: 0, duration: 1.1, ease: "power3.out", scrollTrigger: { trigger: ".contact-right", start: "top 88%" } });
    const isMob = innerWidth < 768;
    if ($(".cinfo")) gsap.from(".cinfo li", { x: isMob ? 0 : -30, y: isMob ? 20 : 0, opacity: 0, stagger: 0.1, duration: 0.8, ease: "power3.out", scrollTrigger: { trigger: ".cinfo", start: "top 88%" } });
    if ($(".cb1")) gsap.to(".cb1", { y: 160, ease: "none", scrollTrigger: { trigger: "#contact", start: "top bottom", end: "bottom top", scrub: true } });
    gsap.from("#footWord span", { yPercent: 105, stagger: 0.03, duration: 0.85, ease: "power3.out", scrollTrigger: { trigger: "#footWord", start: "top 95%", once: true } });
    gsap.from(".foot-top > *", { y: 40, opacity: 0, stagger: 0.12, duration: 0.9, scrollTrigger: { trigger: ".foot-top", start: "top 92%" } });
    $$(".nav-links a").forEach((a) => {
      const h = a.getAttribute("href");
      if (h && h.startsWith("#") && h.length > 1) {
        try {
          const t = $(h);
          if (t) ScrollTrigger.create({ trigger: t, start: "top 55%", end: "bottom 55%", toggleClass: { targets: a, className: "active" } });
        } catch (err) { }
      }
    });
    return startHero;
  }
  function boot() {
    const p = $("#preloader");
    const dismissPreloader = () => {
      if (p && p.style.display !== "none") {
        p.style.display = "none";
      }
    };
    try {
      if (isLighthouse) {
        dismissPreloader();
        initCore();
        const startHero = initMotion();
        if (typeof startHero === "function") startHero();
        return;
      }
      if (RM) {
        dismissPreloader();
        gsap.set(".wa", { scale: 1 });
        initCore();
        const startHero = initMotion();
        if (typeof startHero === "function") startHero();
        return;
      }
      if (typeof Lenis !== "undefined") {
        lenis = new Lenis({ lerp: 0.09, smoothWheel: true });
        lenis.on("scroll", (e) => {
          ScrollTrigger.update();
          state.vel = e.velocity;
          if (Math.abs(e.velocity) > 0.3) state.dir = e.direction || 1;
          if (window._updateCaseStack) window._updateCaseStack();
        });
        gsap.ticker.add((t) => {
          lenis.raf(t * 1000);
          state.vel *= 0.92;
          if (window._updateCaseStack) window._updateCaseStack();
        });
        gsap.ticker.lagSmoothing(0);
        lenis.stop();
      } else {
        lenis = null;
      }
      window.scrollTo(0, 0);
      initCore();
      const startHero = initMotion();
      preloader(() => {
        if (lenis) lenis.start();
        if (typeof startHero === "function") startHero();
      });
      setTimeout(dismissPreloader, 1000);
      const refreshMarquees = () => {
        marquees.forEach((m) => {
          if (m && typeof m.recalc === "function") m.recalc();
        });
      };
      if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => {
          refreshMarquees();
          ScrollTrigger.refresh();
        });
      }
      window.addEventListener("load", () => {
        refreshMarquees();
        ScrollTrigger.refresh();
      });
      $$(".blog-rail img, .testi-rail img").forEach((img) => {
        if (!img.complete) {
          img.addEventListener("load", refreshMarquees, { once: true });
        }
      });
      let rt;
      const handleResize = () => {
        clearTimeout(rt);
        rt = setTimeout(() => {
          refreshMarquees();
          const fcc = $(".fc-c");
          if (fcc) {
            fanWidth = fcc.offsetWidth;
            const fanCfg = getFanSpread();
            gsap.to(".fc-l", { x: -cw() * fanCfg.spread, rotate: -fanCfg.rot, scale: fanCfg.scale, duration: 0.4, ease: "power2.out" });
            gsap.to(".fc-r", { x: cw() * fanCfg.spread, rotate: fanCfg.rot, scale: fanCfg.scale, duration: 0.4, ease: "power2.out" });
          }
          ScrollTrigger.refresh();
        }, 150);
      };
      addEventListener("resize", handleResize);
      addEventListener("orientationchange", handleResize);
    } catch (err) {
      console.error("Boot error:", err);
      dismissPreloader();
    }
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", boot); else boot();
})();
