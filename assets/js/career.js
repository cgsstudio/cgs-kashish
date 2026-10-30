/* =========================================================
       CAREER PAGE   JS
       1. Nav scroll state + burger + progress bar
       2. Stacked card overlap animation (prev scales/dims as next slides up)
       3. Reveal-on-scroll + scrollspy (roles index)
       4. Lazy-loaded Apply popup + validation + mail submit
       5. Interactions: cursor spotlight, magnetic buttons, count-up stats,
          hero parallax, copy-email toast
       ========================================================= */
    (function () {
      'use strict';

      /* ------------ CONFIG ------------ */
      var MAIL_ENDPOINT = '/career-mail.php';
      var HR_MAIL = 'career@chameleogfxstudio.com';
      var MAX_FILE_MB = 5;
      var OK_EXT = ['pdf', 'doc', 'docx', 'rtf', 'odt', 'zip'];
      var DEMO_MODE = /[?&]demo=1/.test(location.search); // career.html?demo=1 → fake success (preview only)
      var FINE_POINTER = window.matchMedia('(pointer:fine)').matches;

      var POSITIONS = [
        'Graphic Designer',
        'UI/UX Designer',
        'Web Developer',
        'SEO Expert',
        'Meta Ads Expert',
        'Performance Marketer',
        'Open Application / Other'
      ];

      /* ------------ 1. Nav + progress bar ------------ */
      var nav = document.getElementById('nav');
      var bar = document.getElementById('scrollBar');
      var toTop = document.getElementById('toTop');
      var toTopVisible = false;
      /* Note: burger and mnav mobile menu interaction is handled by navbar-footer.js */

      /* ------------ 2+3. MASTER ANIMATION LOOP ------------
         One rAF loop drives: nav state, progress bar, stack overlap
         (lerped → buttery smooth), card reveals, scrollspy, role bar. */
      var REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var cards = Array.prototype.slice.call(document.querySelectorAll('.job-card'));
      var indexLinks = Array.prototype.slice.call(document.querySelectorAll('.roles-index a'));
      var rolesSec = document.getElementById('open-roles');
      var roleBar = document.getElementById('roleBar');
      var rbLabel = document.getElementById('rbLabel');
      var rbApply = document.getElementById('rbApply');
      var activeIdx = -1;

      function pad2(n) { return n < 10 ? '0' + n : '' + n; }

      function setActiveChip(i) {
        if (i === activeIdx) return;
        activeIdx = i;
        indexLinks.forEach(function (a, j) { a.classList.toggle('active', j === i); });
        if (i >= 0 && cards[i]) {
          var pos = cards[i].getAttribute('data-position');
          rbLabel.innerHTML = '<b>' + pad2(i + 1) + '</b> · ' + pos;
          rbApply.setAttribute('data-position', pos);
        }
      }

      /* per-card motion state (current values lerp toward targets) */
      var states = cards.map(function () {
        return { s: 1, b: 1, ts: 1, tb: 1, tx: 0, ty: 0, ttx: 0, tty: 0, ls: 0, lb: 0, ltx: 99, lty: 99 };
      });

      function revealCard(card) {
        if (card._rv) return;
        card._rv = true;
        if (REDUCED) { card.classList.remove('reveal'); card.classList.add('revealed'); return; }
        card.classList.add('in');
        card.addEventListener('animationend', function h(e) {
          if (e.animationName !== 'cardIn') return;
          card.classList.remove('reveal', 'in');
          card.classList.add('revealed');
          card.removeEventListener('animationend', h);
        });
      }

      var navScrolled = false, lastBarW = -1, frameQueued = false;
      var motionActive = false;
      function frame() {
        frameQueued = false;
        motionActive = false;
        var vh = window.innerHeight;
        var y = window.scrollY || document.documentElement.scrollTop;
        var sc = y > 10;
        var de = document.documentElement;
        var max = de.scrollHeight - de.clientHeight;
        var w = max > 0 ? (y / max) * 100 : 0;
        var showToTop = y > 400;
        var cardRects = cards.map(function (card) { return card.getBoundingClientRect(); });
        var rolesRect = rolesSec.getBoundingClientRect();
        var spy = -1;
        for (var i = 0; i < cards.length; i++) {
          var card = cards[i], st = states[i];
          var rect = cardRects[i];

          /* reveal guarantee (also covers deep-link landings) */
          if (rect.top < vh * 0.92 && !card._rv) revealCard(card);

          /* Keep the overlap scale animation without dimming card text. */
          if (i < cards.length - 1) {
            var nextTop = cardRects[i + 1].top;
            var p = (vh - nextTop) / vh;
            p = p < 0 ? 0 : p > 1 ? 1 : p;
            st.ts = 1 - p * 0.025;
            st.tb = 1;
          } else { st.ts = 1; st.tb = 1; }

          /* scrollspy: card whose top has passed mid-viewport is "current" */
          if (rect.top < vh * 0.45) spy = i;

          /* lerp toward targets   smooth even on scroll jumps */
          st.s += (st.ts - st.s) * 0.19;
          st.b += (st.tb - st.b) * 0.19;
          st.tx += (st.ttx - st.tx) * 0.12;
          st.ty += (st.tty - st.ty) * 0.12;
          if (Math.abs(st.ts - st.s) > 0.0004 || Math.abs(st.tb - st.b) > 0.0004 ||
            Math.abs(st.ttx - st.tx) > 0.01 || Math.abs(st.tty - st.ty) > 0.01) {
            motionActive = true;
          }

          /* only touch the DOM when something actually changed */
          if (card.classList.contains('revealed') &&
            (Math.abs(st.s - st.ls) > 0.0004 || Math.abs(st.b - st.lb) > 0.0004 ||
              Math.abs(st.tx - st.ltx) > 0.01 || Math.abs(st.ty - st.lty) > 0.01)) {
            st.ls = st.s; st.lb = st.b; st.ltx = st.tx; st.lty = st.ty;
            card.style.setProperty('--s', st.s.toFixed(4));
            card.style.setProperty('--b', st.b.toFixed(4));
            card.style.setProperty('--tx', st.tx.toFixed(3) + 'deg');
            card.style.setProperty('--ty', st.ty.toFixed(3) + 'deg');
          }
        }

        /* Apply visual changes only after all layout measurements are complete. */
        if (sc !== navScrolled) { navScrolled = sc; nav.classList.toggle('scrolled', sc); }
        if (Math.abs(w - lastBarW) > 0.05) { lastBarW = w; bar.style.width = w + '%'; }
        if (toTop && showToTop !== toTopVisible) {
          toTopVisible = showToTop;
          toTop.classList.toggle('show', showToTop);
        }
        setActiveChip(spy);

        /* floating quick-apply bar while inside the roles stack */
        var roleBarVisible = spy >= 0 && rolesRect.top < vh * 0.55 && rolesRect.bottom > vh * 0.62;
        if (roleBarVisible !== roleBar.classList.contains('show')) roleBar.classList.toggle('show', roleBarVisible);
        if (motionActive) requestFrameUpdate();
      }
      function requestFrameUpdate() {
        if (frameQueued) return;
        frameQueued = true;
        requestAnimationFrame(frame);
      }
      window.addEventListener('scroll', requestFrameUpdate, { passive: true });
      window.addEventListener('resize', requestFrameUpdate, { passive: true });
      requestFrameUpdate();

      function scrollToAnchorTarget(target) {
        target.scrollIntoView({
          block: 'start',
          behavior: REDUCED ? 'auto' : 'smooth'
        });
      }

      /* smooth-scroll for in-page anchors */
      document.addEventListener('click', function (e) {
        var a = e.target.closest('a[href^="#"]');
        if (!a) return;
        var hash = a.getAttribute('href');
        var t = document.getElementById(decodeURIComponent(hash.slice(1)));
        if (t) {
          e.preventDefault();
          if (location.hash !== hash) history.pushState(null, '', hash);
          scrollToAnchorTarget(t);
        }
      });

      /* deep-link: refresh layout after fonts/images so anchor lands exactly */
      window.addEventListener('load', function () {
        if (location.hash) {
          var t = document.getElementById(decodeURIComponent(location.hash.slice(1)));
          if (t) setTimeout(function () {
            scrollToAnchorTarget(t);
          }, 120);
        }
      });
      var yr = document.getElementById('year');
      if (yr) yr.textContent = new Date().getFullYear();

      /* ------------ 5a. Pointer interactions: 3D tilt, spotlight, hero parallax ------------ */
      if (FINE_POINTER && !REDUCED) {
        /* job cards: subtle 3D tilt (only when fully revealed & not covered) */
        cards.forEach(function (card, i) {
          card.addEventListener('mousemove', function (e) {
            var st = states[i];
            if (!card.classList.contains('revealed') || st.ts < 0.995) { st.ttx = 0; st.tty = 0; return; }
            var r = card.getBoundingClientRect();
            var px = (e.clientX - r.left) / r.width - 0.5;
            var py = (e.clientY - r.top) / r.height - 0.5;
            st.tty = px * 2.4;
            st.ttx = -py * 2;
          });
          card.addEventListener('mouseleave', function () {
            var st = states[i]; st.ttx = 0; st.tty = 0;
          });
        });

        /* perk cards: spotlight + 3D tilt */
        document.querySelectorAll('.perk').forEach(function (p) {
          p.addEventListener('mousemove', function (e) {
            var r = p.getBoundingClientRect();
            var px = (e.clientX - r.left) / r.width - 0.5;
            var py = (e.clientY - r.top) / r.height - 0.5;
            p.style.setProperty('--mx', (e.clientX - r.left) + 'px');
            p.style.setProperty('--my', (e.clientY - r.top) + 'px');
            p.style.setProperty('--tx', (-py * 5.5) + 'deg');
            p.style.setProperty('--ty', (px * 5.5) + 'deg');
          });
          p.addEventListener('mouseleave', function () {
            p.style.setProperty('--tx', '0deg');
            p.style.setProperty('--ty', '0deg');
          });
        });

        /* spotlight coords for job cards + hero glow parallax */
        document.addEventListener('mousemove', function (e) {
          var card = e.target.closest('.job-card');
          if (card) {
            var r = card.getBoundingClientRect();
            card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
            card.style.setProperty('--my', (e.clientY - r.top) + 'px');
          }
          var hero = document.getElementById('hero');
          hero.style.setProperty('--px', ((e.clientX / window.innerWidth - 0.5) * 34) + 'px');
          hero.style.setProperty('--py', ((e.clientY / window.innerHeight - 0.5) * 22) + 'px');
        }, { passive: true });

        /* ------------ 5e. Custom cursor: red dot + trailing ring ------------ */
        var dot = document.createElement('div'); dot.className = 'cursor-dot';
        var ring = document.createElement('div'); ring.className = 'cursor-ring';
        document.body.appendChild(dot);
        document.body.appendChild(ring);
        var cx = -100, cy = -100, rx = -100, ry = -100, curVisible = false, cursorFrame = 0;
        document.addEventListener('mousemove', function (e) {
          cx = e.clientX; cy = e.clientY;
          if (!curVisible) { curVisible = true; dot.style.opacity = '1'; ring.style.opacity = '1'; rx = cx; ry = cy; }
          if (!cursorFrame) cursorFrame = requestAnimationFrame(cursorLoop);
        }, { passive: true });
        document.addEventListener('mouseleave', function () {
          curVisible = false; dot.style.opacity = '0'; ring.style.opacity = '0';
        });
        function cursorLoop() {
          rx += (cx - rx) * 0.18;
          ry += (cy - ry) * 0.18;
          dot.style.transform = 'translate(' + (cx - 3) + 'px,' + (cy - 3) + 'px)';
          ring.style.transform = 'translate(' + rx + 'px,' + ry + 'px) translate(-50%,-50%)';
          cursorFrame = 0;
          if (Math.abs(cx - rx) > 0.5 || Math.abs(cy - ry) > 0.5) cursorFrame = requestAnimationFrame(cursorLoop);
        }
        document.addEventListener('mouseover', function (e) {
          var hot = e.target.closest('a, button, input, select, .drop-zone, .jc-tags span, .chip');
          ring.classList.toggle('big', !!hot);
          dot.classList.toggle('dim', !!hot);
        });
      }

      /* ------------ 5f. Click ripples on all buttons ------------ */
      document.addEventListener('pointerdown', function (e) {
        var b = e.target.closest('.btn');
        if (!b || REDUCED) return;
        var r = b.getBoundingClientRect();
        var size = Math.max(r.width, r.height) * 1.1;
        var rip = document.createElement('span');
        rip.className = 'ripple';
        rip.style.width = rip.style.height = size + 'px';
        rip.style.left = (e.clientX - r.left - size / 2) + 'px';
        rip.style.top = (e.clientY - r.top - size / 2) + 'px';
        b.appendChild(rip);
        rip.addEventListener('animationend', function () { rip.remove(); });
      });

      /* ------------ 5g. Confetti burst (form success) ------------ */
      function confetti(container) {
        if (REDUCED || !container) return;
        var colors = ['#E5162B', '#F0A30A', '#19B36B', '#2E7DF6', '#ffffff'];
        for (var i = 0; i < 30; i++) {
          var c = document.createElement('span');
          c.className = 'confetti';
          c.style.left = (4 + Math.random() * 92) + '%';
          c.style.background = colors[i % colors.length];
          c.style.setProperty('--d', (1.6 + Math.random()) + 's');
          c.style.setProperty('--dl', (Math.random() * 0.5) + 's');
          c.style.width = (5 + Math.random() * 6) + 'px';
          c.style.height = (8 + Math.random() * 8) + 'px';
          container.appendChild(c);
        }
        setTimeout(function () {
          var cs = container.querySelectorAll('.confetti');
          for (var j = 0; j < cs.length; j++) cs[j].remove();
        }, 3400);
      }

      /* ------------ 5b. Magnetic primary buttons ------------ */
      if (FINE_POINTER) {
        document.querySelectorAll('.btn-primary').forEach(function (btn) {
          btn.addEventListener('mousemove', function (e) {
            var r = btn.getBoundingClientRect();
            var dx = (e.clientX - (r.left + r.width / 2)) / r.width;
            var dy = (e.clientY - (r.top + r.height / 2)) / r.height;
            btn.style.transform = 'translate(' + dx * 10 + 'px,' + dy * 7 + 'px)';
          });
          btn.addEventListener('mouseleave', function () {
            btn.style.transition = 'transform .45s cubic-bezier(.34,1.56,.64,1)';
            btn.style.transform = '';
            setTimeout(function () { btn.style.transition = ''; }, 460);
          });
        });
      }

      /* ------------ 5c. Count-up hero stats ------------ */
      function countUp(el) {
        var target = parseInt(el.getAttribute('data-count'), 10);
        var suffix = el.getAttribute('data-suffix') || '';
        var pad = parseInt(el.getAttribute('data-pad') || '0', 10);
        var dur = 1300, t0 = null;
        function step(t) {
          if (!t0) t0 = t;
          var p = Math.min(1, (t - t0) / dur);
          p = 1 - Math.pow(1 - p, 3); /* easeOutCubic */
          var v = Math.round(target * p);
          var s = String(v);
          while (s.length < pad) s = '0' + s;
          el.textContent = s + suffix;
          if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
      }
      var stats = document.querySelectorAll('.hstat .n');
      if ('IntersectionObserver' in window) {
        var so = new IntersectionObserver(function (entries) {
          entries.forEach(function (en) {
            if (en.isIntersecting) { countUp(en.target); so.unobserve(en.target); }
          });
        }, { threshold: 0.4 });
        stats.forEach(function (s) { so.observe(s); });
      } else { stats.forEach(countUp); }

      /* ------------ 5d. Copy-email toast ------------ */
      var toast = document.getElementById('toast');
      var toastMsg = document.getElementById('toastMsg');
      var toastT = null;
      function showToast(msg) {
        toastMsg.textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastT);
        toastT = setTimeout(function () { toast.classList.remove('show'); }, 2200);
      }
      document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-copy-mail]');
        if (!el) return;
        e.preventDefault();
        var mail = HR_MAIL;
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(mail).then(function () {
            showToast(mail + '   copied to clipboard');
          }, function () { location.href = el.href; });
        } else { location.href = el.href; }
      });

      /* =========================================================
         4. LAZY-LOADED APPLY POPUP
         The modal markup is built ONLY on first click of Apply Now.
         ========================================================= */
      var root = document.getElementById('applyRoot');
      var built = false, overlay, form, lastFocus;

      function buildModal() {
        var opts = POSITIONS.map(function (p) { return '<option value="' + p + '">' + p + '</option>'; }).join('');
        root.innerHTML =
          '<div class="apply-overlay" id="applyOverlay" role="dialog" aria-modal="true" aria-labelledby="amTitle">' +
          '<div class="apply-modal">' +
          '<div class="am-spectrum"></div>' +
          '<div class="am-head">' +
          '<div><h3 id="amTitle">Apply for <span id="amRoleLabel">Position</span></h3>' +
          '<p>Fill in the form   your application goes straight to <b>' + HR_MAIL + '</b>.</p></div>' +
          '<button class="am-close" id="amClose" aria-label="Close">✕</button>' +
          '</div>' +
          '<form class="am-body" id="applyForm" novalidate enctype="multipart/form-data">' +
          '<input type="text" name="_hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0" aria-hidden="true" />' +
          '<div class="f-grid">' +
          '<div class="f-field"><label for="fName">Full Name <b class="req">*</b></label>' +
          '<input id="fName" name="fullname" type="text" placeholder="e.g. Rahul Patel" required />' +
          '<span class="err">Please enter your full name</span></div>' +
          '<div class="f-field"><label for="fPhone">Phone Number <b class="req">*</b></label>' +
          '<input id="fPhone" name="phone" type="tel" placeholder="+91 98765 43210" required />' +
          '<span class="err">Enter a valid phone number</span></div>' +
          '<div class="f-field"><label for="fEmail">Email <b class="req">*</b></label>' +
          '<input id="fEmail" name="email" type="email" placeholder="you@example.com" required />' +
          '<span class="err">Enter a valid email address</span></div>' +
          '<div class="f-field"><label for="fPosition">Position <b class="req">*</b></label>' +
          '<select id="fPosition" name="position" required><option value="" disabled selected>Select position</option>' + opts + '</select>' +
          '<span class="err">Please select a position</span></div>' +
          '<div class="f-field full"><label for="fSubject">Subject <b class="req">*</b></label>' +
          '<input id="fSubject" name="subject" type="text" placeholder="Application subject" required />' +
          '<span class="err">Please add a subject</span></div>' +
          '<div class="f-field full"><label>Attachment (Resume / CV) <b class="req">*</b></label>' +
          '<div class="drop-zone" id="dropZone" role="button" tabindex="0" aria-label="Upload resume">' +
          '<div class="dz-ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg></div>' +
          '<p>Drag &amp; drop your resume, or <b>browse files</b></p>' +
          '<small>PDF, DOC, DOCX, RTF, ZIP · Max ' + MAX_FILE_MB + ' MB</small>' +
          '</div>' +
          '<input type="file" id="resumeInput" name="resume" accept=".pdf,.doc,.docx,.rtf,.odt,.zip" />' +
          '<div class="dz-file" id="dzFile">' +
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>' +
          '<div class="fi"><span class="fn" id="dzName"></span><span class="fs" id="dzSize"></span></div>' +
          '<button type="button" id="dzRemove" aria-label="Remove file">✕</button>' +
          '</div>' +
          '<span id="fileErr" style="display:none;font-size:.75rem;color:var(--red)"></span>' +
          '</div>' +
          '</div>' +
          '<div class="am-foot">' +
          '<span class="am-note">On submit, your application is emailed to <b style="color:#fff;font-weight:600">' + HR_MAIL + '</b></span>' +
          '<button type="submit" class="btn btn-primary btn-submit" id="submitBtn">' +
          '<span class="spin"></span><span class="lbl-txt">Submit Application</span> <span class="arr">→</span>' +
          '</button>' +
          '</div>' +
          '<div class="form-status" id="formStatus"></div>' +
          '</form>' +
          '<div class="am-success" id="amSuccess">' +
          '<div class="check"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></div>' +
          '<h3>Application Sent!</h3>' +
          '<p>Thank you for your interest in joining Chameleo GFX Studio. We have successfully received your application. Our team will review your details and get back to you within 2–3 working days.</p>' +
          '<button class="btn btn-ghost" id="successClose">Close</button>' +
          '</div>' +
          '</div>' +
          '</div>';

        overlay = document.getElementById('applyOverlay');
        form = document.getElementById('applyForm');

        document.getElementById('amClose').addEventListener('click', closeModal);
        document.getElementById('successClose').addEventListener('click', closeModal);
        overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) closeModal(); });
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal();
        });

        /* file upload */
        var dz = document.getElementById('dropZone'),
          fi = document.getElementById('resumeInput'),
          fileRow = document.getElementById('dzFile');
        dz.addEventListener('click', function () { fi.click(); });
        dz.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fi.click(); } });
        ['dragenter', 'dragover'].forEach(function (ev) {
          dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.add('drag'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
          dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('drag'); });
        });
        dz.addEventListener('drop', function (e) {
          if (e.dataTransfer.files.length) { fi.files = e.dataTransfer.files; showFile(); }
        });
        fi.addEventListener('change', showFile);
        document.getElementById('dzRemove').addEventListener('click', function () {
          fi.value = ''; fileRow.classList.remove('show');
        });
        function showFile() {
          hideFileErr();
          if (fi.files.length) {
            document.getElementById('dzName').textContent = fi.files[0].name;
            document.getElementById('dzSize').textContent = (fi.files[0].size / 1024 / 1024).toFixed(2) + ' MB';
            fileRow.classList.add('show');
          } else { fileRow.classList.remove('show'); }
        }

        /* live subject autofill: "Application for {Position}   {Name}" */
        var posSel = document.getElementById('fPosition'),
          subj = document.getElementById('fSubject'),
          nameI = document.getElementById('fName');
        function syncSubject() {
          var p = posSel.value, n = nameI.value.trim();
          subj.value = 'Application for ' + (p || '{Position}') + (n ? '   ' + n : '');
        }
        posSel.addEventListener('change', function () {
          document.getElementById('amRoleLabel').textContent = posSel.value;
          syncSubject();
        });
        nameI.addEventListener('input', syncSubject);

        /* submit */
        form.addEventListener('submit', onSubmit);
        built = true;
      }
      function hideFileErr() { var e = document.getElementById('fileErr'); if (e) { e.style.display = 'none'; } }

      function openModal(position) {
        if (!built) buildModal();                    // ← lazy load
        var sel = document.getElementById('fPosition');
        if (position && POSITIONS.indexOf(position) > -1) {
          sel.value = position;
          sel.dispatchEvent(new Event('change'));
        }
        document.getElementById('applyForm').style.display = '';
        document.getElementById('amSuccess').classList.remove('show');
        lastFocus = document.activeElement;
        document.body.classList.add('no-scroll');
        requestAnimationFrame(function () { overlay.classList.add('open'); });
        setTimeout(function () { document.getElementById('fName').focus(); }, 380);
      }

      function closeModal() {
        overlay.classList.remove('open');
        document.body.classList.remove('no-scroll');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
      }

      /* open on any Apply Now click */
      document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-apply]');
        if (btn) { e.preventDefault(); openModal(btn.getAttribute('data-position')); }
      });

      /* ------------ validation + submit ------------ */
      function setInvalid(id, on) {
        var el = document.getElementById(id);
        if (el) el.closest('.f-field').classList.toggle('invalid', !!on);
      }

      function onSubmit(e) {
        e.preventDefault();
        var name = document.getElementById('fName').value.trim(),
          phone = document.getElementById('fPhone').value.trim(),
          email = document.getElementById('fEmail').value.trim(),
          pos = document.getElementById('fPosition').value,
          subj = document.getElementById('fSubject').value.trim(),
          file = document.getElementById('resumeInput').files[0],
          ok = true;

        var nameOk = name.length >= 2;
        setInvalid('fName', !nameOk); ok = ok && nameOk;

        var phoneOk = /^[+]?[\d\s\-()]{8,16}$/.test(phone);
        setInvalid('fPhone', !phoneOk); ok = ok && phoneOk;

        var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
        setInvalid('fEmail', !emailOk); ok = ok && emailOk;

        setInvalid('fPosition', !pos); ok = ok && !!pos;
        setInvalid('fSubject', subj.length < 4); ok = ok && subj.length >= 4;

        var fe = document.getElementById('fileErr'), dz = document.getElementById('dropZone');
        dz.classList.remove('invalid'); fe.style.display = 'none';
        if (!file) {
          fe.textContent = 'Please attach your resume / CV'; fe.style.display = 'block';
          dz.classList.add('invalid'); ok = false;
        } else {
          var ext = file.name.split('.').pop().toLowerCase();
          if (OK_EXT.indexOf(ext) === -1) {
            fe.textContent = 'File type not allowed. Use ' + OK_EXT.join(', ');
            fe.style.display = 'block'; dz.classList.add('invalid'); ok = false;
          } else if (file.size > MAX_FILE_MB * 1024 * 1024) {
            fe.textContent = 'File too large (max ' + MAX_FILE_MB + ' MB)';
            fe.style.display = 'block'; dz.classList.add('invalid'); ok = false;
          }
        }
        if (!ok) return;

        var btn = document.getElementById('submitBtn'),
          status = document.getElementById('formStatus');
        btn.classList.add('loading'); status.classList.remove('show');

        /* honeypot → silently pretend success (spam bot) */
        if (form._hp && form._hp.value) { finishSuccess(); return; }

        var fd = new FormData(form);

        function finishSuccess() {
          btn.classList.remove('loading');
          form.style.display = 'none';
          var s = document.getElementById('amSuccess');
          s.classList.add('show');
          confetti(s);
        }
        function finishFail(msg) {
          btn.classList.remove('loading');
          status.innerHTML = msg +
            '<br>You can also mail us directly: <a href="mailto:' + HR_MAIL +
            '?subject=' + encodeURIComponent(subj) +
            '&body=' + encodeURIComponent('Name: ' + name + '\nPhone: ' + phone + '\nEmail: ' + email +
              '\nPosition: ' + pos + '\n\n(Please attach your resume to this mail)') + '">Open in Mail App →</a>';
          status.classList.add('show');
        }

        if (DEMO_MODE) { setTimeout(finishSuccess, 1200); return; }   // preview/demo only

        fetch(MAIL_ENDPOINT, { method: 'POST', body: fd })
          .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
          .then(function (j) {
            if (j && j.ok) finishSuccess();
            else finishFail('Submission failed' + (j && j.msg ? ': ' + j.msg : '.'));
          })
          .catch(function () { finishFail('Could not connect to the server.'); });
      }
    })();