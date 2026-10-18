{{-- Main admin overview on the profile page. Needs $admin = ['stats', 'queue', 'decisions'] (ProfileController). --}}
@php
    $stats = $admin['stats'];
    $panelLink = 'inline-flex items-center gap-1 rounded-lg border border-line px-3 py-[7px] text-xs font-semibold text-ink transition hover:border-brand hover:bg-brand/8 hover:text-brand';
    $decisionPill = [
        'approved' => ['border-ok/30 bg-ok/10 text-ok', 'bi-check-circle-fill', 'Tasdiqlangan'],
        'rejected' => ['border-danger/30 bg-danger/10 text-danger', 'bi-x-circle-fill', 'Rad etilgan'],
        'pending' => ['border-amber-400/40 bg-amber-100 text-amber-700', 'bi-clock-history', 'Tekshiruvda'],
    ];
@endphp

{{-- What needs attention first --}}
<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat-card icon="bi-hourglass-split" label="Kutilayotgan e'lonlar" :value="$stats['pending_posts']" :href="route('moderation.index')"
                 :alert="$stats['pending_posts'] > 0" tone="bg-amber-400/25 text-amber-600"
                 :hint="$stats['oldest_pending'] ? 'Eng eskisi ' . \Illuminate\Support\Carbon::parse($stats['oldest_pending'])->diffForHumans() . ' kutmoqda' : 'Hammasi ko\'rib chiqilgan'" />
    <x-stat-card icon="bi-images" label="Kutilayotgan bannerlar" :value="$stats['pending_banners']" :href="route('banners.index', ['status' => 'pending'])"
                 :alert="$stats['pending_banners'] > 0" tone="bg-amber-400/25 text-amber-600" />
    <x-stat-card icon="bi-megaphone" label="Sotuvdagi e'lonlar" :value="$stats['active_posts']" :hint="'Bugun yangi: ' . $stats['new_today']" />
    <x-stat-card icon="bi-people" label="Foydalanuvchilar" :value="$stats['users']" :hint="'Sotilgan e\'lonlar: ' . $stats['sold_posts']" tone="bg-[#5ea4ff]/12 text-[#1a6fd1]" />
</div>

<div class="grid items-start gap-6 xl:grid-cols-12">
<div class="flex flex-col gap-6 xl:col-span-7">
    <x-panel title="Tekshiruv navbati" subtitle="Eng uzoq kutayotgan e'lonlar birinchi">
        <x-slot:action>
            <a href="{{ route('moderation.index') }}" class="{{ $panelLink }}">Hammasini ochish <i class="bi bi-arrow-right"></i></a>
        </x-slot:action>

        <div class="divide-y divide-line">
            @forelse($admin['queue'] as $post)
                <a href="{{ route('moderation.index') }}" class="group flex items-center gap-3 py-3 transition-colors hover:bg-brand/4 sm:-mx-2 sm:rounded-xl sm:px-2">
                    @if($post->image)
                        <img src="{{ $post->imageUrl() }}" alt="" class="size-14 shrink-0 rounded-xl border border-line object-cover">
                    @else
                        <div class="grid size-14 shrink-0 place-items-center rounded-xl border border-line bg-brand/8 text-lg text-muted"><i class="bi bi-image"></i></div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[15px] font-bold text-ink group-hover:text-brand">{{ $post->title }}</div>
                        <div class="truncate text-xs text-muted">{{ $post->category?->name }} · {{ $post->user?->name ?? "O'chirilgan foydalanuvchi" }} · {{ $post->updated_at->diffForHumans() }}</div>
                    </div>
                    <i class="bi bi-chevron-right text-faint"></i>
                </a>
            @empty
                <x-empty-state icon="bi-check2-all" title="Navbat bo'sh" text="Tekshirishni kutayotgan e'lon yo'q — hammasi ko'rib chiqilgan." />
            @endforelse
        </div>
    </x-panel>

    <x-panel title="Oxirgi qarorlaringiz" subtitle="Siz tasdiqlagan yoki rad etgan e'lonlar">
        <div class="divide-y divide-line">
            @forelse($admin['decisions'] as $post)
                @php [$pillClass, $pillIcon, $pillLabel] = $decisionPill[$post->moderation_status] ?? $decisionPill['pending']; @endphp
                <a href="{{ route('posts.show', $post) }}" class="group flex items-center gap-3 py-3">
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-bold text-ink group-hover:text-brand">{{ $post->title }}</div>
                        <div class="truncate text-xs text-muted">
                            {{ $post->user?->name ?? "O'chirilgan foydalanuvchi" }} · {{ $post->moderated_at?->diffForHumans() }}
                            @if($post->moderation_status === 'rejected' && $post->moderation_reason) · {{ \Illuminate\Support\Str::limit($post->moderation_reason, 60) }}@endif
                        </div>
                    </div>
                    <span class="inline-flex shrink-0 items-center gap-1 rounded border px-1.5 py-px text-[11px] font-bold uppercase {{ $pillClass }}"><i class="bi {{ $pillIcon }}"></i>{{ $pillLabel }}</span>
                </a>
            @empty
                <p class="py-6 text-center text-sm text-muted">Hali qaror qabul qilmagansiz.</p>
            @endforelse
        </div>
    </x-panel>
</div>

<div class="xl:col-span-5">
    @include('profile.partials.settings', ['updateRoute' => route('admin.profile.update'), 'nameLabel' => "To'liq ism"])
</div>
</div>
