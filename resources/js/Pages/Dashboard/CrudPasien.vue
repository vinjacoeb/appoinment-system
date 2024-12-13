<script setup>
import { ref, onBeforeMount } from 'vue';
import { useRouter } from 'vue-router';
import { Inertia } from '@inertiajs/inertia';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import axios from 'axios';  // Import Axios
import AppLayout from "@/primevue/layout/AppLayoutAdmin.vue";
import Swal from 'sweetalert2';  // Import SweetAlert2

// Define references to store data
const pasien = ref([]);  // Pasien data
const newPasien = ref({
  name: '',
  email: '',
  password: '',
  alamat: '',
  no_ktp: '',
  no_hp: '',
  no_rm: '',  // This will auto-generate
});

// Filters and loading states
const filters = ref(null);
const loading = ref(false);

// Function to generate no_rm automatically
const generateNoRM = () => {
  const currentDate = new Date();
  const year = currentDate.getFullYear();
  const month = String(currentDate.getMonth() + 1).padStart(2, '0');
  const day = String(currentDate.getDate()).padStart(2, '0');
  const count = pasien.value.length + 1; // assuming the number of patients
  return `${year}${month}${day}-${String(count).padStart(3, '0')}`;
};

// Fetch pasien data from API
const fetchData = async () => {
    loading.value = true;
    try {
        const responsePasien = await axios.get('/pasien');
        pasien.value = responsePasien.data;
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
        name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        email: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    };
};

// Clear filters
const clearFilters = () => {
    initFilters();
};

const editModalVisible = ref(false);
const editPasienData = ref({
  id: null,
  name: '',
  email: '',
  alamat: '',
  no_hp: '',
  no_ktp: '',
  no_rm: '',
});

// Open modal and load pasien data
const openEditModal = (pasienData) => {
  editPasienData.value = { ...pasienData };
  editModalVisible.value = true;
};

// Close modal
const closeEditModal = () => {
  editModalVisible.value = false;
};

// Update pasien information
const updatePasien = async () => {
  try {
    const dataToUpdate = {
      name: editPasienData.value.name,
      email: editPasienData.value.email,
      alamat: editPasienData.value.alamat,
      no_hp: editPasienData.value.no_hp,
      no_ktp: editPasienData.value.no_ktp,
    };

    if (editPasienData.value.password) {
      dataToUpdate.password = editPasienData.value.password;
      dataToUpdate.password_confirmation = editPasienData.value.password_confirmation;
    }

    await axios.put(`/pasien/${editPasienData.value.id}`, dataToUpdate);
    fetchData();
    Swal.fire('Updated!', 'Pasien information has been updated.', 'success');
    closeEditModal();
  } catch (error) {
    console.error('Error updating pasien:', error);
    Swal.fire('Error!', 'Failed to update pasien information.', 'error');
  }
};

// Delete pasien
const deletePasien = (id) => {
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
            Inertia.delete(route('pasien.destroy', id), {
                onSuccess: () => {
                    pasien.value = pasien.value.filter((p) => p.id !== id);
                    Swal.fire(
                        'Deleted!',
                        'Pasien has been deleted.',
                        'success'
                    );
                },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'There was an issue deleting the doctor.',
                        'error'
                    );
                },
            });
        }
    });
};

// Save a new pasien
const savePasien = async () => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to add this pasien?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, add it!',
        cancelButtonText: 'No, cancel!',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                // Hash the password before sending it
                const hashedPasswordResponse = await axios.post('/hash-password', { password: newPasien.value.password });

                // Auto-generate no_rm
                newPasien.value.no_rm = generateNoRM();

                await axios.post('/pasien', {
                    name: newPasien.value.name,
                    email: newPasien.value.email,
                    password: hashedPasswordResponse.data.hashedPassword,
                    alamat: newPasien.value.alamat,
                    no_hp: newPasien.value.no_hp,
                    no_ktp: newPasien.value.no_ktp,
                    no_rm: newPasien.value.no_rm,
                });

                fetchData();
                newPasien.value = {
                    name: '',
                    email: '',
                    password: '',
                    alamat: '',
                    no_ktp: '',
                    no_hp: '',
                    no_rm: '',
                };

                Swal.fire('Added!', 'Pasien has been added.', 'success');
            } catch (error) {
                console.error("Error saving pasien:", error);
                Swal.fire('Error!', 'There was an issue adding the pasien.', 'error');
            }
        }
    });
};

// Initialize filters on mount and fetch data
onBeforeMount(() => {
    initFilters();
    fetchData();  // Fetch data when the component mounts
});
</script>

<template>
  <app-layout>
    <!-- Add New Pasien Form -->
    <div class="card">
        <h5 class="mb-4">Add New Pasien</h5>
      <div class="p-fluid grid">
        <div class="field col-12 md:col-6">
          <label for="name">Name</label>
          <InputText id="name" v-model="newPasien.name" placeholder="Enter patient's name" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="email">Email</label>
          <InputText id="email" v-model="newPasien.email" placeholder="Enter patient's email" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="password">Password</label>
          <InputText id="password" v-model="newPasien.password" type="password" placeholder="Enter patient's password" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="alamat">Address</label>
          <InputText id="alamat" v-model="newPasien.alamat" placeholder="Enter patient's address" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="no_ktp">ID Number</label>
          <InputText id="no_ktp" v-model="newPasien.no_ktp" placeholder="Enter patient's ID number" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="no_hp">Phone Number</label>
          <InputText id="no_hp" v-model="newPasien.no_hp" placeholder="Enter patient's phone number" />
        </div>
        <div class="col-12 text-right">
            <Button label="Save" class="custom-blue-button" @click="savePasien" />
        </div>
      </div>
    </div>

    <!-- Pasien Table -->
    <div class="card">
      <h5>Pasien List</h5>
      <DataTable
        :value="pasien"
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
              <InputText v-model="filters.global.value" placeholder="Global search" />
            </span>
          </div>
        </template>

        <!-- Table Columns -->
        <Column field="name" header="Name" style="min-width: 12rem">
          <template #body="{ data }">{{ data.name }}</template>
        </Column>
        <Column field="email" header="Email" style="min-width: 12rem">
          <template #body="{ data }">{{ data.email }}</template>
        </Column>
        <Column field="alamat" header="Alamat" style="min-width: 12rem">
          <template #body="{ data }">{{ data.alamat }}</template>
        </Column>
        <Column field="no_ktp" header="ID Number" style="min-width: 12rem">
          <template #body="{ data }">{{ data.no_ktp }}</template>
        </Column>
        <Column field="no_hp" header="No HP" style="min-width: 12rem">
          <template #body="{ data }">{{ data.no_hp }}</template>
        </Column>
        <Column field="no_rm" header="Rekam Medis" style="min-width: 12rem">
          <template #body="{ data }">{{ data.no_rm }}</template>
        </Column>
        <Column header="Action" style="min-width: 8rem">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" class="p-button-rounded p-button-info" @click="openEditModal(data)" />
            <Button icon="pi pi-trash" class="p-button-rounded p-button-danger ml-2" @click="deletePasien(data.id)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Edit Modal -->
    <Dialog header="Edit Pasien" v-model:visible="editModalVisible">
      <div class="p-fluid grid">
        <div class="field col-12 md:col-6">
          <label for="edit-name">Name</label>
          <InputText id="edit-name" v-model="editPasienData.name" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="edit-email">Email</label>
          <InputText id="edit-email" v-model="editPasienData.email" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="edit-alamat">Address</label>
          <InputText id="edit-alamat" v-model="editPasienData.alamat" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="edit-no_ktp">Nomer KTP</label>
          <InputText id="edit-no_ktp" v-model="editPasienData.no_ktp" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="edit-no_hp">Phone Number</label>
          <InputText id="edit-no_hp" v-model="editPasienData.no_hp" />
        </div>
        <div class="col-12 text-right">
          <Button label="Save Changes" @click="updatePasien" />
        </div>
      </div>
    </Dialog>
  </app-layout>
</template>

<style scoped>
.custom-blue-button {
  background-color: #3b82f6;
  color: white;
}
</style>
