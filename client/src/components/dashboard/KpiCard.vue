<template>
    <div class="kpi-card" :style="{ '--kpi-color': color }">
        <div v-if="loading" class="kpi-skeleton">
            <div class="skel skel-label"></div>
            <div class="skel skel-value"></div>
            <div class="skel skel-sub"></div>
        </div>
        <template v-else>
            <div class="kpi-label">
                <span class="kpi-icon">{{ icon }}</span>
                {{ label }}
            </div>
            <div class="kpi-value">{{ value }}</div>
            <div class="kpi-footer">
                <span class="kpi-sub">{{ sub }}</span>
                <span v-if="growth !== null && growth !== undefined" class="kpi-growth"
                    :class="parseFloat(growth) >= 0 ? 'growth-pos' : 'growth-neg'">{{ growth }}</span>
            </div>
        </template>
        <div class="kpi-bar"></div>
    </div>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], default: '—' },
    sub: { type: String, default: '' },
    growth: { type: [String, Number], default: null },
    color: { type: String, default: '#4f8ef7' },
    loading: { type: Boolean, default: false },
    icon: { type: String, default: '📊' },
})
</script>

<style scoped>
.kpi-card {
    background: #111827;
    border: 1px solid rgba(99, 130, 201, 0.15);
    border-radius: 14px;
    padding: 18px 18px 14px;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    cursor: default;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
    border-color: rgba(99, 130, 201, 0.3);
}

.kpi-bar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: 14px 14px 0 0;
    background: var(--kpi-color, #4f8ef7);
    opacity: 0.85;
}

.kpi-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #7a91b8;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.kpi-icon {
    font-size: 14px;
}

.kpi-value {
    font-size: 30px;
    font-weight: 800;
    line-height: 1;
    color: var(--kpi-color, #4f8ef7);
    margin-bottom: 8px;
    letter-spacing: -1px;
}

.kpi-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 4px;
}

.kpi-sub {
    font-size: 12px;
    color: #7a91b8;
}

.kpi-growth {
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
}

.growth-pos {
    background: rgba(34, 211, 165, 0.15);
    color: #22d3a5;
}

.growth-neg {
    background: rgba(224, 92, 122, 0.15);
    color: #e05c7a;
}

/* Skeleton */
.kpi-skeleton {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.skel {
    border-radius: 6px;
    background: linear-gradient(90deg, #1a2236 25%, #212d45 50%, #1a2236 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

.skel-label {
    height: 11px;
    width: 60%;
}

.skel-value {
    height: 30px;
    width: 50%;
}

.skel-sub {
    height: 11px;
    width: 80%;
}

@keyframes shimmer {
    from {
        background-position: 200% 0;
    }

    to {
        background-position: -200% 0;
    }
}
</style>