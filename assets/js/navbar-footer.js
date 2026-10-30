/* ================================================================
   CHAMELEO GFX STUDIO — UNIFIED HEADER, FOOTER & FLOATING ACTIONS JS
   Lightweight, robust, self-initializing on every page.
   ================================================================ */

(function () {
  "use strict";

  function initNavFooter() {
    // 1. Sticky Nav state
    var nav = document.getElementById("nav");
    var toTop = document.getElementById("toTop");

    function onScroll() {
      var y = window.scrollY || window.pageYOffset;
      if (nav) {
        nav.classList.toggle("scrolled", y > 30);
      }
      if (toTop) {
        toTop.classList.toggle("show", y > 350);
      }
    }

    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();

    // 2. Back to Top Button
    if (toTop) {
      toTop.addEventListener("click", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
    }

    // 3. Mobile Navigation Drawer
    var burger = document.getElementById("burger");
    var mnav = document.getElementById("mnav") || document.getElementById("drawer");

    if (burger && mnav) {
      if (burger._hasUnifiedNav) return;
      burger._hasUnifiedNav = true;

      function toggleMenu(forceClose) {
        var isOpen = forceClose ? false : !mnav.classList.contains("open");
        mnav.classList.toggle("open", isOpen);
        burger.classList.toggle("on", isOpen);
        burger.setAttribute("aria-expanded", isOpen ? "true" : "false");
        document.body.classList.toggle("menu-open", isOpen);
        document.body.style.overflow = isOpen ? "hidden" : "";
        if (window.lenis) {
          try {
            if (isOpen) {
              window.lenis.stop();
            } else {
              window.lenis.start();
            }
          } catch (err) {}
        }
      }

      burger.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleMenu();
      });

      var mlinks = mnav.querySelectorAll("a");
      for (var i = 0; i < mlinks.length; i++) {
        mlinks[i].addEventListener("click", function () {
          toggleMenu(true);
        });
      }

      // Close on Escape key
      document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && mnav.classList.contains("open")) {
          toggleMenu(true);
        }
      });

      // Close when clicking directly on backdrop
      mnav.addEventListener("click", function (e) {
        if (e.target === mnav) {
          toggleMenu(true);
        }
      });
    }

    // 4. Cookie Consent Banner (Persistent & Dismissible on mobile and desktop)
    var cookie = document.getElementById("cookie");
    if (cookie) {
      if (localStorage.getItem("cgs_cookie_consent") === "true") {
        cookie.style.display = "none";
        cookie.classList.remove("show");
      } else {
        setTimeout(function () {
          if (localStorage.getItem("cgs_cookie_consent") !== "true") {
            cookie.classList.add("show");
          }
        }, 2500);
      }

      var ckBtns = cookie.querySelectorAll("[data-ck]");
      for (var j = 0; j < ckBtns.length; j++) {
        ckBtns[j].addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          cookie.classList.remove("show");
          cookie.style.display = "none";
          try {
            localStorage.setItem("cgs_cookie_consent", "true");
          } catch (err) {}
        });
      }
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initNavFooter);
  } else {
    initNavFooter();
  }
})();
