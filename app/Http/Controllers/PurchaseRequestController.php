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

        $query = PurchaseRequest::with(['animal.category', 'animal.user', 'chat'])
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
                Rule::exists('posts', 'id'),
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

                if ($animal->status === 'sold' || $animal->totalAvailableCount() <= 0) {
                    throw new \RuntimeException("Ushbu hayvon allaqachon sotilgan yoki mavjud emas.");
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

        $query = PurchaseRequest::with(['user', 'animal', 'chat'])
            ->whereHas('animal', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });

        if (in_array($status, ['pending', 'approved', 'sold', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => PurchaseRequest::whereHas('animal', fn($q) => $q->where('user_id', $userId))->count(),
            'pending' => PurchaseRequest::where('status', 'pending')->whereHas('animal', fn($q) => $q->where('user_id', $userId))->count(),
            'approved' => PurchaseRequest::where('status', 'approved')->whereHas('animal', fn($q) => $q->where('user_id', $userId))->count(),
            'sold' => PurchaseRequest::where('status', 'sold')->whereHas('animal', fn($q) => $q->where('user_id', $userId))->count(),
            'rejected' => PurchaseRequest::where('status', 'rejected')->whereHas('animal', fn($q) => $q->where('user_id', $userId))->count(),
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
            $purchaseRequest->update(['status' => 'sold']);

            // If animal is out of inventory or request didn't specify quantity (legacy), mark post as sold
            if ($purchaseRequest->quantity === null || $animal->quantity <= 0 || $animal->totalAvailableCount() <= 0) {
                $animal->update(['status' => 'sold']);

                PurchaseRequest::query()
                    ->where('animal_id', $purchaseRequest->animal_id)
                    ->where('id', '!=', $purchaseRequest->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->update(['status' => 'rejected']);
            }
        });

        return back()->with('success', 'Buyurtma sotilgan deb belgilandi.');
    }

    public function returnListing(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $animal = $purchaseRequest->animal;

        if (! $animal || $animal->user_id !== $request->user()->id) {
            return back()->withErrors(['purchase_request' => 'Ushbu amalni bajarish uchun ruxsat yo\'q.']);
        }

        DB::transaction(function () use ($purchaseRequest, $animal) {
            $animal = Post::where('id', $animal->id)->lockForUpdate()->first();

            // If not already rejected, restore inventory
            if ($purchaseRequest->status !== 'rejected') {
                $reqQty = max(1, (int) $purchaseRequest->quantity);
                $restoreGender = $purchaseRequest->gender ?: ($animal->gender === 'female' ? 'female' : 'male');
                if ($restoreGender === 'female') {
                    $animal->female_quantity = $animal->female_quantity + $reqQty;
                } else {
                    $animal->male_quantity = $animal->male_quantity + $reqQty;
                }
                $animal->quantity = $animal->male_quantity + $animal->female_quantity;
            }

            if ($animal->quantity > 0) {
                $animal->status = 'active';
            }
            $animal->save();

            $purchaseRequest->update(['status' => 'rejected']);
        });

        return back()->with('success', 'E\'lon yana faol holatga qaytarildi va boshqa foydalanuvchilar uchun mavjud bo\'ldi.');
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
            $animal = Post::where('id', $animal->id)->lockForUpdate()->first();

            // Restore inventory
            $reqQty = max(1, (int) $purchaseRequest->quantity);
            $restoreGender = $purchaseRequest->gender ?: ($animal->gender === 'female' ? 'female' : 'male');
            if ($restoreGender === 'female') {
                $animal->female_quantity = $animal->female_quantity + $reqQty;
            } else {
                $animal->male_quantity = $animal->male_quantity + $reqQty;
            }

            $animal->quantity = $animal->male_quantity + $animal->female_quantity;
            if ($animal->quantity > 0 && $animal->status === 'sold') {
                $animal->status = 'active';
            }
            $animal->save();

            $purchaseRequest->update(['status' => 'rejected']);
        });

        return back()->with('success', 'So\'rov rad etildi va miqdor inventarga qaytarildi.');
    }
}
