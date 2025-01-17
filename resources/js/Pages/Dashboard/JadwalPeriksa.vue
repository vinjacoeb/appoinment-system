<script setup>
import { ref, onBeforeMount } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import axios from 'axios';
import AppLayout from "@/primevue/layout/AppLayoutDokter.vue";
import Swal from 'sweetalert2';

// Define references for jadwal periksa
const jadwalPeriksa = ref([]);
const newJadwal = ref({
  hari: '',
  jam_mulai: '',
  jam_selesai: '',
  status: true,
});

// Filters and loading states
const filters = ref(null);
const loading = ref(false);

// Fetch Jadwal Periksa based on logged-in doctor
const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/jadwal-periksa');
    jadwalPeriksa.value = response.data;
  } catch (error) {
    console.error("Error fetching jadwal periksa:", error);
  } finally {
    loading.value = false;
  }
};

// Initialize filters
const initFilters = () => {
  filters.value = {
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    hari: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
  };
};

// Clear filters
const clearFilters = () => {
  initFilters();
};
const statusOptions = [
  { label: "Aktif", value: true },
  { label: "Tidak Aktif", value: false },
];



const saveJadwal = async () => {
  try {
    const dataToSend = { 
      ...newJadwal.value, 
      status: newJadwal.value.status // Tidak perlu .value
    };

    await axios.post('/jadwal-periksa', dataToSend);
    fetchData();
    Swal.fire('Success!', 'Jadwal Periksa added successfully.', 'success');
    newJadwal.value = { hari: '', jam_mulai: '', jam_selesai: '', status: true };
  } catch (error) {
    if (error.response && error.response.data) {
      Swal.fire('Error!', error.response.data.message || 'Anda Tidak Dapat Menambah Jadwal Periksa.', 'error');
    } else {
      Swal.fire('Error!', 'Terjadi kesalahan saat menambah jadwal.', 'error');
    }
  }
};




// Edit Modal state
const editModalVisible = ref(false);
const editJadwal = ref({
  id: null,
  hari: '',
  jam_mulai: '',
  jam_selesai: '',
  status: true,
});

const openEditModal = (jadwal) => {
  editJadwal.value = { ...jadwal, status: !!jadwal.status }; // Pastikan status berupa boolean
  editModalVisible.value = true;
};


// Close Edit Modal
const closeEditModal = () => {
  editModalVisible.value = false;
};

// Update Jadwal Periksa
// Update Jadwal Periksa
const updateJadwal = async () => {
  try {
    const dataToSend = { 
      ...editJadwal.value, 
      status: editJadwal.value.status // Tidak perlu .value
    };

    await axios.post(`/jadwal-periksa/${editJadwal.value.id}`, dataToSend);
    fetchData();
    Swal.fire('Updated!', 'Jadwal Periksa updated successfully.', 'success');
    closeEditModal();
  } catch (error) {
    Swal.fire('Error!', 'Failed to update Jadwal Periksa.', 'error');
  }
};



// Delete Jadwal Periksa
const deleteJadwal = (id) => {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!',
  }).then((result) => {
    if (result.isConfirmed) {
      Inertia.delete(route('jadwal-periksa.destroy', id), {
        onSuccess: () => {
          jadwalPeriksa.value = jadwalPeriksa.value.filter((j) => j.id !== id);
          Swal.fire('Deleted!', 'Jadwal Periksa has been deleted.', 'success');
        },
        onError: () => {
          Swal.fire('Error!', 'Failed to delete Jadwal Periksa.', 'error');
        },
      });
    }
  });
};

// Initialize filters and fetch data
onBeforeMount(() => {
  initFilters();
  fetchData();
});
</script>

<template>
  <app-layout>
    <!-- Add New Jadwal Form -->
    <div class="card">
      <h5 class="mb-4">Add New Jadwal Periksa</h5>
      <div class="p-fluid grid">
        <div class="field col-12 md:col-3">
          <label for="hari">Hari</label>
          <InputText id="hari" v-model="newJadwal.hari" placeholder="Enter day" />
        </div>
        <div class="field col-12 md:col-3">
          <label for="jam_mulai">Start Time</label>
          <InputText id="jam_mulai" v-model="newJadwal.jam_mulai" placeholder="HH:MM" />
        </div>
        <div class="field col-12 md:col-3">
          <label for="jam_selesai">End Time</label>
          <InputText id="jam_selesai" v-model="newJadwal.jam_selesai" placeholder="HH:MM" />
        </div>
        <div class="field col-12 md:col-3">
          <label for="status">Status</label>
          <Dropdown 
          id="status" 
          v-model="newJadwal.status" 
          :options="statusOptions" 
          optionLabel="label" 
          optionValue="value" 
          placeholder="Select status" 
        />

        </div>
        <div class="col-12 text-right">
          <Button label="Save" class="custom-blue-button" @click="saveJadwal" />
        </div>
      </div>
    </div>

    <!-- Jadwal Periksa Table -->
    <div class="card">
      <h5>Jadwal Periksa List</h5>
      <DataTable :value="jadwalPeriksa" :paginator="true" :rows="10" :loading="loading" responsiveLayout="scroll">
        <Column field="hari" header="Day"></Column>
        <Column field="jam_mulai" header="Start Time"></Column>
        <Column field="jam_selesai" header="End Time"></Column>
        <Column field="status" header="Status">
        <template #body="{ data }">
          <span>{{ data.status ? "Aktif" : "Tidak Aktif" }}</span>
        </template>
      </Column>

        <Column header="Actions">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" class="p-button-rounded p-button-info" @click="openEditModal(data)" />
            <Button icon="pi pi-trash" class="p-button-rounded p-button-danger ml-2" @click="deleteJadwal(data.id)" />
          </template>
        </Column>
      </DataTable>

      <!-- Edit Modal -->
      <Dialog v-model:visible="editModalVisible" header="Edit Jadwal Periksa" modal>
        <div class="p-fluid grid">
          <div class="field col-12">
            <label for="editHari">Hari</label>
            <InputText id="editHari" v-model="editJadwal.hari" />
          </div>
          <div class="field col-6">
            <label for="editJamMulai">Start Time</label>
            <InputText id="editJamMulai" v-model="editJadwal.jam_mulai"  placeholder="HH:MM" />
          </div>
          <div class="field col-6">
            <label for="editJamSelesai">End Time</label>
            <InputText id="editJamSelesai" v-model="editJadwal.jam_selesai" placeholder="HH:MM" />
          </div>
          <div class="field col-12">
            <label for="editStatus">Status</label>
            <Dropdown 
  id="editStatus" 
  v-model="editJadwal.status" 
  :options="statusOptions" 
  optionLabel="label" 
  optionValue="value" 
/>

          </div>
        </div>
        <div class="text-right">
          <Button label="Save" class="p-button-primary" @click="updateJadwal" />
          <Button label="Cancel" class="p-button-secondary ml-2" @click="closeEditModal" />
        </div>
      </Dialog>
    </div>
  </app-layout>
</template>

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
