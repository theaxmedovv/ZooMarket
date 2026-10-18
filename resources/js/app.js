import './bootstrap';

// ---- Dropdowns and popovers ------------------------------------------------
// <button data-dropdown-toggle aria-controls="menuId" aria-expanded="false"> + <div id="menuId" hidden>
// One open at a time; closes on outside click and Escape.
// Several buttons may control one panel (the profile menu has a header button and a tab-bar button); they stay in sync.
// A panel with [data-dropdown-backdrop="id"] next to it gets that dim layer, and a full-screen panel
// (the phone's Katalog) stops the page behind it from scrolling.
const toggles = () => document.querySelectorAll('[data-dropdown-toggle]');
const panelOf = btn => document.getElementById(btn.getAttribute('aria-controls'));
const phone = matchMedia('(max-width: 767px)');

function setOpen(btn, open) {
    const panel = panelOf(btn);
    if (!panel) return;
    panel.hidden = !open;
    toggles().forEach(t => { if (panelOf(t) === panel) t.setAttribute('aria-expanded', open); });
    const backdrop = document.querySelector('[data-dropdown-backdrop="' + panel.id + '"]');
    if (backdrop) backdrop.hidden = !open;
    if (panel.hasAttribute('data-catalog') && phone.matches) document.body.classList.toggle('overflow-hidden', open);
}

document.addEventListener('click', e => {
    const btn = e.target.closest('[data-dropdown-toggle]');
    const willOpen = btn ? btn.getAttribute('aria-expanded') !== 'true' : false;
    toggles().forEach(other => {
        if (btn && panelOf(other) === panelOf(btn)) return;
        if (!panelOf(other)?.contains(e.target)) setOpen(other, false);
    });
    if (btn) setOpen(btn, willOpen);
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') toggles().forEach(btn => setOpen(btn, false));
});

// Header: a soft shadow once the page has scrolled
const header = document.getElementById('zmHeader');
if (header) {
    const mark = () => header.toggleAttribute('data-scrolled', scrollY > 4);
    addEventListener('scroll', mark, { passive: true });
    mark();
}

// Tab bar "Qidirish": back to the top and into the search field
document.addEventListener('click', e => {
    if (!e.target.closest('[data-focus-search]')) return;
    scrollTo({ top: 0, behavior: 'smooth' });
    document.querySelector('#zmSearchForm input[type=search]')?.focus({ preventScroll: true });
});

// ---- Bottom sheets ----------------------------------------------------------
// <button data-sheet-open="id"> shows <div id="id" class="max-lg:hidden ...">, a dim layer sits behind it; data-sheet-close,
// the layer or Escape closes it. From `lg` up the sheet is simply part of the page (the filters).
const sheetLayer = document.createElement('div');
sheetLayer.hidden = true;
sheetLayer.className = 'fixed inset-0 z-40 bg-black/40 lg:hidden';
document.body.appendChild(sheetLayer);
let openSheet = null;

function toggleSheet(sheet) {
    if (openSheet) openSheet.classList.add('max-lg:hidden');
    openSheet = sheet;
    sheetLayer.hidden = !sheet;
    document.body.classList.toggle('overflow-hidden', !!sheet);
    if (sheet) sheet.classList.remove('max-lg:hidden');
}
document.addEventListener('click', e => {
    const opener = e.target.closest('[data-sheet-open]');
    if (opener) toggleSheet(document.getElementById(opener.dataset.sheetOpen));
    else if (e.target.closest('[data-sheet-close]') || e.target === sheetLayer) toggleSheet(null);
});
document.addEventListener('keydown', e => { if (e.key === 'Escape' && openSheet) toggleSheet(null); });

// ---- Dialogs ----------------------------------------------------------------
// <button data-dialog-open="dialogId"> opens a native <dialog>; [data-dialog-close] or a backdrop click closes it.
document.addEventListener('click', e => {
    const opener = e.target.closest('[data-dialog-open]');
    if (opener) document.getElementById(opener.dataset.dialogOpen)?.showModal();

    const closer = e.target.closest('[data-dialog-close]');
    if (closer) closer.closest('dialog')?.close();

    if (e.target instanceof HTMLDialogElement) e.target.close();
});

// "Sotib olish" on a listing card links to the listing with #buy: open its buy dialog straight away.
// The hash is dropped so a reload or Back doesn't pop the dialog up again.
if (location.hash === '#buy') {
    history.replaceState(null, '', location.pathname + location.search);
    document.getElementById('buyModalDetail')?.showModal();
}

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
        // On the favourites page an un-liked card no longer belongs there.
        const grid = form.closest('[data-favorites-grid]');
        if (grid && !data.liked) {
            form.closest('article')?.remove();
            if (!grid.querySelector('article')) window.location.reload();
        }
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
        dot.className = 'group grid h-11 min-w-11 cursor-pointer place-items-center';
        dot.innerHTML = '<span class="block h-2.5 w-2.5 rounded-full bg-field transition-all duration-300 group-aria-[current]:w-7 group-aria-[current]:bg-brand"></span>';
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

// ---- Listing form: the questions depend on the category -------------------------
// data-profiles: {categoryId: {breed_label, breed_placeholder, quantity_label, age_label, age_placeholder,
// gender, age, color, name}, default: {...}}. Picking a category re-labels the basic fields, hides the ones that don't
// apply (sex for fish, age for beekeeping, ...) and shows that category's own group of extra questions.
// Everything hidden is also disabled, so it is neither validated by the browser nor submitted.
document.querySelectorAll('[data-listing-form]').forEach(form => {
    const profiles = JSON.parse(form.dataset.profiles);
    const select = form.querySelector('[name="category_id"]');
    if (!select) return;

    const setOff = (el, off) => {
        el.toggleAttribute('data-profile-off', off);
        el.querySelectorAll('input, select, textarea').forEach(input => (input.disabled = off));
    };

    const apply = () => {
        const profile = profiles[select.value] ?? profiles.default;

        form.querySelectorAll('[data-profile-label]').forEach(el => {
            const text = profile[el.dataset.profileLabel + '_label'];
            if (text) el.textContent = text;
        });
        form.querySelectorAll('[data-profile-placeholder]').forEach(el => {
            const text = profile[el.dataset.profilePlaceholder + '_placeholder'];
            if (text) el.placeholder = text;
        });
        form.querySelectorAll('[data-profile-show]').forEach(el => setOff(el, !profile[el.dataset.profileShow]));

        let anyExtras = false;
        form.querySelectorAll('[data-profile-extras]').forEach(group => {
            const on = group.dataset.profileExtras === profile.name;
            anyExtras ||= on;
            setOff(group, !on);
        });
        // Section wrappers only appear when there is a group to show; their inputs are handled per group above
        form.querySelectorAll('[data-profile-section]').forEach(section => section.toggleAttribute('data-profile-off', !anyExtras));
    };

    select.addEventListener('change', apply);
    apply();
});

// ---- Profile photo: preview the chosen file before saving ---------------------
document.querySelectorAll('[data-avatar-input]').forEach(input => input.addEventListener('change', () => {
    const file = input.files?.[0];
    const preview = input.closest('form').querySelector('[data-avatar-preview]');
    if (!file || !preview) return;
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');
    input.closest('form').querySelector('[data-avatar-initial]')?.remove();
}));

// ---- Katalog mega menu --------------------------------------------------------
// Hovering (or focusing) a category on the left shows its panel on the right; "Yana N" reveals the rest of a list.
document.querySelectorAll('[data-catalog]').forEach(menu => {
    const tabs = [...menu.querySelectorAll('[data-catalog-tab]')];
    const panels = [...menu.querySelectorAll('[data-catalog-panel]')];

    const show = id => {
        tabs.forEach(tab => tab.setAttribute('aria-selected', tab.dataset.catalogTab === id));
        // `hidden` keeps the panel out on narrow screens (md:block would otherwise show it)
        panels.forEach(panel => (panel.hidden = panel.dataset.catalogPanel !== id));
    };

    // Phone: the panel is full-screen. First the category list; tapping one drills into its lists, the arrow goes back.
    const list = menu.querySelector('[data-catalog-list]');
    const back = menu.querySelector('[data-catalog-back]');
    const title = menu.querySelector('[data-catalog-title]');
    const overview = () => {
        list.hidden = false;
        back.hidden = true;
        title.textContent = 'Katalog';
        panels.forEach(panel => (panel.hidden = true));
    };
    const drill = tab => {
        show(tab.dataset.catalogTab);
        list.hidden = true;
        back.hidden = false;
        title.textContent = tab.textContent.trim();
        menu.scrollTop = 0;
    };

    tabs.forEach(tab => {
        tab.addEventListener('mouseenter', () => { if (!phone.matches) show(tab.dataset.catalogTab); });
        tab.addEventListener('focus', () => { if (!phone.matches) show(tab.dataset.catalogTab); });
        tab.addEventListener('click', e => {
            if (!phone.matches) return;
            e.preventDefault();
            drill(tab);
        });
    });
    back.addEventListener('click', overview);

    const initial = () => {
        if (phone.matches) { overview(); return; }
        list.hidden = false;
        show((menu.querySelector('[data-catalog-tab][aria-current]') ?? tabs[0])?.dataset.catalogTab);
    };
    initial();
    phone.addEventListener('change', initial);
});

// "Yana N": reveals the folded items next to it (Katalog menu lists and the filter sidebar).
document.addEventListener('click', e => {
    const more = e.target.closest('[data-catalog-more]');
    if (!more) return;
    more.parentElement.querySelectorAll('[data-catalog-extra]').forEach(item => (item.hidden = false));
    more.remove();
});

// ---- Filter sidebar ------------------------------------------------------------
// data-autosubmit: any change reloads the results; empty fields are left out of the URL.
document.addEventListener('change', e => {
    const form = e.target.closest('form[data-autosubmit]');
    if (form && !(form.hasAttribute('data-sheet-form') && matchMedia('(max-width: 1023px)').matches)) form.requestSubmit();
});
document.addEventListener('submit', e => {
    if (!e.target.matches('form[data-autosubmit]')) return;
    [...e.target.elements].forEach(el => {
        if (el.name && el.value === '' && el.type !== 'submit') el.disabled = true;
    });
    // Back/forward restores the page from cache: don't leave fields disabled
    addEventListener('pageshow', () => [...e.target.elements].forEach(el => (el.disabled = false)), { once: true });
});

// ---- Category row: links that don't fit go under "Yana" ----------------------
// The row holds every link and the "Yana" menu a hidden copy of each; on every resize
// the first link that runs past the row's edge and all after it swap to their copy.
document.querySelectorAll('[data-overflow-nav]').forEach(nav => {
    const row = nav.querySelector('[data-overflow-items]');
    const more = nav.querySelector('[data-overflow-more]');
    const links = [...row.children];
    const copies = [...more.querySelectorAll('[data-overflow-copy]')];
    const wide = matchMedia('(min-width: 1024px)');

    const layout = () => {
        // Below lg the chips simply scroll sideways: everything shown, no "Yana".
        row.classList.toggle('overflow-hidden', wide.matches);
        row.classList.toggle('overflow-x-auto', !wide.matches);
        // Measure with every link shown and no "Yana", then make room for "Yana" if needed.
        more.hidden = true;
        links.forEach(link => (link.hidden = false));
        copies.forEach(copy => (copy.hidden = true));
        if (!wide.matches) return;
        more.hidden = row.scrollWidth <= row.clientWidth;
        const edge = row.getBoundingClientRect().right + 1;
        let overflowing = false;
        links.forEach((link, i) => {
            overflowing ||= !more.hidden && link.getBoundingClientRect().right > edge;
            link.hidden = overflowing;
            copies[i].hidden = !overflowing;
        });
    };

    new ResizeObserver(layout).observe(nav);
    document.fonts?.ready.then(layout);
});

// ---- Home: "Yana 12 tasini ko'rish" ------------------------------------------
// <button data-load-more="url" data-load-into="gridId">: the url answers {html, next};
// the cards are appended and the button moves on to the next 12, or goes away at the end.
document.addEventListener('click', async e => {
    const button = e.target.closest('[data-load-more]');
    if (!button || button.getAttribute('aria-busy') === 'true') return;
    button.setAttribute('aria-busy', 'true');
    try {
        const res = await fetch(button.dataset.loadMore, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(res.status);
        const data = await res.json();
        document.getElementById(button.dataset.loadInto)?.insertAdjacentHTML('beforeend', data.html);
        if (data.next) button.dataset.loadMore = data.next;
        else button.parentElement.remove();
    } catch (err) {
        window.zmToast("Xatolik yuz berdi. Qaytadan urinib ko'ring.", 'error');
    } finally {
        button.removeAttribute('aria-busy');
    }
});
