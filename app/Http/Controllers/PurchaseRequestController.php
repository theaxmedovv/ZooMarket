<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PurchaseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('user'), 403);

        $validated = $request->validate([
            'animal_id' => [
                'required',
                'integer',
                Rule::exists('posts', 'id'),
                Rule::unique('purchase_requests', 'animal_id')->where(fn ($query) => $query->where('user_id', $user->id)),
            ],
        ], [
            'animal_id.unique' => 'Siz bu hayvon uchun allaqachon so\'rov yuborgansiz.',
        ]);

        $animal = Post::findOrFail($validated['animal_id']);

        if ($animal->status === 'sold') {
            return back()->withErrors(['animal_id' => 'Ushbu hayvon allaqachon sotilgan.']);
        }

        PurchaseRequest::create([
            'user_id' => $user->id,
            'animal_id' => $animal->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'So\'rovingiz yuborildi');
    }

    public function index()
    {
        $requests = PurchaseRequest::with(['user', 'animal'])
            ->latest()
            ->paginate(20);

        return view('admin.purchase-requests', compact('requests'));
    }

    public function approve(PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $purchaseRequest->update(['status' => 'approved']);

        if ($purchaseRequest->animal) {
            $purchaseRequest->animal->update(['status' => 'sold']);
        }

        return back()->with('success', 'So\'rov tasdiqlandi.');
    }

    public function reject(PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $purchaseRequest->update(['status' => 'rejected']);

        return back()->with('success', 'So\'rov rad etildi.');
    }
}
