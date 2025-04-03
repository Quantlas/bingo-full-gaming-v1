<template>
    <div class="bingo-card">
        <div class="card-header">
            <span v-for="letter in ['B', 'I', 'N', 'G', 'O']" :key="letter">
                {{ letter }}
            </span>
        </div>
        <div class="card-grid">
            <div v-for="(row, rowIndex) in formattedNumbers" :key="rowIndex" class="card-row">
                <div
                    v-for="(num, colIndex) in row"
                    :key="colIndex"
                    :class="[
                        'card-cell',
                        {
                            marked: isMarked(num),
                            free: isFreeSpace(rowIndex, colIndex),
                            'column-highlight': isColumnComplete(colIndex),
                        },
                    ]"
                    @click="toggleMark(num)"
                >
                    <span v-if="!isFreeSpace(rowIndex, colIndex)">{{ num }}</span>
                    <span v-else>FREE</span>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <span>Serial: {{ serialNumber }}</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    numbers: {
        type: [Array, Object],
        required: true,
        validator: (value) => {
            if (Array.isArray(value)) {
                return value.length === 5 && value.every((row) => row.length === 5);
            } else {
                return ['B', 'I', 'N', 'G', 'O'].every((col) => Array.isArray(value[col]));
            }
        },
    },
    markedNumbers: {
        type: [Array, Object],
        default: () => [],
    },
    serialNumber: {
        type: String,
        required: true,
    },
    interactive: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['mark', 'unmark']);

// Formatear numbers para que siempre sea matriz
const formattedNumbers = computed(() => {
    if (Array.isArray(props.numbers)) {
        return props.numbers;
    }
    return [props.numbers.B, props.numbers.I, props.numbers.N, props.numbers.G, props.numbers.O];
});

// Verificar si una columna está completa
const isColumnComplete = (colIndex) => {
    if (!Array.isArray(props.markedNumbers)) {
        const column = ['B', 'I', 'N', 'G', 'O'][colIndex];
        return props.markedNumbers[column]?.length === 5;
    }
    return false;
};

const isFreeSpace = (row, col) => row === 2 && col === 2;

const isMarked = (num) => {
    if (!num) return false;

    if (Array.isArray(props.markedNumbers)) {
        return props.markedNumbers.includes(num);
    } else {
        return Object.values(props.markedNumbers).flat().includes(num);
    }
};

const toggleMark = (num) => {
    if (!props.interactive || isFreeSpace() || !num) return;

    if (isMarked(num)) {
        emit('unmark', num);
    } else {
        emit('mark', num);
    }
};
</script>

<style scoped>
.column-highlight {
    background-color: rgba(16, 185, 129, 0.1);
    border-color: #10b981;
}
</style>
