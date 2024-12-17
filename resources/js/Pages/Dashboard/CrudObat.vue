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
const obats = ref([]);
const newObat = ref({
  nama_obat: '',
  kemasan: '',
  harga: '',
});

// Filters and loading states
const filters = ref(null);
const loading = ref(false);

// Fetch obat data from API
const fetchData = async () => {
    loading.value = true;
    try {
        const responseObat = await axios.get('/obat');
        console.log("Obat data:", responseObat.data);
        obats.value = responseObat.data;
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
        nama_obat: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        kemasan: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
        harga: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    };
};

// Clear filters
const clearFilters = () => {
    initFilters();
};

// Modal states
const editModalVisible = ref(false);
const editObatData = ref({
  id: null,
  nama_obat: '',
  kemasan: '',
  harga: '',
});

// Open modal and load obat data
const openEditModal = (obat) => {
  editObatData.value = { ...obat };
  editModalVisible.value = true;
};

// Close modal
const closeEditModal = () => {
  editModalVisible.value = false;
};

// Update obat information
const updateObat = async () => {
  try {
    const dataToUpdate = {
      nama_obat: editObatData.value.nama_obat,
      kemasan: editObatData.value.kemasan,
      harga: editObatData.value.harga,
    };
    await axios.post(`/obat/${editObatData.value.id}`, dataToUpdate);
    fetchData();
    Swal.fire('Updated!', 'Obat information has been updated.', 'success');
    closeEditModal();
  } catch (error) {
    console.error('Error updating obat:', error);
    Swal.fire('Error!', 'Failed to update obat information.', 'error');
  }
};

// Delete obat
const deleteObat = (id) => {
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
            Inertia.delete(route('obat.destroy', id), {
                onSuccess: () => {
                    obats.value = obats.value.filter((o) => o.id !== id);
                    Swal.fire('Deleted!', 'Obat has been deleted.', 'success');
                },
                onError: () => {
                    Swal.fire('Error!', 'There was an issue deleting the obat.', 'error');
                },
            });
        }
    });
};

// Save a new obat
const saveObat = async () => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to add this obat?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, add it!',
        cancelButtonText: 'No, cancel!',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.post('/obat', {
                    nama_obat: newObat.value.nama_obat,
                    kemasan: newObat.value.kemasan,
                    harga: newObat.value.harga,
                });
                fetchData();
                newObat.value = {
                    nama_obat: '',
                    kemasan: '',
                    harga: '',
                };
                Swal.fire('Added!', 'Obat has been added.', 'success');
            } catch (error) {
                console.error("Error saving obat:", error);
                Swal.fire('Error!', 'There was an issue adding the obat.', 'error');
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
    <!-- Add New Obat Form -->
    <div class="card">
        <h5 class="mb-4">Add New Obat</h5>
      <div class="p-fluid grid">
        <div class="field col-12 md:col-6">
          <label for="nama_obat">Name</label>
          <InputText id="nama_obat" v-model="newObat.nama_obat" placeholder="Enter obat's name" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="kemasan">Packaging</label>
          <InputText id="kemasan" v-model="newObat.kemasan" placeholder="Enter obat's packaging" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="harga">Price</label>
          <InputText id="harga" v-model="newObat.harga" placeholder="Enter obat's price" />
        </div>
        <div class="col-12 text-right">
            <Button label="Save" class="custom-blue-button" @click="saveObat" />
        </div>
      </div>
    </div>

    <!-- Obat Table -->
    <div class="card">
      <h5>Obat List</h5>
      <DataTable
        :value="obats"
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
        <Column field="nama_obat" header="Name" style="min-width: 12rem">
          <template #body="{ data }">{{ data.nama_obat }}</template>
        </Column>
        <Column field="kemasan" header="Packaging" style="min-width: 12rem">
          <template #body="{ data }">{{ data.kemasan }}</template>
        </Column>
        <Column field="harga" header="Price" style="min-width: 12rem">
          <template #body="{ data }">{{ data.harga }}</template>
        </Column>
        <Column header="Action" style="min-width: 8rem">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" class="p-button-rounded p-button-info" @click="openEditModal(data)" />
            <Button icon="pi pi-trash" class="p-button-rounded p-button-danger ml-2" @click="deleteObat(data.id)" />
          </template>
        </Column>
      </DataTable>

      <!-- Edit Modal -->
      <Dialog v-model:visible="editModalVisible" header="Edit Obat" :style="{ width: '50vw' }" modal>
        <div class="p-fluid grid">
          <div class="field col-12 md:col-6">
            <label for="editNamaObat">Name</label>
            <InputText id="editNamaObat" v-model="editObatData.nama_obat" placeholder="Enter obat's name" />
          </div>
          <div class="field col-12 md:col-6">
            <label for="editKemasan">Packaging</label>
            <InputText id="editKemasan" v-model="editObatData.kemasan" placeholder="Enter obat's packaging" />
          </div>
          <div class="field col-12 md:col-6">
            <label for="editHarga">Price</label>
            <InputText id="editHarga" v-model="editObatData.harga" placeholder="Enter obat's price" />
          </div>
        </div>
        <div class="text-right mt-4">
          <Button label="Save" class="p-button-primary" @click="updateObat" />
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
