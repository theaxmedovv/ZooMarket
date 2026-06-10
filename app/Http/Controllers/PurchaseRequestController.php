<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Post;
use App\Models\PurchaseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseRequestController extends Controller
{
    public function userIndex(Request $request)
    {
        $requests = PurchaseRequest::with(['animal', 'chat'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('profile.purchase-requests', compact('requests'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('user'), 403);

        $validated = $request->validate([
            'animal_id' => [
                'required',
                'integer',
                Rule::exists('posts', 'id'),
            ],
        ]);

        $animal = Post::findOrFail($validated['animal_id']);

        if ($animal->status === 'sold') {
            return back()->withErrors(['animal_id' => 'Ushbu hayvon allaqachon sotilgan.']);
        }

        $existingRequest = PurchaseRequest::query()
            ->where('user_id', $user->id)
            ->where('animal_id', $animal->id)
            ->first();

        if ($existingRequest) {
            if ($existingRequest->status === 'pending') {
                return back()->withErrors(['animal_id' => 'Siz bu hayvon uchun allaqachon so\'rov yuborgansiz.']);
            }

            if ($existingRequest->status === 'approved') {
                return back()->withErrors(['animal_id' => 'Bu so\'rov allaqachon tasdiqlangan.']);
            }

            $existingRequest->update(['status' => 'pending']);

            return back()->with('success', 'So\'rovingiz qayta yuborildi');
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
        $requests = PurchaseRequest::with(['user', 'animal', 'chat'])
            ->latest()
            ->paginate(20);

        return view('admin.purchase-requests', compact('requests'));
    }

    public function soldAnimals()
    {
        $soldAnimals = PurchaseRequest::with(['user', 'animal'])
            ->where('status', 'approved')
            ->whereHas('animal', function ($query) {
                $query->where('status', 'sold');
            })
            ->latest('updated_at')
            ->paginate(20);

        return view('admin.sold-animals', compact('soldAnimals'));
    }

    public function approve(PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $animal = $purchaseRequest->animal;

        if (! $animal) {
            return back()->withErrors(['purchase_request' => 'Hayvon topilmadi.']);
        }

        if ($animal->status === 'sold' && $purchaseRequest->status !== 'approved') {
            return back()->withErrors(['purchase_request' => 'Bu hayvon allaqachon sotilgan.']);
        }

        DB::transaction(function () use ($purchaseRequest, $animal) {
            $purchaseRequest->update(['status' => 'approved']);

            PurchaseRequest::query()
                ->where('animal_id', $purchaseRequest->animal_id)
                ->where('id', '!=', $purchaseRequest->id)
                ->where('status', '!=', 'rejected')
                ->update(['status' => 'rejected']);

            $animal->update(['status' => 'sold']);
        });

        Chat::firstOrCreate(
            ['purchase_request_id' => $purchaseRequest->id],
            [
                'post_id'   => $purchaseRequest->animal_id,
                'buyer_id'  => $purchaseRequest->user_id,
                'seller_id' => $animal->user_id,
            ]
        );

        return back()->with('success', 'So\'rov tasdiqlandi. Chat yaratildi.');
    }

    public function reject(PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $purchaseRequest->update(['status' => 'rejected']);

        return back()->with('success', 'So\'rov rad etildi.');
    }
}
