(function () {
  function onReady(callback) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", callback);
    } else {
      callback();
    }
  }

  onReady(function () {
    var body = document.body;
    var header = document.querySelector(".site-header");
    var menu = document.querySelector(".menu-block");
    var overlay = document.querySelector(".menu-overlay");
    var trigger = document.querySelector(".mobile-menu-trigger");
    var closeButton = document.querySelector(".mobile-menu-close");
    var dropdown = document.querySelector(".nav-item.dropdown");
    var dropdownLink = dropdown ? dropdown.querySelector(":scope > .nav-link-item") : null;

    function openMenu() {
      if (menu) menu.classList.add("active");
      if (overlay) overlay.classList.add("active");
      body.classList.add("menu-open");
    }

    function closeMenu() {
      if (menu) menu.classList.remove("active");
      if (overlay) overlay.classList.remove("active");
      if (dropdown) dropdown.classList.remove("header-dropdown-open");
      body.classList.remove("menu-open");
    }

    if (trigger) trigger.addEventListener("click", openMenu);
    if (overlay) overlay.addEventListener("click", closeMenu);
    if (closeButton) closeButton.addEventListener("click", closeMenu);

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") closeMenu();
    });

    if (dropdownLink && dropdown) {
      dropdownLink.addEventListener("click", function (event) {
        if (window.innerWidth <= 991) {
          event.preventDefault();
          dropdown.classList.toggle("header-dropdown-open");
        }
      });

      dropdown.addEventListener("mouseenter", function () {
        if (window.innerWidth > 991) dropdown.classList.add("header-dropdown-open");
      });

      dropdown.addEventListener("mouseleave", function () {
        if (window.innerWidth > 991) dropdown.classList.remove("header-dropdown-open");
      });
    }

    document.querySelectorAll(".menu-block a").forEach(function (link) {
      link.addEventListener("click", function () {
        var isServicesToggle = dropdownLink && link === dropdownLink && window.innerWidth <= 991;
        if (!isServicesToggle && window.innerWidth <= 991) closeMenu();
      });
    });

    window.addEventListener("resize", function () {
      if (window.innerWidth > 991) closeMenu();
    });

    window.addEventListener("scroll", function () {
      if (!header) return;
      header.classList.toggle("is-sticky", window.scrollY > 10);
    }, { passive: true });
  });
})();
