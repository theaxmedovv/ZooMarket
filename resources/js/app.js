import './bootstrap';

// ---- Dropdowns and popovers ------------------------------------------------
// <button data-dropdown-toggle aria-controls="menuId" aria-expanded="false"> + <div id="menuId" hidden>
// One open at a time; closes on outside click and Escape.
const toggles = () => document.querySelectorAll('[data-dropdown-toggle]');
const panelOf = btn => document.getElementById(btn.getAttribute('aria-controls'));

function setOpen(btn, open) {
    const panel = panelOf(btn);
    if (!panel) return;
    panel.hidden = !open;
    btn.setAttribute('aria-expanded', open);
}

document.addEventListener('click', e => {
    const btn = e.target.closest('[data-dropdown-toggle]');
    toggles().forEach(other => {
        if (other === btn) return;
        if (!panelOf(other)?.contains(e.target)) setOpen(other, false);
    });
    if (btn) setOpen(btn, btn.getAttribute('aria-expanded') !== 'true');
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') toggles().forEach(btn => setOpen(btn, false));
});

// ---- Dialogs ----------------------------------------------------------------
// <button data-dialog-open="dialogId"> opens a native <dialog>; [data-dialog-close] or a backdrop click closes it.
document.addEventListener('click', e => {
    const opener = e.target.closest('[data-dialog-open]');
    if (opener) document.getElementById(opener.dataset.dialogOpen)?.showModal();

    const closer = e.target.closest('[data-dialog-close]');
    if (closer) closer.closest('dialog')?.close();

    if (e.target instanceof HTMLDialogElement) e.target.close();
});

// ---- Password visibility ----------------------------------------------------
document.addEventListener('click', e => {
    const btn = e.target.closest('[data-password-toggle]');
    if (!btn) return;
    const input = document.getElementById(btn.dataset.passwordToggle);
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});

// ---- Header search: drop empty fields so the URL stays clean ---------------
const searchForm = document.getElementById('zmSearchForm');
if (searchForm) {
    searchForm.addEventListener('submit', () => {
        searchForm.querySelectorAll('input, select').forEach(el => {
            if (el.name && el.value === '' && (el.type !== 'radio' || el.checked)) el.disabled = true;
        });
    });
    // Back/forward cache can restore the page with those fields still disabled.
    window.addEventListener('pageshow', () => {
        searchForm.querySelectorAll(':disabled').forEach(el => { el.disabled = false; });
    });
}

// ---- Toasts -----------------------------------------------------------------
// Server messages render as <x-toast>; window.zmToast clones the same markup from #zmToastTpl.
const toastBox = document.getElementById('zmToasts');
const toastIcons = { success: 'bi-check-circle-fill', info: 'bi-info-circle-fill', warning: 'bi-exclamation-triangle-fill', error: 'bi-x-circle-fill' };

function dismissToast(toast) {
    if (!toast.isConnected || toast.dataset.hiding) return;
    toast.dataset.hiding = '1';
    setTimeout(() => toast.remove(), 220);
}
function armToast(toast) {
    toast.querySelector('[data-toast-close]').addEventListener('click', () => dismissToast(toast));
    // Errors stay long enough to read; hovering pauses the countdown.
    const delay = toast.dataset.toast === 'error' ? 9000 : 4500;
    let timer = setTimeout(() => dismissToast(toast), delay);
    toast.addEventListener('mouseenter', () => clearTimeout(timer));
    toast.addEventListener('mouseleave', () => { timer = setTimeout(() => dismissToast(toast), 2500); });
}
window.zmToast = function (text, type = 'success') {
    const tpl = document.getElementById('zmToastTpl');
    if (!toastBox || !tpl) return;
    const toast = tpl.content.firstElementChild.cloneNode(true);
    toast.dataset.toast = type;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.querySelector('i').className = 'bi ' + (toastIcons[type] || toastIcons.info);
    toast.querySelector('[data-toast-text]').textContent = text;
    toastBox.appendChild(toast);
    armToast(toast);
};
toastBox?.querySelectorAll('[data-toast]').forEach(armToast);

// ---- Favourites: toggle in place instead of reloading the page -------------
async function toggleLike(form) {
    const button = form.querySelector('button');
    if (button.getAttribute('aria-busy') === 'true') return;
    button.setAttribute('aria-busy', 'true');
    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
        });
        if (res.status === 401 || res.status === 419) { window.location.reload(); return; }
        if (!res.ok) throw new Error(res.status);
        const data = await res.json();

        button.setAttribute('aria-pressed', data.liked);
        const icon = button.querySelector('i.bi-heart, i.bi-heart-fill');
        if (icon) icon.className = icon.className.replace(/bi-heart(-fill)?/, data.liked ? 'bi-heart-fill' : 'bi-heart');
        const label = form.querySelector('[data-like-label]');
        if (label) label.textContent = data.liked ? label.dataset.on : label.dataset.off;
        const count = form.querySelector('[data-like-count]');
        if (count) count.textContent = data.likes;
        document.querySelectorAll('[data-favorites-count]').forEach(badge => {
            badge.textContent = data.favorites;
            badge.hidden = data.favorites === 0;
        });
        window.zmToast(data.message, 'success');
    } catch (e) {
        window.zmToast("Xatolik yuz berdi. Qaytadan urinib ko'ring.", 'error');
    } finally {
        button.removeAttribute('aria-busy');
    }
}

// ---- One submit per click ---------------------------------------------------
// Runs after form-level handlers, so validation or confirm() that cancels the
// submit (defaultPrevented) is respected. Buttons keep their name/value.
const spinner = '<span data-spinner aria-hidden="true" class="mr-1.5 inline-block size-[1em] animate-spin rounded-full border-2 border-current border-r-transparent align-[-0.15em]"></span>';

document.addEventListener('submit', e => {
    const form = e.target;
    if (e.defaultPrevented) return;
    if (form.matches('[data-like-form]')) { e.preventDefault(); toggleLike(form); return; }
    if ((form.method || 'get').toLowerCase() !== 'post' || form.hasAttribute('data-no-busy')) return;
    if (form.dataset.submitting) { e.preventDefault(); return; }
    form.dataset.submitting = '1';
    const button = e.submitter || form.querySelector('[type=submit]');
    if (button && !button.querySelector('[data-spinner]')) {
        button.setAttribute('aria-busy', 'true');
        button.insertAdjacentHTML('afterbegin', spinner);
    }
});
window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submitting]').forEach(form => delete form.dataset.submitting);
    document.querySelectorAll('[aria-busy]').forEach(el => el.removeAttribute('aria-busy'));
    document.querySelectorAll('[data-spinner]').forEach(el => el.remove());
});

// ---- Home: hero slider ------------------------------------------------------
const hero = document.getElementById('hero');
if (hero) {
    const track = hero.querySelector('[data-hero-track]');
    const slides = track.children;
    const dots = hero.querySelector('[data-hero-dots]');
    let index = 0, timer;
    for (let i = 0; i < slides.length; i++) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'h-2.5 w-2.5 cursor-pointer rounded-full bg-[#d9d9d9] transition-all duration-300 current:w-[30px] current:bg-brand';
        dot.setAttribute('aria-label', (i + 1) + '-slayd');
        dot.addEventListener('click', () => go(i));
        dots.appendChild(dot);
    }
    function go(i) {
        index = (i + slides.length) % slides.length;
        track.style.transform = 'translateX(' + (-index * 100) + '%)';
        [...dots.children].forEach((d, n) => n === index ? d.setAttribute('aria-current', 'true') : d.removeAttribute('aria-current'));
        clearInterval(timer);
        timer = setInterval(() => go(index + 1), 6000);
    }
    hero.querySelectorAll('[data-hero-step]').forEach(btn => btn.addEventListener('click', () => go(index + Number(btn.dataset.heroStep))));
    let startX = null;
    track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend', e => {
        if (startX === null) return;
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) go(index + (dx < 0 ? 1 : -1));
        startX = null;
    });
    go(0);
}

// ---- Home: product rail arrows ----------------------------------------------
document.querySelectorAll('[data-rail]').forEach(box => {
    const rail = document.getElementById(box.dataset.rail);
    box.querySelectorAll('button').forEach(btn => btn.addEventListener('click', () => {
        rail.scrollBy({ left: Number(btn.dataset.dir) * rail.clientWidth * 0.8, behavior: 'smooth' });
    }));
});
