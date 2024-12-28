<template>
  <app-layout>
    <div>
      <div class="card">
        <h5>Daftar Poli</h5>
        <DataTable
          :value="daftarPoli"
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

          <Column field="name" header="Nama Pasien" />
          <Column field="keluhan" header="Complaint" />
          <Column field="no_antrian" header="Queue No" />
          <Column header="Action">
            <template #body="{ data }">
    <Button 
      :icon="data.isSaved ? 'pi pi-check' : 'pi pi-pencil'" 
      @click="data.isSaved ? null : editPasien(data.id)" 
      :class="data.isSaved ? 'p-button-rounded p-button-success' : 'p-button-rounded p-button-info'" 
      :disabled="data.isSaved" 
    />
  </template>
          </Column>
        </DataTable>
      </div>

      <Dialog v-model:visible="isEditModalVisible" header="Edit Data Periksa" :modal="true" :closable="true">
        <div class="p-fluid">
          <div class="field">
            <label for="name">Nama Pasien</label>
            <InputText v-model="editData.name" id="name" readonly />
          </div>

          <div class="field">
            <label for="tgl_periksa">Tanggal Periksa</label>
            <Calendar v-model="editData.tgl_periksa" id="tgl_periksa" placeholder="Pilih Tanggal" dateFormat="yy-mm-dd" />
          </div>

          <div class="field">
            <label for="catatan">Catatan</label>
            <InputText v-model="editData.catatan" id="catatan" />
          </div>

          <div class="field">
            <label for="obat">Obat</label>
            <MultiSelect 
              v-model="selectedObat"
              :options="obatList"
              optionLabel="nama_obat"
              optionValue="id"
              placeholder="Pilih Obat"
              :filter="true"
              display="chip"
            />
          </div>

          <div class="field">
            <label for="biaya_periksa">Biaya Periksa</label>
            <InputText v-model="editData.biaya_periksa" id="biaya_periksa" readonly />
          </div>
        </div>

        <template #footer>
          <Button label="Save" class="p-button-primary" @click="saveEdit" :loading="isSaving" />
          <Button label="Cancel" class="p-button-secondary ml-2" @click="isEditModalVisible = false" />
        </template>
      </Dialog>
    </div>
  </app-layout>
</template>

<script setup>
import { ref, onBeforeMount, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { FilterMatchMode } from 'primevue/api';
import AppLayout from "@/primevue/layout/AppLayoutDokter.vue";
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Calendar from 'primevue/calendar';
import MultiSelect from 'primevue/multiselect';

const daftarPoli = ref([]);
const filters = ref(null);
const loading = ref(false);
const isEditModalVisible = ref(false);
const editData = ref({});
const obatList = ref([]);
const selectedObat = ref([]);
const isSaving = ref(false);
const biayaJasaDokter = 150000;

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/periksa');
    daftarPoli.value = response.data;
  } catch (error) {
    console.error("Error fetching data:", error);
  } finally {
    loading.value = false;
  }
};

const fetchObatData = async () => {
  try {
    const response = await axios.get('/obatt');
    obatList.value = response.data;
  } catch (error) {
    console.error("Error fetching obat data:", error);
  }
};

const editPasien = async (id) => {
  const poliData = daftarPoli.value.find(item => item.id === id);
  if (!poliData) return;

  await fetchObatData();

  editData.value = {
    id_daftar_poli: poliData.id,
    name: poliData.name,
    tgl_periksa: null,
    catatan: "",
    obat: [],
    biaya_periksa: biayaJasaDokter,
  };

  selectedObat.value = [];
  isEditModalVisible.value = true;
};

watch(selectedObat, () => {
  if (!editData.value.id_daftar_poli) {
    const biayaObat = selectedObat.value.reduce((total, id) => total + (obatList.value.find(obat => obat.id === id)?.harga || 0), 0);
    editData.value.biaya_periksa = biayaObat + biayaJasaDokter;
  }
}, { immediate: true });

const saveEdit = async () => {
    try {
        const response = await axios.post('/periksa', {
            id_daftar_poli: editData.value.id_daftar_poli,
            tgl_periksa: editData.value.tgl_periksa,
            biaya_periksa: editData.value.biaya_periksa,
            catatan: editData.value.catatan,
            obats: selectedObat.value,
        });

        Swal.fire("Success", "Data berhasil disimpan.", "success");

        // Tandai data sebagai tersimpan di frontend
        const poliIndex = daftarPoli.value.findIndex(poli => poli.id === editData.value.id_daftar_poli);
        if (poliIndex !== -1) {
            daftarPoli.value[poliIndex].isSaved = true;
        }

        isEditModalVisible.value = false;
    } catch (error) {
        console.error("Error saving data:", error);
        Swal.fire("Error", "Gagal menyimpan data.", "error");
    }
};


onBeforeMount(() => {
  filters.value = { global: { value: null, matchMode: FilterMatchMode.CONTAINS } };
  fetchData();
});
</script>
