<script setup>
const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
    data: {
        type: Array,
        required: true,
    },
    striped: {
        type: Boolean,
        default: false,
    },
    hoverable: {
        type: Boolean,
        default: true,
    },
    bordered: {
        type: Boolean,
        default: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyText: {
        type: String,
        default: 'Nenhum dado disponível',
    },
});

const emit = defineEmits(['row-click']);

const handleRowClick = (row, index) => {
    emit('row-click', row, index);
};

const getCellValue = (row, column) => {
    if (typeof column.field === 'function') {
        return column.field(row);
    }
    return row[column.field];
};
</script>

<template>
    <div class="table-brutalist-wrapper">
        <table
            :class="[
                'table-brutalist',
                { 'table-brutalist--striped': striped },
                { 'table-brutalist--hoverable': hoverable },
                { 'table-brutalist--bordered': bordered },
            ]"
        >
            <!-- Header -->
            <thead class="table-brutalist__head">
                <tr class="table-brutalist__row">
                    <th
                        v-for="(column, index) in columns"
                        :key="index"
                        :class="[
                            'table-brutalist__cell table-brutalist__cell--header',
                            column.align ? `table-brutalist__cell--${column.align}` : '',
                            column.width ? '' : 'table-brutalist__cell--auto',
                        ]"
                        :style="column.width ? { width: column.width } : {}"
                    >
                        <slot :name="`header-${column.field}`" :column="column">
                            {{ column.label }}
                        </slot>
                    </th>
                </tr>
            </thead>

            <!-- Body -->
            <tbody class="table-brutalist__body">
                <!-- Loading State -->
                <tr v-if="loading" class="table-brutalist__row">
                    <td :colspan="columns.length" class="table-brutalist__cell table-brutalist__cell--loading">
                        <div class="table-brutalist__loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Carregando...</span>
                        </div>
                    </td>
                </tr>

                <!-- Empty State -->
                <tr v-else-if="data.length === 0" class="table-brutalist__row">
                    <td :colspan="columns.length" class="table-brutalist__cell table-brutalist__cell--empty">
                        <slot name="empty">
                            {{ emptyText }}
                        </slot>
                    </td>
                </tr>

                <!-- Data Rows -->
                <tr
                    v-else
                    v-for="(row, rowIndex) in data"
                    :key="rowIndex"
                    class="table-brutalist__row table-brutalist__row--body"
                    @click="handleRowClick(row, rowIndex)"
                >
                    <td
                        v-for="(column, colIndex) in columns"
                        :key="colIndex"
                        :class="[
                            'table-brutalist__cell',
                            column.align ? `table-brutalist__cell--${column.align}` : '',
                        ]"
                    >
                        <slot :name="`cell-${column.field}`" :row="row" :value="getCellValue(row, column)" :index="rowIndex">
                            {{ getCellValue(row, column) }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.table-brutalist-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 2px solid var(--border-color);
}

.table-brutalist {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Inter', sans-serif;
    background: var(--bg-primary);
}

/* Header */
.table-brutalist__head {
    background: var(--bg-secondary);
}

.table-brutalist__cell--header {
    padding: 16px 20px;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-primary);
    border-bottom: 2px solid var(--border-color);
    white-space: nowrap;
}

/* Body */
.table-brutalist__body .table-brutalist__cell {
    padding: 16px 20px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
}

.table-brutalist__row--body:last-child .table-brutalist__cell {
    border-bottom: none;
}

/* Alignment */
.table-brutalist__cell--left {
    text-align: left;
}

.table-brutalist__cell--center {
    text-align: center;
}

.table-brutalist__cell--right {
    text-align: right;
}

.table-brutalist__cell--auto {
    width: auto;
}

/* Bordered */
.table-brutalist--bordered .table-brutalist__cell {
    border-right: 1px solid var(--border-color);
}

.table-brutalist--bordered .table-brutalist__cell:last-child {
    border-right: none;
}

/* Striped */
.table-brutalist--striped .table-brutalist__row--body:nth-child(even) {
    background: var(--bg-secondary);
}

/* Hoverable */
.table-brutalist--hoverable .table-brutalist__row--body {
    cursor: pointer;
    transition: all 120ms ease;
}

.table-brutalist--hoverable .table-brutalist__row--body:hover {
    background: var(--bg-secondary);
}

/* Loading State */
.table-brutalist__cell--loading,
.table-brutalist__cell--empty {
    padding: 48px 20px;
    text-align: center;
    color: var(--text-muted);
}

.table-brutalist__loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-size: 14px;
}

.table-brutalist__loading i {
    font-size: 20px;
    color: var(--color-accent);
}

/* Scrollbar */
.table-brutalist-wrapper::-webkit-scrollbar {
    height: 8px;
}

.table-brutalist-wrapper::-webkit-scrollbar-track {
    background: var(--bg-primary);
}

.table-brutalist-wrapper::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border: 2px solid var(--bg-primary);
}

.table-brutalist-wrapper::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}

/* Dark mode */
:root[data-theme='dark'] .table-brutalist {
    background: var(--bg-secondary);
}

:root[data-theme='dark'] .table-brutalist__head {
    background: var(--bg-tertiary);
}
</style>
