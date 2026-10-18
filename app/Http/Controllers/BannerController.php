<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Services\DatabaseImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Home page slides (adverts shown in the rotating hero banner).
 * Every user manages their own banners; the main admin sees all of them and approves or rejects each
 * one. A banner shows on the home page only while it is approved and switched on.
 */
class BannerController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected'];

    public function index(Request $request)
    {
        $isAdmin = $this->isAdmin($request);
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null;

        $banners = Banner::with(['creator', 'moderator'])
            ->when(! $isAdmin, fn ($q) => $q->where('created_by', $request->user()->id))
            ->when($status, fn ($q) => $q->where('moderation_status', $status))
            ->ordered()
            ->get();

        $counts = Banner::query()
            ->when(! $isAdmin, fn ($q) => $q->where('created_by', $request->user()->id))
            ->selectRaw('moderation_status, count(*) as total')
            ->groupBy('moderation_status')
            ->pluck('total', 'moderation_status');

        return view('banners.index', compact('banners', 'status', 'counts', 'isAdmin'));
    }

    public function create()
    {
        return view('banners.form', ['banner' => new Banner(['color' => array_key_first(Banner::COLORS), 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $isAdmin = $this->isAdmin($request);
        $data = $this->validated($request, $isAdmin);

        if ($request->hasFile('image')) {
            $data['image'] = DatabaseImageService::store($request->file('image'), 'banners');
        }

        Banner::create($data + [
            'created_by' => $request->user()->id,
            // The admin's own banners need no review; everyone else's wait for the admin
            'moderation_status' => $isAdmin ? 'approved' : 'pending',
            'moderated_at' => $isAdmin ? now() : null,
            'moderated_by' => $isAdmin ? $request->user()->id : null,
        ]);

        return redirect()->route('banners.index')->with(
            $isAdmin ? 'success' : 'info',
            $isAdmin
                ? "Banner qo'shildi va bosh sahifada ko'rinadi."
                : "Banner tekshiruvda. Tasdiqlangach bosh sahifada ko'rinadi."
        );
    }

    public function edit(Request $request, Banner $banner)
    {
        $this->authorizeBanner($request, $banner);

        return view('banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $this->authorizeBanner($request, $banner);

        $isAdmin = $this->isAdmin($request);
        $data = $this->validated($request, $isAdmin, $banner);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            DatabaseImageService::delete($banner->image);
            $data['image'] = $request->hasFile('image')
                ? DatabaseImageService::store($request->file('image'), 'banners')
                : null;
        }

        // A user's edit changes what the admin approved, so it goes back for review
        if (! $isAdmin) {
            $data += [
                'moderation_status' => 'pending',
                'moderation_reason' => null,
                'moderated_at' => null,
                'moderated_by' => null,
            ];
        }

        $banner->update($data);

        return redirect()->route('banners.index')->with(
            $isAdmin ? 'success' : 'info',
            $isAdmin ? 'Banner yangilandi.' : "Banner yangilandi va qayta tekshiruvga yuborildi."
        );
    }

    public function toggle(Request $request, Banner $banner): RedirectResponse
    {
        $this->authorizeBanner($request, $banner);

        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('success', $banner->is_active ? "Banner yoqildi." : "Banner o'chirildi — bosh sahifada ko'rinmaydi.");
    }

    public function destroy(Request $request, Banner $banner): RedirectResponse
    {
        $this->authorizeBanner($request, $banner);

        DatabaseImageService::delete($banner->image);
        $banner->delete();

        return redirect()->route('banners.index')->with('success', "Banner o'chirib tashlandi.");
    }

    public function approve(Request $request, Banner $banner): RedirectResponse
    {
        $banner->update([
            'moderation_status' => 'approved',
            'moderation_reason' => null,
            'moderated_at' => now(),
            'moderated_by' => $request->user()->id,
        ]);

        return back()->with('success', "\"{$banner->label()}\" tasdiqlandi" . ($banner->is_active ? ' va bosh sahifada ko\'rinadi.' : ', lekin egasi uni o\'chirib qo\'ygan.'));
    }

    public function reject(Request $request, Banner $banner): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'reason.required' => "Rad etish sababini yozing — u banner egasiga ko'rsatiladi.",
            'reason.min' => "Sabab kamida 5 ta belgidan iborat bo'lsin.",
        ]);

        $banner->update([
            'moderation_status' => 'rejected',
            'moderation_reason' => $data['reason'],
            'moderated_at' => now(),
            'moderated_by' => $request->user()->id,
        ]);

        return back()->with('success', "\"{$banner->label()}\" rad etildi. Sabab banner egasiga ko'rsatiladi.");
    }

    private function isAdmin(Request $request): bool
    {
        return $request->user()->hasRole('admin');
    }

    /**
     * The admin may touch any banner; everyone else only their own.
     */
    private function authorizeBanner(Request $request, Banner $banner): void
    {
        abort_unless($this->isAdmin($request) || $banner->created_by === $request->user()->id, 403);
    }

    private function validated(Request $request, bool $isAdmin, ?Banner $banner = null): array
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:80'],
            // Only the main admin may place a picture-only banner; everyone else must write a title
            'title' => [$isAdmin ? 'nullable' : 'required', 'string', 'max:80'],
            'offer' => ['nullable', 'string', 'max:140'],
            // Site paths ("/?category_id=...") or full http(s) links only — never javascript: and the like.
            'link' => ['nullable', 'string', 'max:255', 'regex:#^(https?://[^\s]+|/(?!/)[^\s]*)$#i'],
            'button_text' => ['nullable', 'string', 'max:30'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'color' => ['required', Rule::in(array_keys(Banner::COLORS))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ], [
            'title.required' => 'Sarlavhani yozing.',
            'link.regex' => "Havola \"/\" bilan (sayt ichidagi sahifa) yoki \"https://\" bilan boshlansin.",
            'image.max' => "Rasm hajmi 4 MB dan oshmasin.",
            'image.mimes' => 'Rasm JPG, PNG yoki WEBP formatida bo\'lsin.',
        ]);

        unset($data['image']);

        // The admin's banners may be just a picture, but an empty banner is useless: it needs a title or a picture
        $keepsImage = $banner?->image && ! $request->boolean('remove_image');
        if ($isAdmin && ! filled($data['title'] ?? null) && ! $request->hasFile('image') && ! $keepsImage) {
            throw ValidationException::withMessages(['title' => "Sarlavha yozing yoki rasm yuklang — banner bo'sh bo'lishi mumkin emas."]);
        }

        $data['is_active'] = $request->boolean('is_active');

        // Order on the home page is the admin's call
        if ($isAdmin) {
            $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        } else {
            unset($data['sort_order']);
        }

        return $data;
    }
}
