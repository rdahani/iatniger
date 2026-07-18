/**
 * IAT Niger — Interactions front-end
 * Navbar · Mega menu · Dark mode · Reveal · Compteurs · Accordion ·
 * Tabs · Carousel · Lightbox · Validation de formulaires
 */
(function () {
  'use strict';

  /* ---------- Navbar : ombre au scroll ---------- */
  var navbar = document.getElementById('navbar');
  if (navbar) {
    var onScroll = function () {
      navbar.classList.toggle('is-scrolled', window.scrollY > 8);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- Menu mobile ---------- */
  var burger = document.getElementById('nav-burger');
  var navMain = document.getElementById('nav-main');
  if (burger && navMain) {
    burger.addEventListener('click', function () {
      var open = navMain.classList.toggle('open');
      burger.setAttribute('aria-expanded', String(open));
      burger.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
      document.body.style.overflow = open ? 'hidden' : '';
    });
  }

  /* ---------- Dropdowns / mega menu (clic + clavier) ---------- */
  document.querySelectorAll('.has-dropdown > button').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var li = btn.parentElement;
      var wasOpen = li.classList.contains('open');
      document.querySelectorAll('.has-dropdown.open').forEach(function (o) {
        o.classList.remove('open');
        o.querySelector('button').setAttribute('aria-expanded', 'false');
      });
      if (!wasOpen) {
        li.classList.add('open');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });
  document.addEventListener('click', function () {
    document.querySelectorAll('.has-dropdown.open').forEach(function (o) {
      o.classList.remove('open');
      o.querySelector('button').setAttribute('aria-expanded', 'false');
    });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.has-dropdown.open').forEach(function (o) {
        o.classList.remove('open');
        o.querySelector('button').setAttribute('aria-expanded', 'false');
      });
    }
  });

  /* ---------- Thème clair / sombre ---------- */
  var themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', function () {
      var root = document.documentElement;
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      root.style.colorScheme = next === 'dark' ? 'dark' : 'light';
      try {
        localStorage.setItem('iat-theme-pref', next);
        localStorage.removeItem('iat-theme');
      } catch (e) {}
      var meta = document.querySelector('meta[name="theme-color"]');
      if (meta) {
        meta.setAttribute('content', next === 'dark' ? '#0b1020' : '#f7f8fc');
      }
    });
  }

  /* ---------- Reveal au scroll ---------- */
  var reveals = document.querySelectorAll('.reveal');
  if (reveals.length && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    reveals.forEach(function (el) { io.observe(el); });
  } else {
    reveals.forEach(function (el) { el.classList.add('visible'); });
  }

  /* ---------- Compteurs animés ---------- */
  var counters = document.querySelectorAll('[data-count]');
  if (counters.length && 'IntersectionObserver' in window) {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var ioCount = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) { return; }
        var el = entry.target;
        ioCount.unobserve(el);
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        if (reduce) { el.textContent = target.toLocaleString('fr-FR'); return; }
        var start = null;
        var duration = 1600;
        var step = function (ts) {
          if (!start) { start = ts; }
          var p = Math.min((ts - start) / duration, 1);
          var eased = 1 - Math.pow(1 - p, 3);
          el.textContent = Math.round(target * eased).toLocaleString('fr-FR');
          if (p < 1) { requestAnimationFrame(step); }
        };
        requestAnimationFrame(step);
      });
    }, { threshold: 0.4 });
    counters.forEach(function (el) { ioCount.observe(el); });
  }

  /* ---------- Accordions ---------- */
  document.querySelectorAll('.accordion-trigger').forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      var item = trigger.closest('.accordion-item');
      var open = item.classList.toggle('open');
      trigger.setAttribute('aria-expanded', String(open));
    });
  });

  /* ---------- Tabs ---------- */
  document.querySelectorAll('[role="tablist"]').forEach(function (tablist) {
    var tabs = Array.prototype.slice.call(tablist.querySelectorAll('[role="tab"]'));
    var activate = function (tab) {
      tabs.forEach(function (t) {
        var selected = t === tab;
        t.setAttribute('aria-selected', String(selected));
        t.tabIndex = selected ? 0 : -1;
        var panel = document.getElementById(t.getAttribute('aria-controls'));
        if (panel) { panel.hidden = !selected; }
      });
      tab.focus();
    };
    tabs.forEach(function (tab, i) {
      tab.addEventListener('click', function () { activate(tab); });
      tab.addEventListener('keydown', function (e) {
        var next = null;
        if (e.key === 'ArrowRight') { next = tabs[(i + 1) % tabs.length]; }
        if (e.key === 'ArrowLeft') { next = tabs[(i - 1 + tabs.length) % tabs.length]; }
        if (next) { e.preventDefault(); activate(next); }
      });
    });
  });

  /* ---------- Diaporama automatique du hero ---------- */
  document.querySelectorAll('[data-hero-slider]').forEach(function (slider) {
    var slides = slider.querySelectorAll('.hero-img');
    var dots = slider.querySelectorAll('.hero-slider-dots button');
    if (slides.length < 2) { return; }
    var index = 0;
    var delay = 4500;
    var timer = null;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var goTo = function (i) {
      slides[index].classList.remove('is-active');
      if (dots[index]) { dots[index].classList.remove('is-active'); }
      index = (i + slides.length) % slides.length;
      slides[index].classList.add('is-active');
      if (dots[index]) { dots[index].classList.add('is-active'); }
    };
    var start = function () {
      if (reduce || timer) { return; }
      timer = setInterval(function () { goTo(index + 1); }, delay);
    };
    var stop = function () {
      clearInterval(timer);
      timer = null;
    };

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () {
        stop();
        goTo(i);
        start();
      });
    });

    /* Pause au survol, au focus et quand l'onglet est masqué */
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', start);
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) { stop(); } else { start(); }
    });

    start();
  });

  /* ---------- Carousel témoignages ---------- */
  document.querySelectorAll('[data-carousel]').forEach(function (wrap) {
    var track = wrap.querySelector('.testimonial-track');
    var prev = wrap.querySelector('[data-prev]');
    var next = wrap.querySelector('[data-next]');
    if (!track) { return; }
    var scrollBy = function (dir) {
      var card = track.firstElementChild;
      var w = card ? card.getBoundingClientRect().width + 24 : 400;
      track.scrollBy({ left: dir * w, behavior: 'smooth' });
    };
    if (prev) { prev.addEventListener('click', function () { scrollBy(-1); }); }
    if (next) { next.addEventListener('click', function () { scrollBy(1); }); }
  });

  /* ---------- Lightbox galerie ---------- */
  var lightbox = document.getElementById('lightbox');
  if (lightbox) {
    var lbImg = lightbox.querySelector('img');
    document.querySelectorAll('.masonry figure, .home-gallery figure').forEach(function (fig) {
      fig.addEventListener('click', function () {
        var img = fig.querySelector('img');
        lbImg.src = img.src;
        lbImg.alt = img.alt;
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
      });
    });
    var closeLb = function () {
      lightbox.classList.remove('open');
      document.body.style.overflow = '';
    };
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox || e.target.closest('.lightbox-close')) { closeLb(); }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { closeLb(); }
    });
  }

  /* ---------- Validation de formulaires ---------- */
  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    var showError = function (field, show) {
      var wrap = field.closest('.form-field');
      if (wrap) { wrap.classList.toggle('invalid', show); }
    };
    form.addEventListener('submit', function (e) {
      var valid = true;
      form.querySelectorAll('[required]').forEach(function (field) {
        var ok = field.checkValidity();
        showError(field, !ok);
        if (!ok) { valid = false; }
      });
      if (!valid) {
        e.preventDefault();
        var firstInvalid = form.querySelector('.form-field.invalid input, .form-field.invalid select, .form-field.invalid textarea');
        if (firstInvalid) { firstInvalid.focus(); }
      }
    });
    form.querySelectorAll('[required]').forEach(function (field) {
      field.addEventListener('input', function () {
        if (field.checkValidity()) { showError(field, false); }
      });
    });
  });
})();
