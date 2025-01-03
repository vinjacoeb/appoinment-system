<script setup>
import { ref } from 'vue';
import axios from 'axios';

import AppMenuItem from './AppMenuItem.vue';

const model = ref([
    {
        label: 'Home',
        items: [{ label: 'Dashboard Pasien', icon: 'pi pi-fw pi-home', to: '/dashboard/pasien' },
                { label: 'Daftar Poli', icon: 'pi pi-fw pi-table', to: '/dashboard/daftar-poli' },
                { label: 'Riwayat', icon: 'pi pi-fw pi-book', to: '/dashboard/riwayat' },
                { label: 'Logout', icon: 'pi pi-fw pi-power-off', command: () => logout() } // Add logout button
        ]
    }
]);

// Logout function
const logout = async () => {
    try {
        // Send a POST request to the logout route
        await axios.post('/logout-pasien');

        // Show a SweetAlert notification
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Logout',
            text: 'Anda telah berhasil keluar.',
            timer: 2000,
            showConfirmButton: false
        });

        // Redirect to the login page
        window.location.href = '/';
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Logout',
            text: 'Terjadi kesalahan saat logout. Silakan coba lagi.',
        });
    }
};
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
