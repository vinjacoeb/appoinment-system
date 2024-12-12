<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.Pasien::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'alamat' => 'nullable|string|max:255',   // Tidak required
            'no_ktp' => 'nullable|string|max:20',    // Tidak required
            'no_hp' => 'nullable|string|max:15',     // Tidak required
            'no_rm' => 'nullable|string|max:20',     // Tidak required
        ]);

        // Ambil tanggal hari ini dalam format YYYYMMDD
        $date = Carbon::now()->format('Ymd');

        // Cari jumlah pasien yang sudah terdaftar pada tanggal tersebut
        $count = Pasien::where('no_rm', 'like', "$date%")->count();

        // Nomor urut pasien, mulai dari 001
        $no_rm = $date . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // Buat pasien baru dan simpan data
        $user = Pasien::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'no_rm' => $no_rm, // Menyimpan no_rm otomatis
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Setelah login, arahkan pasien ke dashboard pasien
        return redirect('/dashboard/pasien');

    }
}
