@props(['post'])
{{-- Main admin's decision box for one listing: approve to publish, or reject with a reason the seller will see. --}}
@php
    $status = $post->moderation_status;
    // Several panels share one page, so a failed reject only re-opens the form it came from.
    $failedHere = (int) old('post_id') === (int) $post->id && $errors->has('reason');
    $btn = 'inline-flex h-10 cursor-pointer items-center justify-center gap-1.5 rounded-xl px-4 text-sm font-bold whitespace-nowrap transition';
@endphp
<div {{ $attributes->class('rounded-2xl border border-line bg-white p-4 shadow-sm') }}>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0 text-sm">
            @if($status === 'approved')
                <span class="inline-flex items-center gap-1 font-bold text-green-700"><i class="bi bi-shield-check"></i> Tasdiqlangan — hammaga ko'rinadi</span>
            @elseif($status === 'rejected')
                <span class="inline-flex items-center gap-1 font-bold text-danger"><i class="bi bi-shield-x"></i> Rad etilgan {{ $post->moderated_by ? '(admin)' : '(avtomatik tekshiruv)' }}</span>
            @else
                <span class="inline-flex items-center gap-1 font-bold text-amber-600"><i class="bi bi-hourglass-split"></i> Tekshiruv kutilmoqda — hozircha faqat sotuvchiga ko'rinadi</span>
            @endif
            @if($post->moderated_at && $status !== 'pending')
                <small class="block text-muted">{{ $post->moderator?->name ?? 'AI' }} · {{ $post->moderated_at->diffForHumans() }}</small>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            @if($status !== 'approved')
                <form action="{{ route('moderation.approve', $post) }}" method="POST">
                    @csrf
                    <button type="submit" class="{{ $btn }} bg-brand text-white hover:bg-brand-dark"><i class="bi bi-check-lg"></i> Tasdiqlash</button>
                </form>
            @endif
        </div>
    </div>

    @if($status === 'rejected' && $post->moderation_reason)
        <div class="mt-3 rounded-lg border-l-[3px] border-danger bg-danger/6 p-3 text-sm">
            <strong class="text-danger">Sabab:</strong> <span class="whitespace-pre-line text-ink">{{ $post->moderation_reason }}</span>
        </div>
    @endif

    <details class="group mt-3" @if($failedHere) open @endif>
        <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 text-sm font-bold text-danger hover:underline">
            <i class="bi bi-x-circle"></i> {{ $status === 'rejected' ? "Sababni o'zgartirish" : 'Rad etish' }}
        </summary>
        <form action="{{ route('moderation.reject', $post) }}" method="POST" class="mt-3 flex flex-col gap-2">
            @csrf
            <input type="hidden" name="post_id" value="{{ $post->id }}">
            <label for="reason-{{ $post->id }}" class="text-xs font-bold text-muted">Rad etish sababi (sotuvchiga ko'rsatiladi)</label>
            <textarea id="reason-{{ $post->id }}" name="reason" rows="3" required minlength="5" maxlength="1000"
                      class="input rounded-lg p-3 text-sm"
                      placeholder="Masalan: Rasmda hayvon ko'rinmaydi, tavsifda telefon raqami va boshqa reklama bor...">{{ $failedHere ? old('reason') : '' }}</textarea>
            @if($failedHere)
                <small class="text-danger">{{ $errors->first('reason') }}</small>
            @endif
            <div>
                <button type="submit" class="{{ $btn }} border border-danger bg-white text-danger hover:bg-danger hover:text-white"><i class="bi bi-send"></i> Rad etish va sababini yuborish</button>
            </div>
        </form>
    </details>
</div>
