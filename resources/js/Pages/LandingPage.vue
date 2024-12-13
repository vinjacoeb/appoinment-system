
<template>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <header class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
          <div class="text-2xl font-semibold">Appointment System</div>
          <nav>
            <Link href="/" class="text-lg px-4 py-2 hover:bg-blue-500 rounded">Home</Link>
            <Link href="/login/pasien" class="text-lg px-4 py-2 hover:bg-blue-500 rounded">Login Pasien</Link>
            <Link href="/login/dokter" class="text-lg px-4 py-2 hover:bg-blue-500 rounded">Login Dokter</Link>
          </nav>
        </div>
      </header>
  
      <!-- Main Content -->
      <main class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-8">Selamat Datang di Sistem Janji Temu</h1>
            <p class="text-xl text-gray-600 mb-12">Tentukan jadwal dengan dokter atau kelola janji temu Anda dengan mudah.</p>
  
            <!-- Kartu Login -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">

              <!-- Kartu Login Pasien -->
              <a href="/login/pasien" class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-transform transform hover:scale-105">
                <h2 class="text-2xl font-semibold text-center text-blue-600 mb-4">Login Pasien</h2>
                <p class="text-center text-gray-600 mb-6">Silakan login untuk membuat janji temu dengan dokter.</p>
                <div class="w-full bg-blue-600 text-white text-center py-3 rounded-md hover:bg-blue-700">
                    Masuk Sebagai Pasien
                </div>
                </a>

  
              <!-- Kartu Login Dokter -->
              <a href="/login/dokter" class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-transform transform hover:scale-105">
                <h2 class="text-2xl font-semibold text-center text-green-600 mb-4">Login Dokter</h2>
                <p class="text-center text-gray-600 mb-6">Silakan login untuk mengelola jadwal dan janji temu pasien.</p>
                <!-- Tombol Masuk Dokter -->
                <div class="w-full bg-green-600 text-white text-center py-3 rounded-md hover:bg-green-700">
                  Masuk Sebagai Dokter
                </div>
            </a>

            <!-- Kartu Login Pasien -->
            <a href="/login/admin" class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-transform transform hover:scale-105">
                <h2 class="text-2xl font-semibold text-center text-blue-600 mb-4">Login Admin</h2>
                <div class="w-full bg-blue-600 text-white text-center py-3 rounded-md hover:bg-blue-700">
                    Masuk
                </div>
                </a>
            </div>
          </div>
        </div>
      </main>
  
      <!-- Footer -->
      <footer class="bg-gray-800 text-white py-6">
        <div class="max-w-7xl mx-auto px-6 text-center">
          <p>&copy; 2024 Sistem Janji Temu. All rights reserved.</p>
          <p>Created by <a href="#" class="text-blue-400">Your Company</a></p>
        </div>
      </footer>
    </div>
  </template>
  
  <script>
  import { Link } from '@inertiajs/inertia-vue3';
  
  export default {
    name: 'LandingPage'
  };
  </script>
  
  <style scoped>
  /* Menambahkan gaya CSS agar kartu lebih terlihat dan responsif */
  .bg-white {
    display: block;
    max-width: 350px;  /* Tentukan lebar kartu */
    margin: 0 auto;    /* Posisikan kartu di tengah */
  }
  .grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Pastikan grid responsif */
  }
  </style>
  