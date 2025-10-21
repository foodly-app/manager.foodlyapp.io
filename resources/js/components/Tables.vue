<template>
    <div class="tables-page">
        <h2 class="text-2xl font-bold mb-4">{{ $t('menu.tables') }}</h2>
        <p class="text-gray-600">Tables management page - Under development</p>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<style scoped>
.tables-page {
    padding: 1rem;
}
</style>
