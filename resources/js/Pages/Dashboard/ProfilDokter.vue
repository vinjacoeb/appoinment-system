<template>
    <app-layout>
      <div>
        <div class="card">
          <h5>Profil Dokter</h5>
          <form @submit.prevent="saveProfile">
            <div class="p-fluid">
              <div class="field">
                <label for="nama">Nama</label>
                <InputText v-model="profile.nama" id="nama" placeholder="Masukkan nama" required />
              </div>
  
              <div class="field">
                <label for="email">Email</label>
                <InputText v-model="profile.email" id="email" placeholder="Masukkan email" type="email" required />
              </div>
  
              <div class="field">
                <label for="password">Password</label>
                <Password v-model="profile.password" id="password" placeholder="Masukkan password" toggleMask required />
              </div>
  
              <div class="field">
                <label for="confirmPassword">Konfirmasi Password</label>
                <Password v-model="profile.confirmPassword" id="confirmPassword" placeholder="Masukkan ulang password" toggleMask required />
              </div>
  
              <div class="field">
                <label for="alamat">Alamat</label>
                <InputText v-model="profile.alamat" id="alamat" placeholder="Masukkan alamat" required />
              </div>
  
              <div class="field">
                <label for="no_hp">No. HP</label>
                <InputText v-model="profile.no_hp" id="no_hp" placeholder="Masukkan nomor HP" type="tel" required />
              </div>
  
              <div class="field">
                <Button label="Simpan" type="submit" class="custom-blue-button" />
              </div>
            </div>
          </form>
        </div>
      </div>
    </app-layout>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import AppLayout from '@/primevue/layout/AppLayoutDokter.vue';
  import InputText from 'primevue/inputtext';
  import Password from 'primevue/password';
  import Button from 'primevue/button';
  import Swal from 'sweetalert2';
  import axios from 'axios';
  
  const profile = ref({
    nama: '',
    email: '',
    password: '',
    confirmPassword: '',
    alamat: '',
    no_hp: ''
  });
  
  const fetchProfile = async () => {
    try {
      const response = await axios.get('/profil-dokter');  // Gantilah dengan ID dokter yang sesuai
      const dokter = response.data;
      profile.value = {
        nama: dokter.nama,
        email: dokter.email,
        password: '',  // Jangan tampilkan password
        confirmPassword: '',  // Jangan tampilkan konfirmasi password
        alamat: dokter.alamat,
        no_hp: dokter.no_hp
      };
    } catch (error) {
      console.error('Error fetching profile:', error);
      Swal.fire('Error', 'Gagal mengambil profil dokter.', 'error');
    }
  };
  
  const saveProfile = async () => {
  if (profile.value.password !== profile.value.confirmPassword) {
    Swal.fire('Error', 'Password dan konfirmasi password tidak sesuai.', 'error');
    return;
  }

  try {
    // Kirim data ke API untuk diperbarui
    const response = await axios.post('/update-profil', {
      nama: profile.value.nama,
      email: profile.value.email,
      alamat: profile.value.alamat,
      no_hp: profile.value.no_hp,
      password: profile.value.password, // Kirim password hanya jika ada
      password_confirmation: profile.value.confirmPassword, // Pastikan password_confirmation dikirim
    });

    // Tanggapan sukses
    Swal.fire('Sukses', response.data.message, 'success');
  } catch (error) {
    console.error('Error saving profile:', error);
    Swal.fire('Error', 'Gagal menyimpan profil.', 'error');
  }
};

  
  // Ambil data dokter saat komponen dimuat
  onMounted(() => {
    fetchProfile();
  });
  </script>
  
  <style scoped>
  .card {
    margin-top: 20px;
  }
  .custom-blue-button {
    background-color: #007bff;
    color: white;
  }
  .custom-blue-button:hover {
    background-color: #0056b3;
  }
  </style>
  