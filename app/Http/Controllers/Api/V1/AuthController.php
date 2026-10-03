<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\VerifyEmailOtp;
use App\Mail\ForgotPasswordOtp;
use Carbon\Carbon;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $otp = sprintf("%06d", mt_rand(1, 999999));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_code' => $otp,
            'email_verification_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        Mail::to($user->email)->send(new VerifyEmailOtp($otp));

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan cek email Anda untuk kode verifikasi.',
            'user' => $user
        ], 201);
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email tidak terdaftar.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email sudah diverifikasi sebelumnya.'], 400);
        }

        if ($user->email_verification_code !== $request->otp) {
            return response()->json(['message' => 'Kode OTP salah.'], 400);
        }

        if (Carbon::now()->isAfter($user->email_verification_expires_at)) {
            return response()->json(['message' => 'Kode OTP telah kedaluwarsa.'], 400);
        }

        $user->email_verified_at = Carbon::now();
        $user->email_verification_code = null;
        $user->email_verification_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Email berhasil diverifikasi. Anda sekarang bisa login.']);
    }

    public function login(LoginRequest $request)
    {
        $login_type = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $user = User::where($login_type, $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Kredensial tidak valid.'], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json(['message' => 'Email belum diverifikasi.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $otp = sprintf("%06d", mt_rand(1, 999999));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $otp,
                'created_at' => Carbon::now()
            ]
        );

        Mail::to($request->email)->send(new ForgotPasswordOtp($otp));

        return response()->json(['message' => 'Kode pemulihan password telah dikirim ke email Anda.']);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetToken || $resetToken->token !== $request->otp) {
            return response()->json(['message' => 'Kode OTP salah atau tidak valid.'], 400);
        }

        // Cek kedaluwarsa (15 menit)
        if (Carbon::parse($resetToken->created_at)->addMinutes(15)->isPast()) {
            return response()->json(['message' => 'Kode OTP telah kedaluwarsa.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password berhasil direset. Silakan login dengan password baru.']);
    }
}
