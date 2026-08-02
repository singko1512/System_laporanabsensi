<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\JadwalMingguan;
use App\Models\PembimbingMagang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectAuthenticatedUser($request);
        }

        $loginRole = $request->query('role');
        $activeAuthMode = $request->query('mode') === 'register' && ! in_array($loginRole, ['admin', 'superadmin'], true)
            ? 'register'
            : 'login';

        return view('admin.login', [
            'loginRole' => $loginRole,
            'activeAuthMode' => $activeAuthMode,
            'bidangs' => Bidang::orderBy('nama', 'asc')->pluck('nama'),
            'bidangOptions' => Bidang::orderBy('nama', 'asc')->get(),
            'pembimbingOptions' => PembimbingMagang::orderBy('nama', 'asc')->get(),
            'title' => 'Login Akun',
            'action' => route('login'),
        ]);
    }

    public function showRegister(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectAuthenticatedUser($request);
        }

        return redirect()->route('login.form', ['mode' => 'register']);
    }

    public function showForgotPassword(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectAuthenticatedUser($request);
        }

        if ($request->boolean('reset')) {
            $request->session()->forget('password_reset_user_id');
        }

        $verifiedResetUser = null;
        if ($request->session()->has('password_reset_user_id')) {
            $verifiedResetUser = User::where('role', 'user')
                ->find($request->session()->get('password_reset_user_id'));

            if (! $verifiedResetUser) {
                $request->session()->forget('password_reset_user_id');
            }
        }

        return view('admin.login', [
            'loginRole' => null,
            'activeAuthMode' => 'forgot',
            'verifiedResetUser' => $verifiedResetUser,
            'bidangs' => Bidang::orderBy('nama', 'asc')->pluck('nama'),
            'bidangOptions' => Bidang::orderBy('nama', 'asc')->get(),
            'pembimbingOptions' => PembimbingMagang::orderBy('nama', 'asc')->get(),
            'title' => 'Lupa Password',
            'subtitle' => 'Masukkan nama dan email yang dipakai saat registrasi.',
            'action' => route('login'),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'expected_role' => ['nullable', Rule::in(['admin', 'superadmin'])],
        ], [
            'login.required' => 'Email atau ID admin wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $login = trim($credentials['login']);
        $expectedRole = $credentials['expected_role'] ?? null;
        $isEmailLogin = filter_var($login, FILTER_VALIDATE_EMAIL);

        if ($expectedRole) {
            $field = $isEmailLogin ? 'email' : 'username';
        } elseif ($isEmailLogin) {
            $field = 'email';
        } else {
            $adminAccount = User::where('username', $login)
                ->whereIn('role', ['admin', 'superadmin'])
                ->first();

            if (! $adminAccount) {
                return redirect()->back()
                    ->withInput($request->only('login'))
                    ->with('error_swal', 'Masukkan email peserta yang terdaftar atau ID admin yang valid.');
            }

            $field = 'username';
        }

        if (! Auth::attempt([$field => $login, 'password' => $credentials['password'], 'status_akun' => 'aktif'], $request->boolean('remember'))) {
            return redirect()->back()
                ->withInput($request->only('login'))
                ->with('error_swal', 'Akun atau password tidak valid.');
        }

        $request->session()->regenerate();

        if ($expectedRole && Auth::user()->role !== $expectedRole) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login.form', ['role' => $expectedRole])
                ->withInput($request->only('login'))
                ->with('error_swal', 'Gunakan akun ' . ($expectedRole === 'superadmin' ? 'Super Admin' : 'Admin') . ' untuk masuk ke halaman ini.');
        }

        session([
            'admin_authenticated' => in_array(Auth::user()->role, ['admin', 'superadmin'], true),
            'admin_role' => Auth::user()->role,
        ]);

        if (Auth::user()->role === 'superadmin') {
            return redirect()->route('admin.dashboard')->with('success_swal', 'Login Super Admin berhasil!');
        }

        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard', ['tab' => 'pegawai'])->with('success_swal', 'Login Admin berhasil!');
        }

        return redirect()->route('absensi.index')->with('success_swal', 'Login peserta magang berhasil!');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:md_user,email',
            'password' => 'required|string|min:6|confirmed',
            'bidang_id' => 'required|exists:md_bidang,id',
            'pembimbing_magang_id' => [
                'required',
                Rule::exists('md_pembimbing_magang', 'id')
                    ->where(fn ($query) => $query->where('bidang_id', $request->input('bidang_id'))),
            ],
            'tanggal_mulai_magang' => 'nullable|date',
            'tanggal_selesai_magang' => 'nullable|date|after_or_equal:tanggal_mulai_magang',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'bidang_id.required' => 'Bidang magang wajib dipilih.',
            'bidang_id.exists' => 'Bidang magang tidak valid.',
            'pembimbing_magang_id.required' => 'Pembimbing magang wajib dipilih.',
            'pembimbing_magang_id.exists' => 'Pembimbing magang tidak sesuai dengan bidang yang dipilih.',
            'tanggal_mulai_magang.date' => 'Tanggal mulai magang tidak valid.',
            'tanggal_selesai_magang.date' => 'Tanggal selesai magang tidak valid.',
            'tanggal_selesai_magang.after_or_equal' => 'Tanggal selesai magang harus sama atau setelah tanggal mulai.',
        ]);

        $bidang = Bidang::findOrFail($data['bidang_id']);
        $pembimbing = PembimbingMagang::findOrFail($data['pembimbing_magang_id']);

        $user = User::create([
            'nama' => $data['nama'],
            'username' => $this->generateUsername($data['nama'], $data['email']),
            'email' => $data['email'],
            'password' => $data['password'],
            'bidang_id' => $bidang->id,
            'bidang_magang' => $bidang->nama,
            'pembimbing_magang_id' => $pembimbing->id,
            'pembimbing_magang' => $pembimbing->nama,
            'tanggal_mulai_magang' => $data['tanggal_mulai_magang'] ?? null,
            'tanggal_selesai_magang' => $data['tanggal_selesai_magang'] ?? null,
            'role' => 'user',
            'status_akun' => 'aktif',
        ]);

        $user->jadwalMingguan()->create(JadwalMingguan::defaultSchedule());

        Auth::login($user);
        $request->session()->regenerate();

        session([
            'admin_authenticated' => false,
            'admin_role' => 'user',
        ]);

        return redirect()->route('absensi.index')->with('success_swal', 'Registrasi berhasil. Akun peserta magang sudah aktif.');
    }

    public function verifyForgotPasswordIdentity(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $nama = $this->normalizeName($data['nama']);
        $email = Str::lower(trim($data['email']));

        $user = User::where('role', 'user')
            ->whereRaw('LOWER(nama) = ?', [Str::lower($nama)])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            return redirect()->back()
                ->withInput($request->only('nama', 'email'))
                ->with('error_swal', 'Nama dan email tidak cocok dengan data registrasi peserta.');
        }

        $request->session()->put('password_reset_user_id', $user->id);

        return redirect()
            ->route('password.request')
            ->with('success_swal', 'Data peserta ditemukan. Silakan buat password baru.');
    }

    public function resetUserPassword(Request $request)
    {
        $userId = $request->session()->get('password_reset_user_id');
        $user = $userId ? User::where('role', 'user')->find($userId) : null;

        if (! $user) {
            return redirect()
                ->route('password.request')
                ->with('error_swal', 'Verifikasi nama dan email terlebih dahulu sebelum membuat password baru.');
        }

        $data = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);

        $user->update([
            'password' => $data['password'],
        ]);

        $request->session()->forget('password_reset_user_id');

        return redirect()
            ->route('login.form')
            ->withInput(['login' => $user->email])
            ->with('success_swal', 'Password berhasil diperbarui. Silakan masuk dengan password baru.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->forget(['admin_authenticated', 'admin_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success_swal', 'Logout berhasil.');
    }

    private function redirectAuthenticatedUser(Request $request)
    {
        $user = Auth::user();
        $requestedRole = $request->query('role');

        if (in_array($requestedRole, ['admin', 'superadmin'], true) && $user->role !== $requestedRole) {
            return redirect()->route($user->role === 'user' ? 'absensi.index' : 'admin.dashboard')
                ->with('error_swal', 'Anda sudah login sebagai ' . ucfirst($user->role) . '. Logout dulu untuk masuk sebagai role lain.');
        }

        if ($user->role === 'superadmin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard', ['tab' => 'pegawai']);
        }

        return redirect()->route('absensi.index');
    }

    private function generateUsername(string $name, string $email): string
    {
        $emailPrefix = Str::before($email, '@');
        $base = Str::of($emailPrefix ?: $name)
            ->lower()
            ->replaceMatches('/[^a-z0-9._-]+/', '.')
            ->trim('.-_')
            ->value();

        $base = $base !== '' ? $base : 'peserta';
        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    private function normalizeName(string $name): string
    {
        return trim(preg_replace('/\s+/', ' ', $name));
    }
}
