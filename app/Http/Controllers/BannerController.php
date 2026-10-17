<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Services\DatabaseImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The main admin's home page slides: adverts shown in the rotating hero banner.
 */
class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::with('creator')->ordered()->get();

        return view('banners.index', compact('banners'));
    }

    public function create()
    {
        return view('banners.form', ['banner' => new Banner(['color' => array_key_first(Banner::COLORS), 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = DatabaseImageService::store($request->file('image'), 'banners');
        }

        Banner::create($data + ['created_by' => $request->user()->id]);

        return redirect()->route('banners.index')->with('success', "Banner qo'shildi va bosh sahifada ko'rinadi.");
    }

    public function edit(Banner $banner)
    {
        return view('banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            DatabaseImageService::delete($banner->image);
            $data['image'] = $request->hasFile('image')
                ? DatabaseImageService::store($request->file('image'), 'banners')
                : null;
        }

        $banner->update($data);

        return redirect()->route('banners.index')->with('success', 'Banner yangilandi.');
    }

    public function toggle(Banner $banner): RedirectResponse
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('success', $banner->is_active ? "Banner yoqildi — bosh sahifada ko'rinadi." : "Banner o'chirildi — bosh sahifada ko'rinmaydi.");
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        DatabaseImageService::delete($banner->image);
        $banner->delete();

        return redirect()->route('banners.index')->with('success', "Banner o'chirib tashlandi.");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:80'],
            'offer' => ['nullable', 'string', 'max:140'],
            // Site paths ("/posts?...") or full http(s) links only — never javascript: and the like.
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
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
