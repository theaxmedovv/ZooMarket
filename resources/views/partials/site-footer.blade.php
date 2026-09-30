@php
    $zmUser = auth()->user();
    $zmProfileUrl = $zmUser
        ? ($zmUser->hasRole('user') ? route('user.profile.show') : route('profile.show'))
        : route('login');
@endphp
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
            <div>
                <h5>Mijozlarga xizmat</h5>
                <ul>
                    <li><a href="{{ $zmProfileUrl }}">Mening profilim</a></li>
                    @if($zmUser?->hasRole('seller'))
                        <li><a href="{{ route('admin.purchase-requests.index') }}">So'rovlar</a></li>
                        <li><a href="{{ route('admin.archive.index') }}">Arxiv</a></li>
                    @else
                        <li><a href="{{ $zmUser ? route('user.purchase-requests.index') : route('login') }}">Buyurtmalarim</a></li>
                    @endif
                    <li><a href="{{ $zmUser ? route('chats.index') : route('login') }}">Xabarlar</a></li>
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
    toggler('zmMenuBtn', 'zmDrawer', (btn, open) => {
        btn.querySelector('i').className = open ? 'bi bi-x-lg' : 'bi bi-list';
    });
    toggler('zmProfileBtn', 'zmProfileMenu');
})();
</script>
