/**
 * IAT Niger — Navigation admin mobile (drawer)
 */
(function () {
  'use strict';

  var layout = document.querySelector('.admin-layout');
  var toggle = document.getElementById('admin-nav-toggle');
  var sidebar = document.getElementById('admin-sidebar');
  var backdrop = document.getElementById('admin-sidebar-backdrop');
  if (!layout || !toggle || !sidebar) {
    return;
  }

  function isMobile() {
    return window.matchMedia('(max-width: 1024px)').matches;
  }

  function setOpen(open) {
    layout.classList.toggle('nav-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    document.body.style.overflow = open && isMobile() ? 'hidden' : '';
    if (backdrop) {
      backdrop.setAttribute('aria-hidden', String(!open));
    }
  }

  toggle.addEventListener('click', function () {
    setOpen(!layout.classList.contains('nav-open'));
  });

  if (backdrop) {
    backdrop.addEventListener('click', function () {
      setOpen(false);
    });
  }

  sidebar.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      if (isMobile()) {
        setOpen(false);
      }
    });
  });

  window.addEventListener('resize', function () {
    if (!isMobile()) {
      setOpen(false);
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && layout.classList.contains('nav-open')) {
      setOpen(false);
      toggle.focus();
    }
  });
})();
