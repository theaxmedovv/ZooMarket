<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Rate a seller you dealt with. Writing again replaces your earlier review.
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->hasRole('user'), 404);
        abort_unless($request->user()->canReview($user), 403, "Sotuvchini faqat u bilan savdo qilgan xaridor baholay oladi.");

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Bahoni tanlang (1 dan 5 gacha).',
            'rating.between' => 'Baho 1 dan 5 gacha bo\'lishi kerak.',
            'comment.max' => 'Sharh 1000 belgidan oshmasin.',
        ]);

        $existing = Review::where('seller_id', $user->id)->where('reviewer_id', $request->user()->id)->exists();

        Review::updateOrCreate(
            ['seller_id' => $user->id, 'reviewer_id' => $request->user()->id],
            ['rating' => $data['rating'], 'comment' => trim((string) ($data['comment'] ?? '')) ?: null],
        );

        return redirect()->to(route('sellers.show', $user) . '#sharhlar')
            ->with('success', $existing ? 'Sharhingiz yangilandi.' : 'Sharhingiz uchun rahmat!');
    }

    /**
     * Take your own review back (the main admin can remove any).
     */
    public function destroy(Request $request, Review $review): RedirectResponse
    {
        abort_unless($review->reviewer_id === $request->user()->id || $request->user()->hasRole('admin'), 403);

        $seller = $review->seller_id;
        $review->delete();

        return redirect()->to(route('sellers.show', $seller) . '#sharhlar')->with('success', "Sharh o'chirildi.");
    }
}
