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
- `local-fonts.css`, `navbar-footer.css`, `styles.css`, `inline-extracted.css`, `marquee-slider.css`, `services-header-dropdown.css`, `header-footer.css`, `blog.css`, `blog-redesign.css`, `about-page.css`, `contact.css`, `contact-us.css`, `service-pages-redesign.css`, `web-development-services.css`, `conversion-focused-web-services.css`, `bootstrap.min.css`, `swiper-bundle.min.css`, and generated bundle `index-DhDdM_gX.css` are retained pending full page-by-page dependency migration. The separate `/assets/site.css` file is outside this folder and has not been changed. Vendor and generated bundles are not edited.

## Notes
- HTML references to renamed files were updated in the same commit.
- No CSS declarations, selectors, breakpoints, animations, or values were changed.
- No file was deleted in this pass. Empty or apparently unreferenced files are retained until a complete reference scan can prove they are safe to remove.
- This is the first structural pass, not the complete one-page/one-stylesheet migration. Some pages still use multiple shared stylesheets or inline style blocks; these are intentionally not collapsed until their cascade and visual output can be verified page by page.


## Page entrypoints added in the follow-up pass

Each migrated HTML page now points to one page-level stylesheet entrypoint under `pages/`. These entrypoints import the existing shared styles in the page's former order; shared source stylesheets remain reusable and are not copied into every page.

- `pages/index.css` — homepage entrypoint; original homepage rules are retained in `pages/home-content.css`.
- `pages/seo-services.css` — SEO page entrypoint; original inline style blocks are retained in `pages/seo-services-inline.css`.
- `pages/career.css` — career page entrypoint; original inline styles are retained in `pages/career-inline.css`.
- `pages/about.css`, `pages/contact.css`, `pages/contact-legacy.css`, `pages/disclaimer.css`, `pages/privacy-policy.css`, `pages/refund-policy.css`, `pages/terms.css`, `pages/thank-you.css`, `pages/web-development-services.css`, `pages/sitemap.css`, and `pages/blogs.css` — individual page entrypoints.
- `pages/blog/index.css` and `pages/blog/website-design-trends-2026.css` — blog landing and article page entrypoints.
- `pages/blog/*.css` — dedicated entrypoints for individual blog articles. The short TOC style blocks on the affected articles were moved into their respective entrypoint files.

## Migration status

- Migrated the homepage, SEO services, career, about, contact/contact-legacy, legal/thank-you, web-development-services, sitemap, blog listing, and individual blog article pages to one local page-level stylesheet link.
- External font stylesheets and local-font preload/fallback links are intentionally retained where they are part of font loading rather than page styling.
- Existing shared CSS files, vendor bundles, and generated bundles remain in place. No uncertain or apparently unused stylesheet has been deleted.
- No selector, declaration value, breakpoint, animation, or design token was intentionally changed. CSS is consolidated through ordered imports and inline-style extraction only.
- A browser-based visual regression pass has not yet been run. Pages with complex generated bundles should still be checked at desktop, tablet, and mobile breakpoints before declaring the cleanup fully complete.
