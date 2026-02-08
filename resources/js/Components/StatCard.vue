<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    value: [String, Number],
    fontSize: {
        type: String,
        default: '64px',
    },
    icon: String,
    iconColor: {
        type: String,
        default: 'blue',
    },
    trend: String,
    trendValue: String,
    trendUp: Boolean,
});

// Auto-adjust font size based on value length
const dynamicFontSize = computed(() => {
    const valueStr = String(props.value || '');
    const length = valueStr.length;

    if (length <= 6) return '64px';
    if (length <= 10) return '48px';
    if (length <= 15) return '36px';
    if (length <= 20) return '28px';
    return '22px';
});
</script>

<template>
    <div class="stat">
        <div class="stat__accent"></div>
        <div class="stat__icon">
            <i :class="`fas ${icon}`"></i>
        </div>

        <div class="stat__label">{{ title }}</div>
        <div class="stat__number" :style="{ fontSize: dynamicFontSize }">{{ value }}</div>

        <div v-if="trend" class="stat__trend">
            <span :class="['stat__trend-value', trendUp ? 'is-up' : 'is-down']">
                <i :class="trendUp ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                {{ trendValue }}
            </span>
            <span class="stat__trend-label">{{ trend }}</span>
        </div>
    </div>
</template>

<style scoped>
.stat {
    position: relative;
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    padding: 32px;
    overflow: hidden;
    transition: border-color 180ms ease, transform 180ms ease;
}

.stat:hover {
    border-color: #ff6b35;
    transform: translateY(-2px);
}

.stat__accent {
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 0;
    background: #ff6b35;
    transition: height 180ms ease;
}

.stat:hover .stat__accent { height: 100%; }

.stat__icon {
    position: absolute;
    top: 24px;
    right: 24px;
    width: 48px;
    height: 48px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-tertiary);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 180ms ease;
}

.stat:hover .stat__icon {
    background: #ff6b35;
    border-color: #ff6b35;
    color: #fff;
    transform: rotate(4deg) scale(1.06);
}

.stat__label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--text-secondary);
    margin-bottom: 8px;
}

.stat__number {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 64px;
    font-weight: 800;
    line-height: 1;
    color: var(--text-primary);
    transition: color 180ms ease;
    word-break: break-word;
    max-width: calc(100% - 60px);
}

.stat:hover .stat__number { color: #ff6b35; }

.stat__trend {
    margin-top: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
}

.stat__trend-value {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}

.stat__trend-value i { font-size: 10px; }
.stat__trend-value.is-up { color: #16a34a; }
.stat__trend-value.is-down { color: #dc2626; }

.stat__trend-label { color: var(--text-tertiary); font-weight: 500; }
</style>
