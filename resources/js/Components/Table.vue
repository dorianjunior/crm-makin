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
    <div class="table-wrapper">
        <table
            :class="[
                'table',
                { 'table--striped': striped },
                { 'table--hoverable': hoverable },
                { 'table--bordered': bordered },
            ]"
        >
            <!-- Header -->
            <thead class="table__head">
                <tr class="table__row">
                    <th
                        v-for="(column, index) in columns"
                        :key="index"
                        :class="[
                            'table__cell table__cell--header',
                            column.align ? `table__cell--${column.align}` : '',
                            column.width ? '' : 'table__cell--auto',
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
            <tbody class="table__body">
                <!-- Loading State -->
                <tr v-if="loading" class="table__row">
                    <td :colspan="columns.length" class="table__cell table__cell--loading">
                        <div class="table__loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Carregando...</span>
                        </div>
                    </td>
                </tr>

                <!-- Empty State -->
                <tr v-else-if="data.length === 0" class="table__row">
                    <td :colspan="columns.length" class="table__cell table__cell--empty">
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
                    class="table__row table__row--body"
                    @click="handleRowClick(row, rowIndex)"
                >
                    <td
                        v-for="(column, colIndex) in columns"
                        :key="colIndex"
                        :class="[
                            'table__cell',
                            column.align ? `table__cell--${column.align}` : '',
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
.table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 2px solid var(--border-color);
}

.table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Inter', sans-serif;
    background: var(--bg-primary);
}

/* Header */
.table__head {
    background: var(--bg-secondary);
}

.table__cell--header {
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
.table__body .table__cell {
    padding: 16px 20px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
}

.table__row--body:last-child .table__cell {
    border-bottom: none;
}

/* Alignment */
.table__cell--left {
    text-align: left;
}

.table__cell--center {
    text-align: center;
}

.table__cell--right {
    text-align: right;
}

.table__cell--auto {
    width: auto;
}

/* Bordered */
.table--bordered .table__cell {
    border-right: 1px solid var(--border-color);
}

.table--bordered .table__cell:last-child {
    border-right: none;
}

/* Striped */
.table--striped .table__row--body:nth-child(even) {
    background: var(--bg-secondary);
}

/* Hoverable */
.table--hoverable .table__row--body {
    cursor: pointer;
    transition: all 120ms ease;
}

.table--hoverable .table__row--body:hover {
    background: var(--bg-secondary);
}

/* Loading State */
.table__cell--loading,
.table__cell--empty {
    padding: 48px 20px;
    text-align: center;
    color: var(--text-muted);
}

.table__loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-size: 14px;
}

.table__loading i {
    font-size: 20px;
    color: var(--color-accent);
}

/* Scrollbar */
.table-wrapper::-webkit-scrollbar {
    height: 8px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: var(--bg-primary);
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border: 2px solid var(--bg-primary);
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}

/* Dark mode */
:root[data-theme='dark'] .table {
    background: var(--bg-secondary);
}

:root[data-theme='dark'] .table__head {
    background: var(--bg-tertiary);
}
</style>
