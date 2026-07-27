/* Simbuktu — public site behaviour. No dependencies, no build step. */
(function () {
  'use strict';

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* --- mobile navigation ------------------------------------------------ */
  var toggle = document.querySelector('[data-nav-toggle]');
  var nav = document.querySelector('[data-nav]');

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.getAttribute('data-open') === 'true';
      nav.setAttribute('data-open', open ? 'false' : 'true');
      toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
      toggle.textContent = open ? 'Menu' : 'Luk';
    });

    nav.addEventListener('click', function (e) {
      if (e.target.tagName === 'A' && nav.getAttribute('data-open') === 'true') {
        nav.setAttribute('data-open', 'false');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.textContent = 'Menu';
      }
    });
  }

  /* --- give every trace its true length so the draw-on lands exactly ---- */
  document.querySelectorAll('.diagram .trace').forEach(function (path) {
    if (typeof path.getTotalLength !== 'function') return;
    var len = Math.ceil(path.getTotalLength());
    if (len > 0) path.style.setProperty('--len', len);
  });

  /* --- reveal on scroll -------------------------------------------------- */
  var targets = Array.prototype.slice.call(
    document.querySelectorAll('[data-reveal], .diagram')
  );

  function activate(el) {
    el.classList.add(el.classList.contains('diagram') ? 'is-live' : 'is-in');
  }

  if (reduced || !('IntersectionObserver' in window)) {
    targets.forEach(activate);
    return;
  }

  // Anything already on screen goes live straight away. Observer callbacks are
  // throttled in background tabs, and above-the-fold content must never be
  // left invisible waiting for one.
  var pending = targets.filter(function (el) {
    var box = el.getBoundingClientRect();
    if (box.top < window.innerHeight * 0.92 && box.bottom > 0) {
      activate(el);
      return false;
    }
    return true;
  });

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      activate(entry.target);
      observer.unobserve(entry.target);
    });
  }, { rootMargin: '0px 0px -12% 0px', threshold: 0.12 });

  pending.forEach(function (el) { observer.observe(el); });

  // Last resort: if the observer never reports (throttled background tab, or
  // the user lands mid-page), reveal whatever has scrolled into view anyway.
  window.addEventListener('scroll', function () {
    if (!pending.length) return;
    pending = pending.filter(function (el) {
      var box = el.getBoundingClientRect();
      if (box.top < window.innerHeight && box.bottom > 0) {
        activate(el);
        observer.unobserve(el);
        return false;
      }
      return true;
    });
  }, { passive: true });
})();
