<footer class="zm-footer">
    <span class="zm-footer-deco"></span>
    <div class="zm-wrap">
        <div class="zm-footer-grid">
            <div>
                <a href="{{ route('home') }}" class="zm-logo">ZooMarket</a>
                <h5>Biz bilan bog'lanish</h5>
                <div class="zm-contact"><i class="bi bi-whatsapp"></i><div><small>WhatsApp</small><b>+998 90 000 00 00</b></div></div>
                <div class="zm-contact"><i class="bi bi-telephone"></i><div><small>Qo'ng'iroq qiling</small><b>+998 71 200 00 00</b></div></div>
                <h5 style="margin-top: 24px">Ilovani yuklab oling</h5>
                <div class="zm-stores">
                    <a href="#" class="zm-store"><i class="bi bi-apple"></i><span><small>Download on the</small><b>App Store</b></span></a>
                    <a href="#" class="zm-store"><i class="bi bi-google-play"></i><span><small>GET IT ON</small><b>Google Play</b></span></a>
                </div>
            </div>
            <div>
                <h5>Mashhur kategoriyalar</h5>
                <ul>
                    @foreach(($navCategories ?? collect())->take(7) as $category)
                        <li><a href="{{ route('posts.index', ['category_id' => $category->id]) }}">{{ $category->name }}</a></li>
                    @endforeach
                    <li><a href="{{ route('posts.index') }}">Barcha e'lonlar</a></li>
                </ul>
            </div>
            {{-- Account links live only in the header; the footer is for browsing and info. --}}
            <div>
                <h5>Ma'lumot</h5>
                <ul>
                    <li><a href="#">Biz haqimizda</a></li>
                    <li><a href="#">Xavfsiz xarid qoidalari</a></li>
                    @guest<li><a href="{{ route('register') }}">Sotuvchi bo'lish</a></li>@endguest
                    <li><a href="#">Foydalanish shartlari</a></li>
                    <li><a href="#">Maxfiylik siyosati</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="zm-footer-bottom">&copy; {{ date('Y') }} ZooMarket. Barcha huquqlar himoyalangan.</div>
</footer>

<script>
(function () {
    function toggler(btnId, panelId, onToggle) {
        const btn = document.getElementById(btnId);
        const panel = document.getElementById(panelId);
        if (!btn || !panel) return () => {};
        const set = open => {
            panel.classList.toggle('open', open);
            btn.classList.toggle('open', open);
            btn.setAttribute('aria-expanded', open);
            if (onToggle) onToggle(btn, open);
        };
        btn.addEventListener('click', e => { e.stopPropagation(); set(!panel.classList.contains('open')); });
        document.addEventListener('click', e => { if (!panel.contains(e.target)) set(false); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') set(false); });
        return set;
    }
    toggler('zmProfileBtn', 'zmProfileMenu');
    toggler('zmFilterBtn', 'zmFilterPanel');

    // Search + filters share one form; drop empty fields so the URL stays clean.
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

    // ---- Toasts -------------------------------------------------------------
    const toastBox = document.getElementById('zmToasts');
    const toastIcons = { success: 'bi-check-circle-fill', info: 'bi-info-circle-fill', warning: 'bi-exclamation-triangle-fill', error: 'bi-x-circle-fill' };
    function dismissToast(toast) {
        if (!toast.isConnected || toast.classList.contains('hiding')) return;
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 220);
    }
    function armToast(toast) {
        toast.querySelector('.zm-toast-close').addEventListener('click', () => dismissToast(toast));
        // Errors stay long enough to read; hovering pauses the countdown.
        const delay = toast.classList.contains('zm-toast-error') ? 9000 : 4500;
        let timer = setTimeout(() => dismissToast(toast), delay);
        toast.addEventListener('mouseenter', () => clearTimeout(timer));
        toast.addEventListener('mouseleave', () => { timer = setTimeout(() => dismissToast(toast), 2500); });
    }
    window.zmToast = function (text, type = 'success') {
        if (!toastBox) return;
        const toast = document.createElement('div');
        toast.className = 'zm-toast zm-toast-' + type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.innerHTML = '<i class="bi ' + (toastIcons[type] || toastIcons.info) + '"></i><span></span>'
            + '<button type="button" class="zm-toast-close" aria-label="Yopish"><i class="bi bi-x-lg"></i></button>';
        toast.querySelector('span').textContent = text;
        toastBox.appendChild(toast);
        armToast(toast);
    };
    toastBox?.querySelectorAll('.zm-toast').forEach(armToast);

    // ---- Favourites: toggle in place instead of reloading the page ----------
    async function toggleLike(form) {
        const button = form.querySelector('button');
        if (button.classList.contains('zm-busy')) return;
        button.classList.add('zm-busy');
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });
            if (res.status === 401 || res.status === 419) { window.location.reload(); return; }
            if (!res.ok) throw new Error(res.status);
            const data = await res.json();

            button.classList.toggle('liked', data.liked);
            button.classList.toggle('is-liked', data.liked);
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
            button.classList.remove('zm-busy');
        }
    }

    // ---- One submit per click ------------------------------------------------
    // Runs after form-level handlers, so validation or confirm() that cancels the
    // submit (defaultPrevented) is respected. Buttons keep their name/value.
    document.addEventListener('submit', e => {
        const form = e.target;
        if (e.defaultPrevented) return;
        if (form.matches('[data-like-form]')) { e.preventDefault(); toggleLike(form); return; }
        if ((form.method || 'get').toLowerCase() !== 'post' || form.hasAttribute('data-no-busy')) return;
        if (form.dataset.submitting) { e.preventDefault(); return; }
        form.dataset.submitting = '1';
        const button = e.submitter || form.querySelector('[type=submit]');
        if (button && !button.querySelector('.zm-spinner')) {
            button.classList.add('zm-busy');
            button.setAttribute('aria-busy', 'true');
            button.insertAdjacentHTML('afterbegin', '<span class="zm-spinner" aria-hidden="true"></span>');
        }
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form[data-submitting]').forEach(form => delete form.dataset.submitting);
        document.querySelectorAll('.zm-busy').forEach(el => { el.classList.remove('zm-busy'); el.removeAttribute('aria-busy'); });
        document.querySelectorAll('.zm-spinner').forEach(el => el.remove());
    });
})();
</script>
