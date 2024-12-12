<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Models\Dokter;
use App\Models\Pasien;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Custom login response berdasarkan role pengguna
        app()->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    // Check if the user is authenticated via a specific guard
                    if (auth()->guard('dokter')->check()) {
                        return redirect()->intended('/dashboard/dokter'); // Redirect to doctor dashboard
                    } elseif (auth()->guard('pasien')->check()) {
                        return redirect()->intended('/dashboard/pasien'); // Redirect to patient dashboard
                    }
        
                    return redirect('/login'); // Fallback if no guard is authenticated
                }
            };
        });
        
        
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrasi tindakan Fortify
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Tampilan login sesuai dengan 'type' (pasien, dokter, admin)
        Fortify::loginView(function (Request $request) {
            $type = $request->get('type', 'pasien'); // Default 'pasien'
            return Inertia::render("Auth/Login" . ucfirst($type), [
                'type' => $type,
            ]);
        });
        
        // Tampilan registrasi pasien
        Fortify::registerView(fn() => Inertia::render('Auth/Register'));

        // Batasan login per IP
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())) . '|' . $request->ip()
            );
            return Limit::perMinute(5)->by($throttleKey);
        });

        // Batasan autentikasi dua faktor
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Cukup satu kali autentikasi menggunakan Fortify
        Fortify::authenticateUsing(function (Request $request) {
            $credentials = $request->only('email', 'password');
            // Debugging session before attempting login
            \Log::info('Guard Attempt: ', ['guard' => $request->get('guard', 'pasien')]);

            if (auth()->guard('pasien')->attempt($credentials)) {
                \Log::info('Pasien authenticated');
                return auth()->guard('pasien')->user();
            }
        
            // Login dokter
            if (auth()->guard('dokter')->attempt($credentials)) {
                return auth()->guard('dokter')->user();
            }
        
            // Login admin
            if (auth()->guard('admin')->attempt($credentials)) {
                return auth()->guard('admin')->user();
            }
        
            return null;
        });
    }
}
