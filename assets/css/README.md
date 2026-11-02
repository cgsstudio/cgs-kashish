# CSS inventory and structure

This directory is organized by responsibility. Existing stylesheet contents are preserved; this pass changes file locations/names and HTML references only.

## Page styles
- `pages/index.css` — homepage stylesheet previously named `site.css`.

## Reusable section/component styles
- `components/about-section.css` — About section styles.
- `components/why-choose-us-section.css` — Why Choose Us section styles.
- `components/testimonials-section.css` — Testimonials section styles.
- `components/home-one-section.css` — Home One section styles.
- `components/industries-home-section.css` — Industries section styles.
- `components/navbar-mega-menu.css` — shared mega-menu styles.
- `components/footer-layout.css` — shared footer layout styles.
- `components/whatsapp-widget.css` — WhatsApp floating widget styles.

## Shared and vendor styles retained in this directory
- `local-fonts.css`, `navbar-footer.css`, `styles.css`, `inline-extracted.css`, `marquee-slider.css`, `services-header-dropdown.css`, `header-footer.css`, `blog.css`, `blog-redesign.css`, `about-page.css`, `contact.css`, `contact-us.css`, `service-pages-redesign.css`, `web-development-services.css`, `conversion-focused-web-services.css`, `site.css`, `bootstrap.min.css`, `swiper-bundle.min.css`, and generated bundle `index-DhDdM_gX.css` are retained pending full page-by-page dependency migration. Vendor and generated bundles are not edited.

## Notes
- HTML references to renamed files were updated in the same commit.
- No CSS declarations, selectors, breakpoints, animations, or values were changed.
- No file was deleted in this pass. Empty or apparently unreferenced files are retained until a complete reference scan can prove they are safe to remove.
- Some pages still use multiple shared stylesheets or inline style blocks; these are intentionally not collapsed until their cascade and visual output can be verified page by page.
