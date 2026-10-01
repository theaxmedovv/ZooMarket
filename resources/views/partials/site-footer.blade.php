@php
    $zmFooterTitle = "relative mb-4 pb-2.5 text-xl font-extrabold after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-10 after:bg-white after:content-['']";
    $zmFooterList = 'grid list-disc gap-2.5 pl-[18px] font-semibold [&_a]:hover:underline';
@endphp
<footer id="zmFooter" class="relative mt-auto overflow-hidden bg-brand text-white">
    <span class="absolute -top-20 -right-[60px] size-[360px] rounded-full border-[70px] border-white/[.07]" aria-hidden="true"></span>
    <div class="wrap relative">
        <div class="grid grid-cols-1 gap-7 pt-[52px] pb-10 md:grid-cols-[1.3fr_1fr_1fr] md:gap-10">
            <div>
                <a href="{{ route('home') }}" class="mb-[26px] inline-block text-[32px] leading-none font-black tracking-tight">ZooMarket</a>
                <h5 class="{{ $zmFooterTitle }}">Biz bilan bog'lanish</h5>
                @foreach([['bi-whatsapp', 'WhatsApp', '+998 90 000 00 00'], ['bi-telephone', "Qo'ng'iroq qiling", '+998 71 200 00 00']] as [$icon, $label, $phone])
                    <div class="mb-3.5 flex items-center gap-3">
                        <i class="bi {{ $icon }} text-[22px]"></i>
                        <div><small class="block text-[13px] opacity-85">{{ $label }}</small><b class="text-[15px]">{{ $phone }}</b></div>
                    </div>
                @endforeach
                <h5 class="{{ $zmFooterTitle }} mt-6">Ilovani yuklab oling</h5>
                <div class="mt-1.5 flex flex-wrap gap-2.5">
                    @foreach([['bi-apple', 'Download on the', 'App Store'], ['bi-google-play', 'GET IT ON', 'Google Play']] as [$icon, $small, $store])
                        <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-white/40 bg-black px-3.5 py-[7px]">
                            <i class="bi {{ $icon }} text-2xl"></i>
                            <span><small class="block text-[10px] leading-none opacity-80">{{ $small }}</small><b class="text-[15px] leading-tight">{{ $store }}</b></span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div>
                <h5 class="{{ $zmFooterTitle }}">Mashhur kategoriyalar</h5>
                <ul class="{{ $zmFooterList }}">
                    @foreach(($navCategories ?? collect())->take(7) as $category)
                        <li><a href="{{ route('posts.index', ['category_id' => $category->id]) }}">{{ $category->name }}</a></li>
                    @endforeach
                    <li><a href="{{ route('posts.index') }}">Barcha e'lonlar</a></li>
                </ul>
            </div>
            {{-- Account links live only in the header; the footer is for browsing and info. --}}
            <div>
                <h5 class="{{ $zmFooterTitle }}">Ma'lumot</h5>
                <ul class="{{ $zmFooterList }}">
                    <li><a href="#">Biz haqimizda</a></li>
                    <li><a href="#">Xavfsiz xarid qoidalari</a></li>
                    @guest<li><a href="{{ route('register') }}">Sotuvchi bo'lish</a></li>@endguest
                    <li><a href="#">Foydalanish shartlari</a></li>
                    <li><a href="#">Maxfiylik siyosati</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="relative bg-brand-dark p-4 text-center text-sm font-semibold">&copy; {{ date('Y') }} ZooMarket. Barcha huquqlar himoyalangan.</div>
</footer>
