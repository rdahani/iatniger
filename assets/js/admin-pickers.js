/**
 * Sélecteurs admin : médiathèque (parcourir + aperçu) et bibliothèque d'icônes.
 */
(function () {
  'use strict';
  if (!window.IAT_ADMIN) return;

  var cfg = window.IAT_ADMIN;
  var modal = null;

  function ensureModal() {
    if (modal) return modal;
    modal = document.createElement('div');
    modal.className = 'admin-picker-modal';
    modal.hidden = true;
    modal.innerHTML =
      '<div class="admin-picker-backdrop" data-close></div>' +
      '<div class="admin-picker-dialog" role="dialog" aria-modal="true">' +
        '<div class="admin-picker-head">' +
          '<h2 class="h3" data-title>Bibliothèque</h2>' +
          '<button type="button" class="icon-btn" data-close aria-label="Fermer">' +
            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
          '</button>' +
        '</div>' +
        '<div class="admin-picker-toolbar">' +
          '<input type="search" data-search placeholder="Rechercher…" autocomplete="off">' +
        '</div>' +
        '<div class="admin-picker-body" data-body></div>' +
      '</div>';
    document.body.appendChild(modal);
    modal.addEventListener('click', function (e) {
      if (e.target.closest('[data-close]')) closeModal();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hidden) closeModal();
    });
    return modal;
  }

  function openModal(title) {
    var m = ensureModal();
    m.querySelector('[data-title]').textContent = title;
    m.querySelector('[data-search]').value = '';
    m.hidden = false;
    document.body.style.overflow = 'hidden';
    m.querySelector('[data-search]').focus();
    return m;
  }

  function closeModal() {
    if (!modal) return;
    modal.hidden = true;
    document.body.style.overflow = '';
  }

  function assetUrl(path, base) {
    if (!path) return '';
    if (/^https?:\/\//i.test(path)) return path;
    if (base === 'img') {
      if (path.indexOf('img/') === 0 || path.indexOf('docs/') === 0 || path.indexOf('uploads/') === 0 && path.indexOf('img/') !== 0) {
        /* chemins déjà relatifs à assets/ pour docs ; pour img base, path est sous img/ */
        if (path.indexOf('docs/') === 0) return cfg.assetBase + path;
      }
      return cfg.assetBase + 'img/' + path.replace(/^img\//, '');
    }
    return cfg.assetBase + path.replace(/^\//, '');
  }

  function isImagePath(p) {
    return /\.(jpe?g|png|gif|webp)(\?|$)/i.test(p || '');
  }

  function setMediaPreview(field, path) {
    var preview = field.querySelector('[data-preview]');
    var base = field.getAttribute('data-base') || 'img';
    var url = assetUrl(path, base);
    if (path && isImagePath(path)) {
      preview.innerHTML = '<img src="' + url + '" alt="" width="96" height="72">';
      preview.setAttribute('aria-hidden', 'false');
    } else if (path) {
      preview.innerHTML = '<span class="admin-media-placeholder">Fichier</span>';
      preview.setAttribute('aria-hidden', 'false');
    } else {
      preview.innerHTML = '<span class="admin-media-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></span>';
      preview.setAttribute('aria-hidden', 'true');
    }
  }

  function setIconPreview(field, name) {
    var preview = field.querySelector('[data-icon-preview]');
    fetch(cfg.iconsApi + '?q=' + encodeURIComponent(name))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var found = (data.icons || []).find(function (i) { return i.name === name; });
        preview.innerHTML = found ? found.svg : '';
      })
      .catch(function () { preview.textContent = name; });
  }

  function openMediaBrowser(field) {
    var base = field.getAttribute('data-base') || 'img';
    var accept = field.getAttribute('data-accept') || 'image';
    var input = field.querySelector('[data-media-input]');
    var m = openModal('Choisir un fichier');
    var body = m.querySelector('[data-body]');
    var search = m.querySelector('[data-search]');
    body.innerHTML = '<p class="caption">Chargement…</p>';

    function load(q) {
      var url = cfg.mediaApi + '?accept=' + encodeURIComponent(accept === 'image' ? 'image' : 'all') + '&q=' + encodeURIComponent(q || '');
      fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var files = data.files || [];
          if (!files.length) {
            body.innerHTML = '<p class="caption">Aucun fichier trouvé.</p>';
            return;
          }
          body.innerHTML = '<div class="admin-media-grid"></div>';
          var grid = body.querySelector('.admin-media-grid');
          files.forEach(function (f) {
            var value = f.path;
            /* Pour base img : stocker sans préfixe img/ */
            if (base === 'img' && value.indexOf('img/') === 0) {
              value = value.slice(4);
            }
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'admin-media-tile';
            btn.title = f.path;
            if (isImagePath(f.path)) {
              btn.innerHTML = '<img src="' + cfg.assetBase + f.path + '" alt="" loading="lazy"><span>' + f.name + '</span>';
            } else {
              btn.innerHTML = '<span class="admin-media-file">' + f.ext.toUpperCase() + '</span><span>' + f.name + '</span>';
            }
            btn.addEventListener('click', function () {
              input.value = value;
              setMediaPreview(field, value);
              closeModal();
            });
            grid.appendChild(btn);
          });
        })
        .catch(function () {
          body.innerHTML = '<p class="caption">Erreur de chargement.</p>';
        });
    }

    load('');
    search.oninput = function () {
      clearTimeout(search._t);
      search._t = setTimeout(function () { load(search.value); }, 250);
    };
  }

  function openIconBrowser(field) {
    var input = field.querySelector('[data-icon-input]');
    var m = openModal('Bibliothèque d\'icônes');
    var body = m.querySelector('[data-body]');
    var search = m.querySelector('[data-search]');
    body.innerHTML = '<p class="caption">Chargement…</p>';

    function load(q) {
      fetch(cfg.iconsApi + '?q=' + encodeURIComponent(q || ''))
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var icons = data.icons || [];
          if (!icons.length) {
            body.innerHTML = '<p class="caption">Aucune icône.</p>';
            return;
          }
          body.innerHTML = '<div class="admin-icon-grid"></div>';
          var grid = body.querySelector('.admin-icon-grid');
          icons.forEach(function (ic) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'admin-icon-tile' + (ic.name === input.value ? ' is-selected' : '');
            btn.title = ic.name;
            btn.innerHTML = ic.svg + '<span>' + ic.name + '</span>';
            btn.addEventListener('click', function () {
              input.value = ic.name;
              field.querySelector('[data-icon-preview]').innerHTML = ic.svg;
              closeModal();
            });
            grid.appendChild(btn);
          });
        });
    }

    load('');
    search.oninput = function () {
      clearTimeout(search._t);
      search._t = setTimeout(function () { load(search.value); }, 200);
    };
  }

  function uploadMedia(field, file) {
    var base = field.getAttribute('data-base') || 'img';
    var input = field.querySelector('[data-media-input]');
    var fd = new FormData();
    fd.append('csrf', cfg.csrf);
    fd.append('base', base);
    fd.append('fichier', file);
    fetch(cfg.uploadApi, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.ok) {
          alert(data.error || 'Échec du téléversement');
          return;
        }
        input.value = data.path;
        setMediaPreview(field, data.path);
      })
      .catch(function () { alert('Erreur réseau pendant le téléversement'); });
  }

  document.addEventListener('click', function (e) {
    var browse = e.target.closest('[data-media-browse]');
    if (browse) {
      e.preventDefault();
      openMediaBrowser(browse.closest('[data-picker="media"]'));
      return;
    }
    var clear = e.target.closest('[data-media-clear]');
    if (clear) {
      e.preventDefault();
      var field = clear.closest('[data-picker="media"]');
      var input = field.querySelector('[data-media-input]');
      input.value = '';
      setMediaPreview(field, '');
      return;
    }
    var iconBrowse = e.target.closest('[data-icon-browse]');
    if (iconBrowse) {
      e.preventDefault();
      openIconBrowser(iconBrowse.closest('[data-picker="icon"]'));
    }
  });

  document.addEventListener('change', function (e) {
    var up = e.target.closest('[data-media-upload]');
    if (up && up.files && up.files[0]) {
      uploadMedia(up.closest('[data-picker="media"]'), up.files[0]);
      up.value = '';
    }
  });

  document.addEventListener('input', function (e) {
    if (e.target.matches('[data-media-input]')) {
      setMediaPreview(e.target.closest('[data-picker="media"]'), e.target.value);
    }
    if (e.target.matches('[data-icon-input]')) {
      setIconPreview(e.target.closest('[data-picker="icon"]'), e.target.value);
    }
  });
})();
