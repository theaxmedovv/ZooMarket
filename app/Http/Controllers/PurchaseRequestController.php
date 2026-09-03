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
        $status = $request->query('status');
        $userId = $request->user()->id;

        $query = PurchaseRequest::with(['animal' => fn($q) => $q->withTrashed()->with(['category', 'user']), 'chat'])
            ->where('user_id', $userId);

        if (in_array($status, ['pending', 'approved', 'sold', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total' => PurchaseRequest::where('user_id', $userId)->count(),
            'pending' => PurchaseRequest::where('user_id', $userId)->where('status', 'pending')->count(),
            'approved' => PurchaseRequest::where('user_id', $userId)->where('status', 'approved')->count(),
            'sold' => PurchaseRequest::where('user_id', $userId)->where('status', 'sold')->count(),
            'rejected' => PurchaseRequest::where('user_id', $userId)->where('status', 'rejected')->count(),
        ];

        return view('profile.purchase-requests', compact('requests', 'stats', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('user'), 403);

        $validated = $request->validate([
            'animal_id' => [
                'required',
                'integer',
                Rule::exists('posts', 'id')->whereNull('deleted_at'),
            ],
            'gender' => [
                'required',
                'string',
                Rule::in(['male', 'female']),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $animalId = (int) $validated['animal_id'];
        $gender = $validated['gender'];
        $requestedQty = (int) $validated['quantity'];

        try {
            DB::transaction(function () use ($user, $animalId, $gender, $requestedQty) {
                $animal = Post::where('id', $animalId)->lockForUpdate()->firstOrFail();

                if ($animal->moderation_status !== 'approved' || in_array($animal->status, ['sold', 'archived'], true) || $animal->totalAvailableCount() <= 0) {
                    throw new \RuntimeException("Ushbu hayvon sotuvda mavjud emas yoki hali tasdiqlanmagan.");
                }

                $available = $gender === 'male' ? $animal->availableMaleCount() : $animal->availableFemaleCount();
                if ($requestedQty > $available) {
                    $genderName = $gender === 'male' ? 'erkak' : "urg'ochi";
                    throw new \RuntimeException("Kechirasiz, tanlangan jins ({$genderName}) bo'yicha yetarli miqdor mavjud emas. Hozirda mavjud: {$available} ta.");
                }

                // Deduct inventory
                if ($gender === 'male') {
                    $animal->male_quantity = max(0, $animal->male_quantity - $requestedQty);
                } else {
                    $animal->female_quantity = max(0, $animal->female_quantity - $requestedQty);
                }

                $animal->quantity = $animal->male_quantity + $animal->female_quantity;
                if ($animal->quantity <= 0) {
                    $animal->status = 'sold';
                }
                $animal->save();

                PurchaseRequest::create([
                    'user_id' => $user->id,
                    'animal_id' => $animal->id,
                    'gender' => $gender,
                    'quantity' => $requestedQty,
                    'status' => 'pending',
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }

        return back()->with('success', 'So\'rovingiz muvaffaqiyatli yuborildi!');
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $userId = $request->user()->id;

        $query = PurchaseRequest::with(['user', 'animal' => fn($q) => $q->withTrashed(), 'chat'])
            ->whereHas('animal', function ($q) use ($userId) {
                $q->withTrashed()->where('user_id', $userId);
            });

        if (in_array($status, ['pending', 'approved', 'sold', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => PurchaseRequest::whereHas('animal', fn($q) => $q->withTrashed()->where('user_id', $userId))->count(),
            'pending' => PurchaseRequest::where('status', 'pending')->whereHas('animal', fn($q) => $q->withTrashed()->where('user_id', $userId))->count(),
            'approved' => PurchaseRequest::where('status', 'approved')->whereHas('animal', fn($q) => $q->withTrashed()->where('user_id', $userId))->count(),
            'sold' => PurchaseRequest::where('status', 'sold')->whereHas('animal', fn($q) => $q->withTrashed()->where('user_id', $userId))->count(),
            'rejected' => PurchaseRequest::where('status', 'rejected')->whereHas('animal', fn($q) => $q->withTrashed()->where('user_id', $userId))->count(),
        ];

        return view('admin.purchase-requests', compact('requests', 'stats', 'status'));
    }

    public function soldAnimals(Request $request)
    {
        return redirect()->route('admin.archive.index');
    }

    public function approve(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $animal = $purchaseRequest->animal;

        if (! $animal || $animal->user_id !== $request->user()->id) {
            return back()->withErrors(['purchase_request' => 'Ushbu amalni bajarish uchun ruxsat yo\'q.']);
        }

        DB::transaction(function () use ($purchaseRequest) {
            $purchaseRequest->update(['status' => 'approved']);
        });

        Chat::firstOrCreate(
            ['purchase_request_id' => $purchaseRequest->id],
            [
                'post_id'   => $purchaseRequest->animal_id,
                'buyer_id'  => $purchaseRequest->user_id,
                'seller_id' => $animal->user_id,
            ]
        );

        return back()->with('success', 'So\'rov tasdiqlandi. Xaridor va sotuvchi o\'rtasida chat yaratildi.');
    }

    public function markSold(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $animal = $purchaseRequest->animal;

        if (! $animal || $animal->user_id !== $request->user()->id) {
            return back()->withErrors(['purchase_request' => 'Ushbu amalni bajarish uchun ruxsat yo\'q.']);
        }

        DB::transaction(function () use ($purchaseRequest, $animal) {
            // 1. Mark this purchase request as sold
            $purchaseRequest->update(['status' => 'sold']);

            // 2. Partial Sale -> Archive: Unconditionally move listing to Archive (status = 'sold')
            $animal->update(['status' => 'sold']);

            // 3. Reject all other active requests (pending, approved) for this listing
            PurchaseRequest::query()
                ->where('animal_id', $animal->id)
                ->where('id', '!=', $purchaseRequest->id)
                ->whereIn('status', ['pending', 'approved'])
                ->update(['status' => 'rejected']);

            // 4. Close and deactivate all chats for this listing
            Chat::query()
                ->where('post_id', $animal->id)
                ->update(['closed_at' => now()]);
        });

        return back()->with('success', 'E\'lon sotilgan deb belgilandi va arxivga o\'tkazildi. Aloqador chatlar yopildi.');
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $animal = $purchaseRequest->animal;

        if (! $animal || $animal->user_id !== $request->user()->id) {
            return back()->withErrors(['purchase_request' => 'Ushbu amalni bajarish uchun ruxsat yo\'q.']);
        }

        if ($purchaseRequest->status === 'rejected') {
            return back()->with('success', 'Bu so\'rov allaqachon rad etilgan.');
        }

        DB::transaction(function () use ($purchaseRequest, $animal) {
            $animal = Post::withTrashed()->where('id', $animal->id)->lockForUpdate()->first();

            // Restore inventory only if the animal is not sold or archived, and not soft-deleted
            if ($animal && ! in_array($animal->status, ['sold', 'archived'], true) && ! $animal->trashed()) {
                $reqQty = max(1, (int) $purchaseRequest->quantity);
                $restoreGender = $purchaseRequest->gender ?: ($animal->gender === 'female' ? 'female' : 'male');
                if ($restoreGender === 'female') {
                    $animal->female_quantity = $animal->female_quantity + $reqQty;
                } else {
                    $animal->male_quantity = $animal->male_quantity + $reqQty;
                }

                $animal->quantity = $animal->male_quantity + $animal->female_quantity;
                $animal->save();
            }

            $purchaseRequest->update(['status' => 'rejected']);

            // Close chat for this rejected request if exists
            if ($purchaseRequest->chat) {
                $purchaseRequest->chat->update(['closed_at' => now()]);
            }
        });

        return back()->with('success', 'So\'rov rad etildi.');
    }
}
