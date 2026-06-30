<script setup>
import axios from 'axios';
import MultiSelect from 'primevue/multiselect';
import { useToast } from 'primevue/usetoast';
import { onMounted, ref, watch } from 'vue';

const toast = useToast();

const props = defineProps({
    medicals: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Select Medical Details',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['value', 'update:medicals']);

const options = ref([]);
const loading = ref(false);
const selectedMedicals = ref(props.medicals);
const minSearchLength = 2;

const loadInitialValues = async () => {
    if (!props.medicals || props.medicals.length === 0) {
        return;
    }

    try {
        loading.value = true;
        const response = await axios.post('/common/get-medical-details', {
            ids: props.medicals,
        });

        options.value = Array.isArray(response.data) ? response.data : [];
        selectedMedicals.value = [...props.medicals];
    } catch (error) {
        console.error('Error loading medical details:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to load medical details',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

const fetchMedicals = async (query = '') => {
    if (!query || query.length < minSearchLength) {
        return;
    }

    try {
        loading.value = true;
        const response = await axios.post('/common/get-medical-details', {
            params: { query },
        });
        options.value = Array.isArray(response.data) ? response.data : [];
    } catch (error) {
        console.error('Error fetching medical details:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to fetch medical details',
            life: 3000,
        });
        options.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadInitialValues();
    fetchMedicals('  ');
});

watch(
    () => props.medicals,
    (newValue) => {
        if (JSON.stringify(newValue) !== JSON.stringify(selectedMedicals.value)) {
            selectedMedicals.value = [...newValue];
            loadInitialValues();
        }
    },
    { immediate: true },
);

watch(
    () => selectedMedicals.value,
    (newValue) => {
        emit('value', newValue);
        emit('update:medicals', newValue);
    },
);
</script>

<template>
    <div class="medical-selector">
        <MultiSelect
            v-model="selectedMedicals"
            :options="options"
            optionLabel="name"
            optionValue="id"
            :filter="true"
            :loading="loading"
            :disabled="disabled"
            :placeholder="placeholder"
            class="w-full"
            display="chip"
            autoFilterFocus
            autoOptionFocus
            resetFilterOnHide
            @filter="fetchMedicals($event.value)"
        />
    </div>
</template>

<style scoped>
.medical-selector :deep(.p-multiselect) {
    width: 100%;
    min-height: 2.5rem;
}

.medical-selector :deep(.p-multiselect-label) {
    min-height: 2.5rem;
    display: flex;
    align-items: center;
}

.medical-selector :deep(.p-multiselect-token) {
    padding: 0.15rem 0.5rem;
    margin: 0.1rem;
}
</style>
