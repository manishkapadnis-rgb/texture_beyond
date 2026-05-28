/* ============================================================
   Texture & Beyond — Storefront JS
   Premium AJAX cart, toast notifications, slide-cart drawer,
   reveal & GSAP scroll animations.
   ============================================================ */
(function () {
  'use strict';

  const SITE = (window.SITE_URL || '').replace(/\/$/, '');
  const CART_ENDPOINT = SITE + '/cart-action.php';

  /* ---------- Reveal + GSAP ---------- */
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => { if (en.isIntersecting) en.target.classList.add('active'); });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    document.querySelectorAll('.reveal-up').forEach((el) => io.observe(el));
  }
  if (window.gsap && window.ScrollTrigger) {
    gsap.registerPlugin(ScrollTrigger);
    gsap.utils.toArray('.gsap-fade').forEach((el) => {
      gsap.from(el, { opacity: 0, y: 40, duration: 1.2, ease: 'power3.out',
        scrollTrigger: { trigger: el, start: 'top 85%' } });
    });
    gsap.utils.toArray('.gsap-stagger > *').forEach((el, i) => {
      gsap.from(el, { opacity: 0, y: 50, duration: 1, delay: i * 0.08, ease: 'power3.out',
        scrollTrigger: { trigger: el, start: 'top 88%' } });
    });
  }

  /* ---------- Toast ---------- */
  const Toast = (() => {
    function stack() {
      let s = document.getElementById('toast-stack');
      if (!s) {
        s = document.createElement('div');
        s.id = 'toast-stack';
        s.className = 'toast-stack';
        s.setAttribute('aria-live', 'polite');
        document.body.appendChild(s);
      }
      return s;
    }
    function show(message, type) {
      type = type || 'success';
      const el = document.createElement('div');
      el.className = 'toast toast--' + type;
      const icon = type === 'error' ? 'error' : type === 'info' ? 'info' : 'check_circle';
      el.innerHTML =
        '<span class="material-symbols-outlined toast__icon">' + icon + '</span>' +
        '<span class="toast__msg"></span>' +
        '<button type="button" class="toast__close" aria-label="Dismiss">' +
        '<span class="material-symbols-outlined">close</span></button>';
      el.querySelector('.toast__msg').textContent = message;
      stack().appendChild(el);
      requestAnimationFrame(() => el.classList.add('toast--in'));
      const close = () => {
        el.classList.remove('toast--in');
        el.classList.add('toast--out');
        setTimeout(() => el.remove(), 350);
      };
      el.querySelector('.toast__close').addEventListener('click', close);
      setTimeout(close, 3600);
    }
    return { show };
  })();

  /* ---------- Cart Drawer ---------- */
  const Cart = (() => {
    const drawer = document.getElementById('mini-cart-drawer');
    const overlay = document.getElementById('cart-overlay');
    const itemsEl = document.getElementById('mini-cart-items');
    const subtotalEl = document.getElementById('mini-cart-subtotal');
    const badgeEl = document.getElementById('cart-count-badge');
    const countNumEl = document.getElementById('mini-cart-count-num');
    const shipFillEl = document.getElementById('ship-fill');
    const shipMsgEl = document.getElementById('ship-msg');
    const termsEl = document.getElementById('mini-cart-terms');
    const checkoutBtn = document.getElementById('mini-cart-checkout');

    function escapeHtml(s) {
      return String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      }[c]));
    }

    function updateBadge(count) {
      if (!badgeEl) return;
      const n = Number(count || 0);
      badgeEl.textContent = n;
      badgeEl.classList.toggle('hidden', n <= 0);
    }

    function renderItems(items) {
      if (!itemsEl) return;
      if (!items || !items.length) {
        itemsEl.innerHTML =
          '<div class="mini-cart-empty">' +
            '<span class="material-symbols-outlined mini-cart-empty__icon">shopping_bag</span>' +
            '<p class="mini-cart-empty__title">Your cart is empty</p>' +
            '<p class="mini-cart-empty__text">Discover handcrafted pieces curated for you.</p>' +
            '<a href="' + SITE + '/shop.php" class="btn-primary">Shop Now</a>' +
          '</div>';
        return;
      }
      itemsEl.innerHTML = items.map((it) => (
        '<article class="mini-cart-item" data-cart-item-id="' + it.id + '">' +
          '<a class="mini-cart-item__img" href="' + it.url + '">' +
            '<img src="' + it.image + '" alt="' + escapeHtml(it.name) + '" loading="lazy"/>' +
          '</a>' +
          '<div class="mini-cart-item__body">' +
            '<a class="mini-cart-item__title" href="' + it.url + '">' + escapeHtml(it.name) + '</a>' +
            '<div class="mini-cart-item__price">' + it.line_html + '</div>' +
            '<div class="qty-wrap" role="group" aria-label="Quantity">' +
              '<button type="button" class="qty-btn" data-cart-action="dec" aria-label="Decrease">−</button>' +
              '<span class="qty-val">' + it.qty + '</span>' +
              '<button type="button" class="qty-btn" data-cart-action="inc" aria-label="Increase">+</button>' +
            '</div>' +
          '</div>' +
          '<div class="mini-cart-item__right">' +
            '<a href="' + it.url + '" class="mini-cart-item__edit" aria-label="Edit item"><span class="material-symbols-outlined">edit</span></a>' +
            '<button type="button" class="mini-cart-item__remove" data-cart-remove aria-label="Remove item"><span class="material-symbols-outlined">delete</span></button>' +
          '</div>' +
        '</article>'
      )).join('');
    }

    function applyState(data) {
      if (!data) return;
      updateBadge(data.count);
      if (countNumEl) countNumEl.textContent = data.count || 0;
      if (data.items) renderItems(data.items);
      if (subtotalEl && data.subtotal_html) subtotalEl.textContent = data.subtotal_html;
      if (shipFillEl) {
        const pct = Math.max(0, Math.min(100, Number(data.free_shipping_progress || 0)));
        shipFillEl.style.width = pct + '%';
      }
      if (shipMsgEl) {
        if (data.free_shipping_threshold > 0) {
          shipMsgEl.innerHTML = data.free_shipping_unlocked
            ? "Congratulations! You've got <strong>free shipping</strong>!"
            : 'Spend <strong>' + data.free_shipping_remaining_html + '</strong> more for free shipping.';
        } else {
          shipMsgEl.textContent = 'Add a signature piece to begin.';
        }
      }
      syncCheckoutGate();
    }

    function syncCheckoutGate() {
      if (!checkoutBtn) return;
      const requireTerms = termsEl && !termsEl.checked;
      checkoutBtn.setAttribute('aria-disabled', requireTerms ? 'true' : 'false');
    }
    if (termsEl) termsEl.addEventListener('change', syncCheckoutGate);
    if (checkoutBtn) checkoutBtn.addEventListener('click', (e) => {
      if (checkoutBtn.getAttribute('aria-disabled') === 'true') {
        e.preventDefault();
        Toast.show('Please agree to the Terms & Conditions.', 'error');
      }
    });

    function pulseBadge() {
      if (!badgeEl || !window.gsap) return;
      gsap.fromTo(badgeEl, { scale: 1 }, { scale: 1.45, duration: 0.18, ease: 'power2.out', yoyo: true, repeat: 1 });
    }

    function open() {
      if (!drawer || !overlay) return;
      drawer.classList.add('is-open');
      overlay.classList.add('is-open');
      drawer.setAttribute('aria-hidden', 'false');
      overlay.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      if (window.gsap) {
        gsap.fromTo(drawer, { x: '100%' }, { x: '0%', duration: 0.55, ease: 'power3.out' });
        gsap.fromTo(overlay, { opacity: 0 }, { opacity: 1, duration: 0.4, ease: 'power2.out' });
      }
    }
    function close() {
      if (!drawer || !overlay) return;
      const finish = () => {
        drawer.classList.remove('is-open');
        overlay.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (window.gsap) gsap.set(drawer, { clearProps: 'all' });
      };
      if (window.gsap) {
        gsap.to(drawer, { x: '100%', duration: 0.4, ease: 'power3.in', onComplete: finish });
        gsap.to(overlay, { opacity: 0, duration: 0.3, ease: 'power2.in' });
      } else { finish(); }
    }

    function setBtnLoading(btn, loading) {
      if (!btn) return;
      if (loading) {
        if (!btn.dataset.originalText) btn.dataset.originalText = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('is-loading');
        btn.setAttribute('aria-busy', 'true');
        btn.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span><span class="sr-only">Loading…</span>';
      } else {
        btn.disabled = false;
        btn.classList.remove('is-loading');
        btn.removeAttribute('aria-busy');
        if (btn.dataset.originalText) {
          btn.innerHTML = btn.dataset.originalText;
          delete btn.dataset.originalText;
        }
      }
    }

    async function request(params) {
      const body = new URLSearchParams(params);
      body.set('ajax', '1');
      const res = await fetch(CART_ENDPOINT, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: body.toString()
      });
      if (!res.ok) throw new Error('Network error');
      return res.json();
    }

    async function refresh() {
      try { applyState(await request({ action: 'fetch' })); } catch (e) {}
    }
    async function add(id, qty, btn) {
      setBtnLoading(btn, true);
      try {
        const data = await request({ action: 'add', id, qty: qty || 1 });
        applyState(data);
        Toast.show(data.msg || 'Product added to cart', data.ok ? 'success' : 'error');
        pulseBadge();
        open();
      } catch (e) {
        Toast.show('Could not add to cart. Please try again.', 'error');
      } finally { setBtnLoading(btn, false); }
    }
    async function update(id, qty) {
      try {
        applyState(await request({ action: 'update', id, qty }));
        Toast.show('Cart updated', 'success');
      } catch (e) { Toast.show('Update failed', 'error'); }
    }
    async function remove(id) {
      try {
        applyState(await request({ action: 'remove', id }));
        Toast.show('Item removed', 'info');
      } catch (e) { Toast.show('Remove failed', 'error'); }
    }

    /* ---------- Events ---------- */
    document.addEventListener('submit', (e) => {
      const form = e.target.closest('.add-to-cart-form');
      if (!form) return;
      e.preventDefault();
      const id = form.querySelector('[name="id"]')?.value;
      const qty = form.querySelector('[name="qty"]')?.value || 1;
      const btn = form.querySelector('button[type="submit"], button:not([type="button"])');
      if (!id) return;
      add(id, qty, btn);
    });

    document.addEventListener('click', (e) => {
      const opener = e.target.closest('[data-open-cart], #cart-toggle');
      if (opener) {
        e.preventDefault();
        refresh().then(open);
        return;
      }
      const closer = e.target.closest('[data-close-cart], #close-cart-drawer');
      if (closer) { e.preventDefault(); close(); return; }
      if (overlay && e.target === overlay) { close(); return; }

      const qStep = e.target.closest('.add-to-cart-form .qty-increase, .add-to-cart-form .qty-decrease');
      if (qStep) {
        const input = qStep.parentElement.querySelector('input[name="qty"]');
        if (input) {
          let v = parseInt(input.value, 10) || 1;
          input.value = qStep.classList.contains('qty-increase') ? v + 1 : Math.max(1, v - 1);
        }
        return;
      }

      const removeBtn = e.target.closest('.mini-cart-item [data-cart-remove]');
      if (removeBtn) {
        const id = removeBtn.closest('.mini-cart-item')?.dataset.cartItemId;
        if (id) remove(id);
        return;
      }
      const qBtn = e.target.closest('.mini-cart-item [data-cart-action]');
      if (qBtn) {
        const art = qBtn.closest('.mini-cart-item');
        const id = art?.dataset.cartItemId;
        if (!id) return;
        const qtyEl = art.querySelector('.qty-val');
        let q = parseInt(qtyEl?.textContent || '1', 10);
        q = qBtn.dataset.cartAction === 'inc' ? q + 1 : q - 1;
        if (q < 1) { remove(id); } else { update(id, q); }
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && drawer?.classList.contains('is-open')) close();
    });

    /* Prime drawer with current state once on load */
    refresh();

    return { open, close, refresh, add, update, remove };
  })();

  /* ---------- Legacy flash auto-hide ---------- */
  setTimeout(() => {
    document.querySelectorAll('.flash-msg').forEach((f) => {
      f.style.transition = 'opacity .4s';
      f.style.opacity = '0';
      setTimeout(() => f.remove(), 500);
    });
  }, 4000);

  /* ---------- Public globals ---------- */
  window.addToCart = (pid, qty) => Cart.add(pid, qty || 1, null);
  window.TBCart = Cart;
  window.TBToast = Toast;
})();
