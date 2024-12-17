<script setup>
import { ref, onBeforeMount } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import axios from 'axios';  
import AppLayout from "@/primevue/layout/AppLayoutAdmin.vue";
import Swal from 'sweetalert2';

// Define references to store data
const polis = ref([]);
const newPoli = ref({
  nama_poli: '',
  keterangan: '',
});

// Filters and loading states
const filters = ref(null);
const loading = ref(false);

// Fetch poli data from API
const fetchData = async () => {
    loading.value = true;
    try {
        const responsePoli = await axios.get('/poli');
        console.log("Poli data:", responsePoli.data);
        polis.value = responsePoli.data;
    } catch (error) {
        console.error("Error fetching data:", error);
    } finally {
        loading.value = false;
    }
};

// Initialize filters
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        nama_poli: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        keterangan: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    };
};

// Clear filters
const clearFilters = () => {
    initFilters();
};

// Modal states
const editModalVisible = ref(false);
const editPoliData = ref({
  id: null,
  nama_poli: '',
  keterangan: '',
});

// Open modal and load poli data
const openEditModal = (poli) => {
  editPoliData.value = { ...poli };
  editModalVisible.value = true;
};

// Close modal
const closeEditModal = () => {
  editModalVisible.value = false;
};

// Update poli information
const updatePoli = async () => {
  try {
    const dataToUpdate = {
      nama_poli: editPoliData.value.nama_poli,
      keterangan: editPoliData.value.keterangan,
    };
    await axios.post(`/poli/${editPoliData.value.id}`, dataToUpdate);
    fetchData();
    Swal.fire('Updated!', 'Poli information has been updated.', 'success');
    closeEditModal();
  } catch (error) {
    console.error('Error updating poli:', error);
    Swal.fire('Error!', 'Failed to update poli information.', 'error');
  }
};

// Delete poli
const deletePoli = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Inertia.delete(route('poli.destroy', id), {
                onSuccess: () => {
                    polis.value = polis.value.filter((p) => p.id !== id);
                    Swal.fire('Deleted!', 'Poli has been deleted.', 'success');
                },
                onError: () => {
                    Swal.fire('Error!', 'There was an issue deleting the poli.', 'error');
                },
            });
        }
    });
};

// Save a new poli
const savePoli = async () => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to add this poli?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, add it!',
        cancelButtonText: 'No, cancel!',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.post('/poli', {
                    nama_poli: newPoli.value.nama_poli,
                    keterangan: newPoli.value.keterangan,
                });
                fetchData();
                newPoli.value = {
                    nama_poli: '',
                    keterangan: '',
                };
                Swal.fire('Added!', 'Poli has been added.', 'success');
            } catch (error) {
                console.error("Error saving poli:", error);
                Swal.fire('Error!', 'There was an issue adding the poli.', 'error');
            }
        }
    });
};

// Initialize filters on mount and fetch data
onBeforeMount(() => {
    initFilters();
    fetchData();  
});
</script>

<template>
  <app-layout>
    <!-- Add New Poli Form -->
    <div class="card">
        <h5 class="mb-4">Add New Poli</h5>
      <div class="p-fluid grid">
        <div class="field col-12 md:col-6">
          <label for="nama_poli">Name</label>
          <InputText id="nama_poli" v-model="newPoli.nama_poli" placeholder="Enter poli's name" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="keterangan">Description</label>
          <InputText id="keterangan" v-model="newPoli.keterangan" placeholder="Enter poli's description" />
        </div>
        <div class="col-12 text-right">
            <Button label="Save" class="custom-blue-button" @click="savePoli" />
        </div>
      </div>
    </div>

    <!-- Poli Table -->
    <div class="card">
      <h5>Poli List</h5>
      <DataTable
        :value="polis"
        :paginator="true"
        :rows="10"
        :loading="loading"
        :filters="filters"
        filterDisplay="menu"
        dataKey="id"
        responsiveLayout="scroll"
        v-model:filters="filters"
      >
        <!-- Table Header -->
        <template #header>
          <div class="flex justify-content-between flex-column sm:flex-row">
            <Button type="button" icon="pi pi-filter-slash" label="Clear Filters" class="p-button-outlined mb-2" @click="clearFilters" />
            <span class="p-input-icon-left mb-2">
              <i class="pi pi-search" />
              <InputText v-model="filters['global'].value" placeholder="Search" style="width: 100%" />
            </span>
          </div>
        </template>

        <!-- Columns -->
        <Column field="nama_poli" header="Name" style="min-width: 12rem">
          <template #body="{ data }">{{ data.nama_poli }}</template>
        </Column>
        <Column field="keterangan" header="Description" style="min-width: 12rem">
          <template #body="{ data }">{{ data.keterangan }}</template>
        </Column>
        <Column header="Action" style="min-width: 8rem">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" class="p-button-rounded p-button-info" @click="openEditModal(data)" />
            <Button icon="pi pi-trash" class="p-button-rounded p-button-danger ml-2" @click="deletePoli(data.id)" />
          </template>
        </Column>
      </DataTable>

      <Dialog v-model:visible="editModalVisible" header="Edit Poli" :style="{ width: '50vw' }" modal>
  <div class="p-fluid grid">
    <div class="field col-12 md:col-6">
      <label for="editNamaPoli">Name</label>
      <InputText id="editNamaPoli" v-model="editPoliData.nama_poli" placeholder="Enter poli's name" />
    </div>
    <div class="field col-12 md:col-6">
      <label for="editKeterangan">Description</label>
      <InputText id="editKeterangan" v-model="editPoliData.keterangan" placeholder="Enter poli's description" />
    </div>
  </div>
  <div class="text-right mt-4">
    <Button label="Save" class="p-button-primary" @click="updatePoli" />
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
  border-color: #007bff;
  color: white;
}

.custom-blue-button:hover {
  background-color: #0056b3;
  border-color: #0056b3;
}
</style>
