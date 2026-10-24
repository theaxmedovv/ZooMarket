<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Anyone signed in can flag a listing or a seller; the main admin reads the reports and decides.
 */
class ReportController extends Controller
{
    public function storeForPost(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->moderation_status === 'approved', 404);
        abort_if($post->user_id === $request->user()->id, 403, "O'z e'loningizga shikoyat qilib bo'lmaydi.");

        return $this->store($request, ['post_id' => $post->id], 'e\'lon');
    }

    public function storeForUser(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->hasRole('user'), 404);
        abort_if($user->is($request->user()), 403, "O'zingizga shikoyat qilib bo'lmaydi.");

        return $this->store($request, ['reported_user_id' => $user->id], 'foydalanuvchi');
    }

    private function store(Request $request, array $target, string $what): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', Rule::in(array_keys(config('zoomarket.report_reasons')))],
            'comment' => [Rule::requiredIf($request->input('reason') === 'other'), 'nullable', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Shikoyat sababini tanlang.',
            'reason.in' => 'Shikoyat sababini ro\'yxatdan tanlang.',
            'comment.required' => 'Boshqa sabab bo\'lsa, qisqacha yozib bering.',
        ]);

        $already = Report::open()
            ->where('reporter_id', $request->user()->id)
            ->where('post_id', $target['post_id'] ?? null)
            ->where('reported_user_id', $target['reported_user_id'] ?? null)
            ->exists();

        if ($already) {
            return back()->with('info', "Siz bu {$what} haqida allaqachon shikoyat yuborgansiz. Admin ko'rib chiqadi.");
        }

        Report::create($target + [
            'reporter_id' => $request->user()->id,
            'reason' => $data['reason'],
            'comment' => trim((string) ($data['comment'] ?? '')) ?: null,
        ]);

        \App\Support\Activity::record('report_sent', $what . ': ' . $data['reason']);

        return back()->with('success', "Shikoyatingiz adminga yuborildi. Rahmat!");
    }

    /**
     * Main admin: the reports, open ones first.
     */
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['open', 'resolved', 'dismissed'], true) ? $request->query('status') : 'open';

        $reports = Report::with(['reporter', 'post.user', 'reportedUser', 'handler'])
            ->where('status', $status)
            ->when($status === 'open', fn ($q) => $q->oldest(), fn ($q) => $q->latest('handled_at'))
            ->paginate(15)
            ->withQueryString();

        $counts = Report::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('reports.index', compact('reports', 'status', 'counts'));
    }

    public function resolve(Request $request, Report $report): RedirectResponse
    {
        return $this->close($request, $report, 'resolved', "Shikoyat ko'rib chiqildi.");
    }

    public function dismiss(Request $request, Report $report): RedirectResponse
    {
        return $this->close($request, $report, 'dismissed', 'Shikoyat asossiz deb belgilandi.');
    }

    /**
     * Take the reported listing down (it goes back to "rad etilgan" with the reason shown to its owner)
     * and close every open report about it.
     */
    public function hidePost(Request $request, Report $report): RedirectResponse
    {
        abort_unless($report->post_id !== null, 404);

        $post = $report->post;
        $reason = 'Shikoyat asosida olib tashlandi: ' . $report->reasonLabel();

        $post->update([
            'moderation_status' => 'rejected',
            'moderation_reason' => $reason,
            'moderated_at' => now(),
            'moderated_by' => $request->user()->id,
        ]);

        Report::open()->where('post_id', $post->id)->update([
            'status' => 'resolved',
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return back()->with('success', "\"{$post->title}\" olib tashlandi, shu e'londagi shikoyatlar yopildi.");
    }

    private function close(Request $request, Report $report, string $status, string $message): RedirectResponse
    {
        $report->update([
            'status' => $status,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return back()->with('success', $message);
    }
}
