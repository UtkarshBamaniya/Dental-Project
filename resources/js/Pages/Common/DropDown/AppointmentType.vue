<script setup>
import axios from 'axios';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';
import { onMounted, ref, watch } from 'vue';

const toast = useToast();

const props = defineProps({
    modelValue: {
        type: [Number, String, null],
        default: null,
    },
    placeholder: {
        type: String,
        default: 'Select appointment type',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    invalid: {
        type: Boolean,
        default: false,
    },
    inputClass: {
        type: [String, Array, Object],
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const options = ref([]);
const loading = ref(false);
const selectedValue = ref(props.modelValue);

const fetchAppointmentTypes = async (selectedId = null) => {
    try {
        loading.value = true;
        const response = await axios.post('/common/get-appointment-types', {
            id: selectedId,
        });
        options.value = Array.isArray(response.data) ? response.data : [];
    } catch (error) {
        console.error('Error fetching appointment types:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to load appointment types',
            life: 3000,
        });
        options.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchAppointmentTypes(props.modelValue);
});

watch(
    () => props.modelValue,
    (newValue) => {
        selectedValue.value = newValue;

        if (newValue && !options.value.some((option) => option.id === newValue)) {
            fetchAppointmentTypes(newValue);
        }
    },
);

watch(
    () => selectedValue.value,
    (newValue) => {
        emit('update:modelValue', newValue);
    },
);
</script>

<template>
    <Select
        v-model="selectedValue"
        :options="options"
        optionLabel="name"
        optionValue="id"
        :placeholder="placeholder"
        :disabled="disabled"
        :loading="loading"
        :invalid="invalid"
        :class="inputClass"
    />
</template>
