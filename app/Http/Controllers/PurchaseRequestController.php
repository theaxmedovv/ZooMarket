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
            'male_quantity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'female_quantity' => ['nullable', 'integer', 'min:0', 'max:100'],
            // Legacy single-gender form: gender + quantity
            'gender' => [
                'required_without_all:male_quantity,female_quantity',
                'nullable',
                'string',
                Rule::in(['male', 'female']),
            ],
            'quantity' => [
                'required_without_all:male_quantity,female_quantity',
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $animalId = (int) $validated['animal_id'];

        if (isset($validated['male_quantity']) || isset($validated['female_quantity'])) {
            $maleQty = (int) ($validated['male_quantity'] ?? 0);
            $femaleQty = (int) ($validated['female_quantity'] ?? 0);
        } else {
            $maleQty = $validated['gender'] === 'male' ? (int) $validated['quantity'] : 0;
            $femaleQty = $validated['gender'] === 'female' ? (int) $validated['quantity'] : 0;
        }

        if ($maleQty + $femaleQty < 1) {
            return back()->withErrors(['quantity' => 'Kamida 1 ta hayvon tanlang.']);
        }

        try {
            DB::transaction(function () use ($user, $animalId, $maleQty, $femaleQty) {
                $animal = Post::where('id', $animalId)->lockForUpdate()->firstOrFail();

                if ($animal->moderation_status !== 'approved' || in_array($animal->status, ['sold', 'archived'], true) || $animal->totalAvailableCount() <= 0) {
                    throw new \RuntimeException("Ushbu hayvon sotuvda mavjud emas yoki hali tasdiqlanmagan.");
                }

                if ($maleQty > $animal->availableMaleCount()) {
                    throw new \RuntimeException("Kechirasiz, erkak hayvonlar yetarli emas. Hozirda mavjud: {$animal->availableMaleCount()} ta.");
                }

                if ($femaleQty > $animal->availableFemaleCount()) {
                    throw new \RuntimeException("Kechirasiz, urg'ochi hayvonlar yetarli emas. Hozirda mavjud: {$animal->availableFemaleCount()} ta.");
                }

                // Deduct the selected animals from the listing; the rest stays on sale
                $animal->male_quantity -= $maleQty;
                $animal->female_quantity -= $femaleQty;
                $animal->quantity = $animal->male_quantity + $animal->female_quantity;

                // Everything is reserved by open requests: hide from sale, but keep the
                // listing alive until those requests are sold or rejected
                if ($animal->quantity <= 0) {
                    $animal->status = 'reserved';
                }
                $animal->save();

                PurchaseRequest::create([
                    'user_id' => $user->id,
                    'animal_id' => $animal->id,
                    'male_quantity' => $maleQty,
                    'female_quantity' => $femaleQty,
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

        if (! in_array($purchaseRequest->status, ['pending', 'approved'], true)) {
            return back()->withErrors(['purchase_request' => 'Faqat faol so\'rovni sotilgan deb belgilash mumkin.']);
        }

        $archived = DB::transaction(function () use ($purchaseRequest, $animal) {
            $animal = Post::withTrashed()->where('id', $animal->id)->lockForUpdate()->first();

            // Only the animals selected in this request are sold; their stock was
            // already deducted when the request was made
            $purchaseRequest->update(['status' => 'sold']);
            $purchaseRequest->chat?->update(['closed_at' => now()]);

            // Archive the listing only once nothing remains: no stock left and
            // no other open request still holding a reservation
            $hasOpenRequests = PurchaseRequest::query()
                ->where('animal_id', $animal->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if (! $animal->isArchived() && $animal->totalAvailableCount() <= 0 && ! $hasOpenRequests) {
                $animal->update(['status' => 'sold']);
                return true;
            }

            return false;
        });

        if ($archived) {
            return back()->with('success', 'So\'rov sotilgan deb belgilandi. E\'londagi barcha hayvonlar sotilgani uchun e\'lon arxivga o\'tkazildi.');
        }

        return back()->with('success', 'So\'rov sotilgan deb belgilandi. Qolgan hayvonlar e\'londa qoldi.');
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

        if ($purchaseRequest->status === 'sold') {
            return back()->withErrors(['purchase_request' => 'Sotilgan so\'rovni rad etib bo\'lmaydi.']);
        }

        DB::transaction(function () use ($purchaseRequest, $animal) {
            $animal = Post::withTrashed()->where('id', $animal->id)->lockForUpdate()->first();

            // Return the reserved animals to the listing, unless it is archived or deleted
            if ($animal && ! $animal->isArchived()) {
                $wasFullyReserved = $animal->totalAvailableCount() <= 0;

                $animal->male_quantity += (int) $purchaseRequest->male_quantity;
                $animal->female_quantity += (int) $purchaseRequest->female_quantity;
                $animal->quantity = $animal->male_quantity + $animal->female_quantity;

                if ($wasFullyReserved && $animal->status === 'reserved' && $animal->quantity > 0) {
                    $animal->status = 'active';
                }
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
