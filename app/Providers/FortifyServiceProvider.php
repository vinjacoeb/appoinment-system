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
        // Custom login response based on user role
        app()->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    // Check if the user is authenticated via a specific guard
                    if (auth()->guard('dokter')->check()) {
                        return redirect()->intended('/dashboard/dokter'); // Redirect to doctor dashboard
                    } elseif (auth()->guard('pasien')->check()) {
                        return redirect()->intended('/dashboard/pasien'); // Redirect to patient dashboard
                    } elseif (auth()->guard('admin')->check()) {
                        return redirect()->intended('/dashboard/admin'); // Redirect to admin dashboard
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
        // Register Fortify actions
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Define the login view based on the 'type' (pasien, dokter, admin)
        Fortify::loginView(function (Request $request) {
            $type = $request->get('type', 'pasien');
            return Inertia::render("Auth/Login" . ucfirst($type), [
                'type' => $type,
            ]);
        });

        // Define the registration view for patients
        Fortify::registerView(fn() => Inertia::render('Auth/Register'));

        // Rate limit login attempts based on IP address and username
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())) . '|' . $request->ip()
            );
            return Limit::perMinute(5)->by($throttleKey);
        });

        // Rate limit two-factor authentication attempts
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Authenticate the user using the selected guard
        Fortify::authenticateUsing(function (Request $request) {
            $credentials = $request->only('email', 'password');
            // Debugging session before attempting login
            \Log::info('Guard Attempt: ', ['guard' => $request->get('guard', 'pasien')]);

            // Try to authenticate as pasien
            if (auth()->guard('pasien')->attempt($credentials)) {
                \Log::info('Pasien authenticated');
                return auth()->guard('pasien')->user();
            }

            // Try to authenticate as dokter
            if (auth()->guard('dokter')->attempt($credentials)) {
                return auth()->guard('dokter')->user();
            }

            // Try to authenticate as admin
            if (auth()->guard('admin')->attempt($credentials)) {
                return auth()->guard('admin')->user();
            }

            // Return null if authentication fails
            return null;
        });
    }
}
