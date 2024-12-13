<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle($request, Closure $next, $role)
{
    if (!auth()->check()) {
        return redirect('/login');
    }

    $user = auth()->user();

    // Memeriksa apakah role pengguna sesuai dengan string (contoh: '1' untuk 'dokter')
    if ($user->role !== (string) $role) {
        return redirect()->back()->withErrors(['role' => 'Anda tidak memiliki akses sebagai ' . $role . '.']);
    }

    return $next($request);
}

}

