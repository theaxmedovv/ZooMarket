{{-- "Shikoyat qilish" dialog. $id: dialog id (opened with data-dialog-open), $action: where it posts, $subject: what is being reported. --}}
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}Label"
        class="m-auto max-h-[90dvh] w-[min(500px,calc(100vw-2rem))] overflow-y-auto overscroll-contain rounded-2xl border border-line bg-white p-0 text-ink backdrop:bg-black/50 max-md:mt-auto max-md:mb-0 max-md:w-full max-md:max-w-full max-md:rounded-b-none">
    <div class="flex items-center justify-between px-4 pt-4 md:px-6 md:pt-5">
        <h5 class="text-lg font-bold text-ink" id="{{ $id }}Label"><i class="bi bi-flag-fill mr-2 text-danger"></i> Shikoyat qilish</h5>
        <button type="button" data-dialog-close aria-label="Yopish" class="grid size-11 cursor-pointer place-items-center rounded-lg text-muted hover:bg-soft hover:text-ink"><i class="bi bi-x-lg"></i></button>
    </div>
    <form action="{{ $action }}" method="POST">
        @csrf
        <div class="flex flex-col gap-4 p-4 md:p-6">
            <p class="text-sm text-muted">{{ $subject }} haqida adminga xabar bering. Shikoyatni faqat admin ko'radi, sotuvchiga ko'rsatilmaydi.</p>

            <fieldset class="flex flex-col gap-1.5">
                <legend class="mb-1.5 text-sm font-semibold text-ink">Sabab</legend>
                @foreach(config('zoomarket.report_reasons') as $key => $label)
                    <label class="flex min-h-11 cursor-pointer items-center gap-2.5 rounded-lg border border-line px-3 py-2 text-sm text-ink hover:border-brand/45 has-checked:border-brand has-checked:bg-brand-soft">
                        <input type="radio" name="reason" value="{{ $key }}" required class="size-4 accent-brand">
                        {{ $label }}
                    </label>
                @endforeach
            </fieldset>

            <label class="flex flex-col gap-1.5 text-sm font-semibold text-ink">
                Izoh <span class="font-normal text-muted">(ixtiyoriy, "Boshqa sabab" uchun majburiy)</span>
                <textarea name="comment" rows="3" maxlength="1000" class="input rounded-lg px-3 py-2 font-normal" placeholder="Nima noto'g'ri ekanini qisqacha yozing"></textarea>
            </label>
        </div>
        <div class="sticky bottom-0 flex gap-2 border-t border-line bg-white px-4 pt-3 pb-[calc(1rem+env(safe-area-inset-bottom))] md:static md:border-0 md:px-6 md:pt-0 md:pb-6">
            <button type="button" data-dialog-close class="btn h-11 flex-1 rounded-xl border-line bg-white font-semibold text-muted hover:border-ink hover:text-ink">Bekor qilish</button>
            <button type="submit" class="btn h-11 flex-1 rounded-xl border-danger bg-danger font-semibold text-white hover:brightness-95"><i class="bi bi-flag"></i> Yuborish</button>
        </div>
    </form>
</dialog>
