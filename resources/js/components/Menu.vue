<template>
    <div class="menu-page">
        <h2 class="text-2xl font-bold mb-4">{{ $t('menu.menu') }}</h2>
        <p class="text-gray-600">Menu management page - Under development</p>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<style scoped>
.menu-page {
    padding: 1rem;
}
</style>
