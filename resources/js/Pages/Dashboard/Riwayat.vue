<template>
    <app-layout>
      <div>
        <div class="card">
          <h5>Riwayat Periksa</h5>
          <DataTable
            :value="riwayatPeriksa"
            :paginator="true"
            :rows="10"
            :loading="loading"
            :filters="filters"
            filterDisplay="menu"
            dataKey="id"
            responsiveLayout="scroll"
            v-model:filters="filters"
          >
            <template #header>
              <span class="p-input-icon-left">
                <i class="pi pi-search" />
                <InputText v-model="filters['global'].value" placeholder="Search" />
              </span>
            </template>
  
            <Column field="no_antrian" header="No. Antrian" />
            <Column field="keluhan" header="Keluhan" />
            <Column header="Nama Pasien">
              <template #body="{ data }">
                {{ data.pasien.name }}
              </template>
            </Column>
            <Column header="Jadwal Dokter">
              <template #body="{ data }">
                {{ data.jadwal.hari }} - {{ data.jadwal.jam_mulai }} to {{ data.jadwal.jam_selesai }}
              </template>
            </Column>
            <Column header="Action">
              <template #body="{ data }">
                <Button 
                  icon="pi pi-eye" 
                  @click="viewPeriksa(data.id)" 
                  class="p-button-rounded p-button-info" 
                />
              </template>
            </Column>
          </DataTable>
        </div>
  
        <!-- Modal for Viewing Details -->
        <Dialog v-model:visible="isViewModalVisible" header="Detail Riwayat" :modal="true" :closable="false">
          <div class="p-fluid">
            <h4>Detail Periksa</h4>
  
            <!-- Table for Patient Information -->
            <table class="p-datatable p-datatable-responsive">
              <thead>
                <tr>
                  <th colspan="2">Informasi Pasien</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Nama Pasien:</strong></td>
                  <td>{{ viewData.nama_pasien }}</td>
                </tr>
                <tr>
                  <td><strong>Tanggal Periksa:</strong></td>
                  <td>{{ viewData.tgl_periksa }}</td>
                </tr>
                <tr>
                  <td><strong>Catatan:</strong></td>
                  <td>{{ viewData.catatan }}</td>
                </tr>
              </tbody>
            </table>
  
            <!-- Table for Medication -->
            <div v-if="viewData.obatList.length > 0">
              <h4>Obat yang Diberikan</h4>
              <table class="p-datatable p-datatable-responsive">
                <thead>
                  <tr>
                    <th>Nama Obat</th>
                    <th>Kemasan</th>
                    <th>Harga</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="obat in viewData.obatList" :key="obat.id">
                    <td>{{ obat.nama_obat }}</td>
                    <td>{{ obat.kemasan }}</td>
                    <td>Rp {{ obat.harga.toLocaleString() }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
  
            <!-- Table for Biaya -->
            <h4>Biaya Periksa</h4>
            <table class="p-datatable p-datatable-responsive">
              <thead>
                <tr>
                  <th colspan="2">Detail Biaya</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Biaya Poli:</strong></td>
                  <td>Rp 150.000</td>
                </tr>
                <tr>
                  <td><strong>Biaya Obat:</strong></td>
                  <td>Rp {{ obatTotal.toLocaleString() }}</td>
                </tr>
                <tr>
                  <td><strong>Total:</strong></td>
                  <td>Rp {{ totalBiaya.toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>
          </div>
  
          <template #footer>
            <Button label="Close" class="p-button-secondary" @click="closeModal" />
          </template>
        </Dialog>
      </div>
    </app-layout>
  </template>
  
  <script setup>
  import { ref, onBeforeMount, computed } from 'vue';
  import axios from 'axios';
  import { FilterMatchMode } from 'primevue/api';
  import AppLayout from "@/primevue/layout/AppLayout.vue";  
  import Button from 'primevue/button';
  import Column from 'primevue/column';
  import DataTable from 'primevue/datatable';
  import InputText from 'primevue/inputtext';
  import Dialog from 'primevue/dialog';
  
  const riwayatPeriksa = ref([]);
  const filters = ref(null);
  const loading = ref(false);
  const isViewModalVisible = ref(false);
  const viewData = ref({}); // Data for viewing the patient's record
  
  // Function to fetch data
  const fetchData = async () => {
    loading.value = true;
    try {
      const response = await axios.get('/show-riwayat');  // Fetch patient's history
      riwayatPeriksa.value = response.data;
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      loading.value = false;
    }
  };
  
  const viewPeriksa = async (id_daftar_poli) => {
    try {
      const response = await axios.get(`/riwayat/${id_daftar_poli}`);
      const periksaData = response.data;
  
      if (!periksaData) {
        console.error("No data found for the selected 'riwayat'.");
        return;
      }
  
      // Set the viewData
      viewData.value = {
        nama_pasien: periksaData.daftar.pasien.name,
        tgl_periksa: periksaData.tgl_periksa,
        catatan: periksaData.catatan,
        obatList: periksaData.detail_periksa.map(detail => detail.obat),
      };
  
      // Ensure modal visibility is set to true
      isViewModalVisible.value = true;
    } catch (error) {
      console.error("Error fetching detailed 'riwayat' data:", error);
    }
  };
  
  // Computed properties for calculating biaya
  const obatTotal = computed(() => {
    return viewData.value.obatList.reduce((total, obat) => total + obat.harga, 0);
  });
  
  const totalBiaya = computed(() => {
    const biayaPoli = 150000;
    return biayaPoli + obatTotal.value;
  });
  
  // Function to close the modal
  const closeModal = () => {
    isViewModalVisible.value = false;
  };
  
  onBeforeMount(() => {
    filters.value = { global: { value: null, matchMode: FilterMatchMode.CONTAINS } };
    fetchData();
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
  
  /* Add some padding and spacing for table content */
  table {
    width: 100%;
    margin-bottom: 20px;
    border-collapse: collapse;
  }
  
  th, td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
  
  th {
    background-color: #f4f4f4;
    font-weight: bold;
  }
  
  td {
    font-size: 14px;
  }
  
  h4 {
    margin-top: 20px;
    font-size: 18px;
    color: #333;
  }
  </style>
  