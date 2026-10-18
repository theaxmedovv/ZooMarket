<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * "Parolni unutdim": email -> 6-digit code sent by mail (Gmail SMTP) -> new password.
 * Session keys: pw_reset_email (code requested for), pw_reset_verified (code accepted for).
 */
class PasswordResetController extends Controller
{
    /** Seconds before another code can be requested for the same email. */
    private const RESEND_SECONDS = 60;

    /** Codes one IP address may request per hour. */
    private const HOURLY_PER_IP = 10;

    public function showEmailForm()
    {
        return view('auth.forgot-password');
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $email = Str::lower($data['email']);

        return $this->dispatchCode($request, $email, 'email');
    }

    public function resendCode(Request $request): RedirectResponse
    {
        $email = $request->session()->get('pw_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        return $this->dispatchCode($request, $email, 'code');
    }

    public function showCodeForm(Request $request)
    {
        $email = $request->session()->get('pw_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-code', [
            'email' => $email,
            'minutes' => PasswordResetCode::CODE_MINUTES,
        ]);
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $email = $request->session()->get('pw_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $data = $request->validate(
            ['code' => ['required', 'digits:6']],
            ['code.required' => 'Tasdiqlash kodini kiriting.', 'code.digits' => 'Kod 6 ta raqamdan iborat bo\'lishi kerak.']
        );

        $record = PasswordResetCode::where('email', $email)->first();

        if (! $record || $record->isExpired()) {
            $record?->delete();

            return back()->withErrors(['code' => 'Kod muddati tugagan yoki so\'ralmagan. Yangi kod oling.']);
        }

        if ($record->attempts >= PasswordResetCode::MAX_ATTEMPTS) {
            $record->delete();

            return back()->withErrors(['code' => 'Juda ko\'p noto\'g\'ri urinish. Yangi kod oling.']);
        }

        if (! $record->matches($data['code'])) {
            $record->increment('attempts');
            $left = PasswordResetCode::MAX_ATTEMPTS - $record->attempts;

            return back()->withErrors([
                'code' => $left > 0
                    ? "Kod noto'g'ri. Yana {$left} ta urinish qoldi."
                    : 'Kod noto\'g\'ri. Urinishlar tugadi, yangi kod oling.',
            ]);
        }

        $record->update(['verified_at' => now()]);
        $request->session()->put('pw_reset_verified', $email);

        return redirect()->route('password.reset');
    }

    public function showResetForm(Request $request)
    {
        if (! $this->verifiedRecord($request)) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function reset(Request $request): RedirectResponse
    {
        $record = $this->verifiedRecord($request);

        if (! $record) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Avval emailingizga yuborilgan kodni tasdiqlang.']);
        }

        $data = $request->validate(
            ['password' => ['required', 'string', 'min:8', 'confirmed']],
            [
                'password.required' => 'Yangi parolni kiriting.',
                'password.min' => 'Parol kamida 8 ta belgidan iborat bo\'lishi kerak.',
                'password.confirmed' => 'Parollar bir-biriga mos kelmadi.',
            ]
        );

        $user = User::where('email', $record->email)->first();

        if (! $user) {
            $record->delete();

            return redirect()->route('password.request');
        }

        // Rotating remember_token and dropping stored sessions signs out every device that knew the old password.
        $user->forceFill([
            'password' => $data['password'],
            'remember_token' => Str::random(60),
        ])->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        $record->delete();
        $request->session()->forget(['pw_reset_email', 'pw_reset_verified']);

        return redirect()->route('login')
            ->with('success', 'Parolingiz yangilandi. Yangi parol bilan tizimga kiring.');
    }

    /**
     * The code row for the email whose code was accepted in this session, if it is still good to use.
     */
    private function verifiedRecord(Request $request): ?PasswordResetCode
    {
        $email = $request->session()->get('pw_reset_verified');

        if (! $email) {
            return null;
        }

        $record = PasswordResetCode::where('email', $email)->first();

        return $record?->canReset() ? $record : null;
    }

    /**
     * Mail a fresh code. Unknown emails get the very same answer as known ones, so the form
     * can't be used to find out who has an account.
     */
    private function dispatchCode(Request $request, string $email, string $errorField): RedirectResponse
    {
        $ipKey = 'pw-reset-ip:' . $request->ip();
        $emailKey = 'pw-reset-email:' . $email;

        if (RateLimiter::tooManyAttempts($ipKey, self::HOURLY_PER_IP)) {
            return back()->withErrors([$errorField => 'Juda ko\'p so\'rov yuborildi. Birozdan so\'ng qayta urinib ko\'ring.'])->withInput();
        }

        if (RateLimiter::tooManyAttempts($emailKey, 1)) {
            $seconds = RateLimiter::availableIn($emailKey);

            return back()->withErrors([$errorField => "Yangi kodni {$seconds} soniyadan so'ng so'rashingiz mumkin."])->withInput();
        }

        RateLimiter::hit($ipKey, 3600);
        RateLimiter::hit($emailKey, self::RESEND_SECONDS);

        $user = User::where('email', $email)->first();

        if ($user) {
            $code = PasswordResetCode::issue($email);

            try {
                Mail::to($user->email)->send(new PasswordResetCodeMail($code, $user->name));
            } catch (\Throwable $e) {
                report($e);
                PasswordResetCode::where('email', $email)->delete();
                RateLimiter::clear($emailKey);

                return back()->withErrors([$errorField => 'Xatni yuborib bo\'lmadi. Birozdan so\'ng qayta urinib ko\'ring.'])->withInput();
            }
        }

        $request->session()->put('pw_reset_email', $email);
        $request->session()->forget('pw_reset_verified');

        return redirect()->route('password.code')
            ->with('info', 'Agar bu email ro\'yxatdan o\'tgan bo\'lsa, unga tasdiqlash kodi yuborildi.');
    }
}
