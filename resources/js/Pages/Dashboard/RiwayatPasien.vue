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

          <Column field="nama_pasien" header="Nama Pasien" />
          <Column field="tgl_periksa" header="Tanggal Periksa" />
          <Column field="biaya_periksa" header="Biaya Periksa" />
          <Column field="catatan" header="Catatan" />
          <Column header="Obat">
            <template #body="{ data }">
              <ul>
                <li v-for="detail in data.detailPeriksa" :key="detail.id">{{ detail.obat.nama_obat }}</li>
              </ul>
            </template>
          </Column>
          <Column header="Action">
            <template #body="{ data }">
              <Button 
                icon="pi pi-pencil" 
                @click="editPeriksa(data.id)" 
                class="p-button-rounded p-button-info" 
              />
            </template>
          </Column>
        </DataTable>
      </div>

      <Dialog v-model:visible="isEditModalVisible" header="Edit Data Periksa" :modal="true" :closable="true">
        <div class="p-fluid">
          <div class="field">
            <label for="nama_pasien">Nama Pasien</label>
            <InputText v-model="editData.nama_pasien" id="nama_pasien" readonly />
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
          <Button label="Save" class="p-button-primary" @click="saveEdit" />
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

const riwayatPeriksa = ref([]);
const filters = ref(null);
const loading = ref(false);
const isEditModalVisible = ref(false);
const editData = ref({});
const obatList = ref([]);
const selectedObat = ref([]);
const biayaJasaDokter = 150000;

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/riwayat-periksa');
    riwayatPeriksa.value = response.data;
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

const editPeriksa = async (id) => {
  const periksaData = riwayatPeriksa.value.find(item => item.id === id);
  if (!periksaData) return;

  await fetchObatData();

  editData.value = {
    id: periksaData.id,
    id_daftar_poli: periksaData.id_daftar_poli, // Tambahkan id_daftar_poli
    nama_pasien: periksaData.pasien.name, // Past ikan nama pasien diambil dari relasi yang benar
    tgl_periksa: periksaData.tgl_periksa,
    catatan: periksaData.catatan,
    obat: periksaData.detailPeriksa.map(detail => detail.id_obat),
    biaya_periksa: periksaData.biaya_periksa,
  };

  selectedObat.value = periksaData.detailPeriksa.map(detail => detail.id_obat);
  isEditModalVisible.value = true;
};

watch(selectedObat, () => {
  const biayaObat = selectedObat.value.reduce((total, id) => {
    const obat = obatList.value.find(obat => obat.id === id);
    return total + (obat ? obat.harga : 0);
  }, 0);

  editData.value.biaya_periksa = biayaObat + biayaJasaDokter;
}, { immediate: true });

const saveEdit = async () => {
  try {
    const response = await axios.put(`/riwayat-periksa/${editData.value.id}`, {
      id_daftar_poli: editData.value.id_daftar_poli, // Sertakan id_daftar_poli dalam payload
      tgl_periksa: editData.value.tgl_periksa,
      biaya_periksa: editData.value.biaya_periksa,
      catatan: editData.value.catatan,
      obats: selectedObat.value,
    });

    Swal.fire("Success", "Data berhasil disimpan.", "success");

    // Perbarui data di frontend
    const periksaIndex = riwayatPeriksa.value.findIndex(periksa => periksa.id === editData.value.id);
    if (periksaIndex !== -1) {
      riwayatPeriksa.value[periksaIndex] = { ...editData.value, detailPeriksa: selectedObat.value.map(id => ({ id_obat: id, obat: obatList.value.find(obat => obat.id === id) })) };
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