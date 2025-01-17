<script setup>
import { useLayout } from '@/primevue/layout/composables/layout';
import { computed } from 'vue';

const { layoutConfig } = useLayout();

const logoUrl = computed(() => {
    return `layout/images/${layoutConfig.darkTheme.value ? 'logo-white' : 'logo-dark'}.svg`;
});
</script>

<template>
    <div class="layout-footer">
        &copy; 2024 Sistem Janji Temu. All rights reserved.
        <span class="font-medium ml-2">Appoinment System Team</span>
    </div>
</template>
<style lang="scss" scoped></style>
