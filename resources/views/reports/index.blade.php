@extends('layouts.app')

@section('content')
@php
    $btn = 'inline-flex min-h-11 cursor-pointer items-center gap-1.5 rounded-lg border px-3 text-sm font-bold whitespace-nowrap transition lg:min-h-9';
@endphp
<div class="page">
    <x-page-head title="Shikoyatlar" subtitle="Foydalanuvchilar yuborgan shikoyatlar. Tekshirib chiqing: asosli bo'lsa e'lonni olib tashlang yoki shikoyatni yoping, asossiz bo'lsa rad eting." />

    <div class="mb-6 flex flex-wrap gap-2 border-b border-line pb-3">
        <x-status-tab :href="route('reports.index', ['status' => 'open'])" :active="$status === 'open'" :count="$counts['open'] ?? 0" :highlight="($counts['open'] ?? 0) > 0"><i class="bi bi-flag-fill mr-1 text-danger"></i> Ochiq</x-status-tab>
        <x-status-tab :href="route('reports.index', ['status' => 'resolved'])" :active="$status === 'resolved'" :count="$counts['resolved'] ?? 0"><i class="bi bi-check-circle-fill mr-1 text-brand"></i> Ko'rib chiqilgan</x-status-tab>
        <x-status-tab :href="route('reports.index', ['status' => 'dismissed'])" :active="$status === 'dismissed'" :count="$counts['dismissed'] ?? 0"><i class="bi bi-x-circle-fill mr-1 text-muted"></i> Asossiz</x-status-tab>
    </div>

    <div class="flex flex-col gap-4">
        @forelse($reports as $report)
            <article class="rounded-[20px] border border-line bg-white p-4 md:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <span class="inline-flex items-center gap-1 rounded-full border border-danger/30 bg-danger/10 px-2.5 py-1 text-xs font-bold text-danger"><i class="bi bi-flag-fill"></i>{{ $report->reasonLabel() }}</span>
                        <div class="mt-2 text-base font-bold break-words text-ink">
                            @if($report->post_id)
                                <i class="bi bi-card-text mr-1 text-brand"></i>E'lon:
                                <a href="{{ route('posts.show', $report->post) }}" class="hover:text-brand">{{ $report->post?->title ?? "O'chirilgan e'lon" }}</a>
                                @if($report->post?->user)<span class="text-sm font-normal text-muted"> · sotuvchi <a href="{{ route('sellers.show', $report->post->user) }}" class="hover:text-brand">{{ $report->post->user->name }}</a></span>@endif
                            @else
                                <i class="bi bi-person mr-1 text-brand"></i>Sotuvchi:
                                <a href="{{ route('sellers.show', $report->reportedUser) }}" class="hover:text-brand">{{ $report->reportedUser?->name }}</a>
                            @endif
                        </div>
                    </div>
                    <small class="text-xs text-muted">{{ $report->created_at->diffForHumans() }}</small>
                </div>

                @if($report->comment)
                    <p class="mt-3 rounded-xl bg-soft px-3 py-2 text-sm break-words whitespace-pre-line text-ink">{{ $report->comment }}</p>
                @endif

                <div class="mt-3 text-xs text-muted">
                    <i class="bi bi-person mr-1"></i>Shikoyatchi: {{ $report->reporter?->name ?? "O'chirilgan foydalanuvchi" }}
                    @if($report->reporter) · {{ $report->reporter->email }}@endif
                    @if($report->status !== 'open' && $report->handler) · {{ $report->handler->name }} ko'rib chiqdi, {{ $report->handled_at?->diffForHumans() }}@endif
                </div>

                @if($report->status === 'open')
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-line pt-4">
                        @if($report->post_id && $report->post && $report->post->moderation_status === 'approved')
                            <form action="{{ route('reports.hide-post', $report) }}" method="POST" onsubmit="return confirm('E\'lon olib tashlanadi (sotuvchiga sabab ko\'rsatiladi). Davom etasizmi?')">
                                @csrf
                                <button type="submit" class="{{ $btn }} border-danger bg-danger text-white hover:brightness-95"><i class="bi bi-eye-slash"></i> E'lonni olib tashlash</button>
                            </form>
                        @endif
                        <form action="{{ route('reports.resolve', $report) }}" method="POST">
                            @csrf
                            <button type="submit" class="{{ $btn }} border-brand/35 bg-brand/10 text-brand hover:bg-brand hover:text-white"><i class="bi bi-check2-circle"></i> Ko'rib chiqildi</button>
                        </form>
                        <form action="{{ route('reports.dismiss', $report) }}" method="POST">
                            @csrf
                            <button type="submit" class="{{ $btn }} border-line text-muted hover:border-ink hover:text-ink"><i class="bi bi-x-circle"></i> Asossiz</button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <x-empty-state icon="bi-flag" title="Shikoyat yo'q" text="Bu bo'limda hozircha shikoyat yo'q." />
        @endforelse
    </div>

    @if($reports->hasPages())
        <div class="mt-8">{{ $reports->links() }}</div>
    @endif
</div>
@endsection
