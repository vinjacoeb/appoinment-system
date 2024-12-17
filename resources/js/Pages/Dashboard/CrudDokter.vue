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
const dokters = ref([]);  // Doctors data
const polis = ref([]);    // Poli options for dropdown
const newDoctor = ref({
  nama: '',
  email: '',
  password: '',
  password_confirmation: '',
  alamat: '',
  no_hp: '',
  id_poli: null,
});

// Filters and loading states
const filters = ref(null);
const loading = ref(false);

// Fetch doctors and poli data from API
const fetchData = async () => {
    loading.value = true;
    try {
        // Fetch doctor data
        const responseDokter = await axios.get('/dokter');
        console.log("Doctors data:", responseDokter.data);  // Log the response
        dokters.value = responseDokter.data;

        // Fetch poli data
        const responsePoli = await axios.get('/poli');
        console.log("Poli data:", responsePoli.data);  // Log the response
        polis.value = responsePoli.data.map((p) => ({ label: p.nama_poli, value: p.id }));

        // Map poli names to doctors based on id_poli
        dokters.value = dokters.value.map(dokter => {
            const poli = responsePoli.data.find(p => p.id === dokter.id_poli);
            return {
                ...dokter,
                poli: poli ? poli.nama_poli : 'Unknown'  // Add poli name to the doctor data
            };
        });
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
        nama: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        email: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
        poli: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
};

// Clear filters
const clearFilters = () => {
    initFilters();
};


const editModalVisible = ref(false);
const editDoctorData = ref({
  id: null,
  nama: '',
  email: '',
  alamat: '',
  no_hp: '',
  id_poli: null,
});

// Open modal and load doctor data
const openEditModal = (doctor) => {
  editDoctorData.value = { ...doctor, id_poli: { label: doctor.poli, value: doctor.id_poli } };
  editModalVisible.value = true;
};

// Close modal
const closeEditModal = () => {
  editModalVisible.value = false;
};

// Update doctor information
const updateDoctor = async () => {
  try {
    // Prepare the data for the PUT request
    const dataToUpdate = {
      nama: editDoctorData.value.nama,
      email: editDoctorData.value.email,
      alamat: editDoctorData.value.alamat,
      no_hp: editDoctorData.value.no_hp,
      id_poli: editDoctorData.value.id_poli.value,
    };

    // If a password is provided, include it along with the confirmation
    if (editDoctorData.value.password) {
      dataToUpdate.password = editDoctorData.value.password;
      dataToUpdate.password_confirmation = editDoctorData.value.password_confirmation;
    }

    // Send the PUT request with the updated data
    await axios.put(`/dokter/${editDoctorData.value.id}`, dataToUpdate);

    // Refresh the data after the update
    fetchData();
    Swal.fire('Updated!', 'Doctor information has been updated.', 'success');
    closeEditModal();
  } catch (error) {
    console.error('Error updating doctor:', error);
    Swal.fire('Error!', 'Failed to update doctor information.', 'error');
  }
};



const deleteDoctor = (id) => {
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
            Inertia.delete(route('dokter.destroy', id), {
                onSuccess: () => {
                    dokters.value = dokters.value.filter((d) => d.id !== id);
                    Swal.fire(
                        'Deleted!',
                        'Doctor has been deleted.',
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

// Save a new doctor
const saveDoctor = async () => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to add this doctor?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, add it!',
        cancelButtonText: 'No, cancel!',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                // Hash the password before sending it
                const hashedPasswordResponse = await axios.post('/hash-password', { password: newDoctor.value.password });

                // Ensure the id_poli is correctly sent with the request (only the value, not the whole object)
                await axios.post('/dokter', {
                    nama: newDoctor.value.nama,
                    email: newDoctor.value.email,
                    password: newDoctor.value.password,  // Use the hashed password from the response
                    password_confirmation: newDoctor.value.password,
                    alamat: newDoctor.value.alamat,
                    no_hp: newDoctor.value.no_hp,
                    id_poli: newDoctor.value.id_poli.value,  // Send only the value of id_poli
                });

                // Refresh the data
                fetchData();

                // Clear the form
                newDoctor.value = {
                    nama: '',
                    email: '',
                    password: '',
                    alamat: '',
                    no_hp: '',
                    id_poli: null,
                };

                Swal.fire('Added!', 'Doctor has been added.', 'success');
            } catch (error) {
                console.error("Error saving doctor:", error);
                Swal.fire('Error!', 'There was an issue adding the doctor.', 'error');
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
    <!-- Add New Doctor Form -->
    <div class="card">
        <h5 class="mb-4">Add New Doctor</h5>
      <div class="p-fluid grid">
        <div class="field col-12 md:col-6">
          <label for="nama">Name</label>
          <InputText id="nama" v-model="newDoctor.nama" placeholder="Enter doctor's name" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="email">Email</label>
          <InputText id="email" v-model="newDoctor.email" placeholder="Enter doctor's email" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="password">Password</label>
          <InputText id="password" v-model="newDoctor.password" type="password" placeholder="Enter doctor's password" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="password_confirmation">Confirm Password</label>
          <InputText id="password_confirmation" v-model="newDoctor.password_confirmation" type="password" placeholder="Confirm patient's password" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="alamat">Address</label>
          <InputText id="alamat" v-model="newDoctor.alamat" placeholder="Enter doctor's address" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="no_hp">Phone Number</label>
          <InputText id="no_hp" v-model="newDoctor.no_hp" placeholder="Enter doctor's phone number" />
        </div>
        <div class="field col-12 md:col-6">
          <label for="id_poli">Specialization</label>
          <Dropdown id="id_poli" v-model="newDoctor.id_poli" :options="polis" placeholder="Select Specialization" />
        </div>
        <div class="col-12 text-right">
            <Button label="Save" class="custom-blue-button" @click="saveDoctor" />
        </div>
      </div>
    </div>

    <!-- Doctors Table -->
    <div class="card">
      <h5>Doctor List</h5>
      <DataTable
        :value="dokters"
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
        <Column field="nama" header="Name" style="min-width: 12rem">
          <template #body="{ data }">{{ data.nama }}</template>
        </Column>
        <Column field="email" header="Email" style="min-width: 12rem">
          <template #body="{ data }">{{ data.email }}</template>
        </Column>
        <Column field="alamat" header="Address" style="min-width: 12rem">
          <template #body="{ data }">{{ data.alamat }}</template>
        </Column>
        <Column field="no_hp" header="Phone" style="min-width: 12rem">
          <template #body="{ data }">{{ data.no_hp }}</template>
        </Column>
        <Column header="Specialization" field="poli" style="min-width: 12rem">
          <template #body="{ data }">{{ data.poli }}</template>
        </Column>
        <Column header="Action" style="min-width: 8rem">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" class="p-button-rounded p-button-info" @click="openEditModal(data)" />
            <Button icon="pi pi-trash" class="p-button-rounded p-button-danger ml-2" @click="deleteDoctor(data.id)" />
          </template>
        </Column>
      </DataTable>

      <Dialog v-model:visible="editModalVisible" header="Edit Doctor" :style="{ width: '50vw' }" modal>
  <div class="p-fluid grid">
    <div class="field col-12 md:col-6">
      <label for="editNama">Name</label>
      <InputText id="editNama" v-model="editDoctorData.nama" placeholder="Enter doctor's name" />
    </div>
    <div class="field col-12 md:col-6">
      <label for="editEmail">Email</label>
      <InputText id="editEmail" v-model="editDoctorData.email" placeholder="Enter doctor's email" />
    </div>
    <div class="field col-12 md:col-6">
      <label for="editPassword">Password</label>
      <InputText id="editPassword" v-model="editDoctorData.password" placeholder="Enter new password" type="password" />
    </div>
    <div class="field col-12 md:col-6">
      <label for="editPasswordConfirmation">Confirm Password</label>
      <InputText id="editPasswordConfirmation" v-model="editDoctorData.password_confirmation" placeholder="Confirm new password" type="password" />
    </div>
    <div class="field col-12 md:col-6">
      <label for="editAlamat">Address</label>
      <InputText id="editAlamat" v-model="editDoctorData.alamat" placeholder="Enter doctor's address" />
    </div>
    <div class="field col-12 md:col-6">
      <label for="editNoHp">Phone Number</label>
      <InputText id="editNoHp" v-model="editDoctorData.no_hp" placeholder="Enter doctor's phone number" />
    </div>
    <div class="field col-12 md:col-6">
      <label for="editPoli">Specialization</label>
      <Dropdown id="editPoli" v-model="editDoctorData.id_poli" :options="polis" placeholder="Select Specialization" />
    </div>
  </div>
  <div class="text-right mt-4">
    <Button label="Save" class="p-button-primary" @click="updateDoctor" />
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
