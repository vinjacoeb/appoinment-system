<template>
    <app-layout>
        <div class="form-container">
            <h3 class="form-title">Pendaftaran Poli</h3>
            <form method="POST" action="{{ route('daftar-poli.store') }}">

                <!-- Nomor Rekam Medis -->
                <div class="p-field">
                    <label for="no_rm" class="form-label">Nomor Rekam Medis</label>
                    <input
                        type="text"
                        :value="pasien.no_rm"
                        name="no_rm"
                        id="no_rm"
                        readonly
                        class="form-input"
                    />
                </div>

                <!-- Pilih Poli -->
                <div class="p-field">
                    <label for="poli" class="form-label">Pilih Poli</label>
                    <select v-model="form.id_poli" name="id_poli" class="form-dropdown" id="poli" required @change="fetchJadwal">
                        <option value="" disabled>Pilih Poli</option>
                        <option v-for="poli in poliOptions" :key="poli.id" :value="poli.id">
                            {{ poli.nama_poli }}
                        </option>
                    </select>
                </div>

                <!-- Pilih Jadwal -->
                <div class="p-field">
                    <label for="jadwal" class="form-label">Pilih Jadwal</label>
                    <select v-model="form.id_jadwal" name="id_jadwal" class="form-dropdown" id="jadwal" required>
                        <option value="" disabled>Pilih Jadwal</option>
                        <option v-for="jadwal in jadwalOptions" :key="jadwal.id" :value="jadwal.id">
                            {{ jadwal.label }}
                        </option>
                    </select>
                </div>

                <!-- Keluhan -->
                <div class="p-field">
                    <label for="keluhan" class="form-label">Keluhan</label>
                    <textarea
                        id="keluhan"
                        v-model="form.keluhan"
                        name="keluhan"
                        rows="4"
                        class="form-textarea"
                        placeholder="Masukkan Keluhan"
                        required
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="p-field text-center">
                    <button type="submit" class="form-button">
                        <i class="pi pi-check"></i> Daftar
                    </button>
                </div>
            </form>
        </div>
    </app-layout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { defineProps } from 'vue';
import AppLayout from "@/primevue/layout/AppLayoutPasien.vue";
import axios from 'axios';

// Define props
defineProps({
    pasien: Object,
    poliOptions: Array,
});

// Form data
const form = ref({
    id_poli: null,
    id_jadwal: null,
    keluhan: '',
});

const jadwalOptions = ref([]);

// Function to fetch jadwal based on selected poli
const fetchJadwal = async () => {
    if (form.value.id_poli) {
        try {
            const response = await axios.post('/api/get-jadwal-by-poli', {
                id_poli: form.value.id_poli,
            });
            jadwalOptions.value = response.data;
        } catch (error) {
            console.error(error);
        }
    }
};
</script>


<style scoped lang="scss">
.form-container {
    max-width: 600px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.form-title {
    text-align: center;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.p-field {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 1rem;
    color: #555;
}

.form-input, .form-dropdown, .form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    color: #333;
    background-color: #f9f9f9;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-input:focus, .form-dropdown:focus, .form-textarea:focus {
    border-color: #007bff;
    outline: none;
    background-color: #fff;
}

.form-button {
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
    font-weight: bold;
    color: #fff;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.form-button:hover {
    background-color: #0056b3;
}

.text-center {
    text-align: center;
}
</style>
