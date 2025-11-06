<template>
    <div class="stats-card" :class="cardClass">
        <div class="stats-icon" :class="iconClass">
            <i :class="icon"></i>
        </div>
        
        <div class="stats-content">
            <div class="stats-title">{{ title }}</div>
            <div class="stats-value">{{ formattedValue }}</div>
            
            <div class="stats-trend" :class="trendClass" v-if="showTrend">
                <span class="trend-icon">
                    <i :class="trendIcon"></i>
                </span>
                <span class="trend-value">{{ trendValue }}</span>
                <span class="trend-label">{{ trendLabel }}</span>
            </div>
            
            <div class="stats-description" v-if="description">
                {{ description }}
            </div>
        </div>
        
        <div class="stats-badge" v-if="badge" :class="badgeClass">
            {{ badge }}
        </div>
    </div>
</template>

<script>
import { computed } from 'vue'

export default {
    name: 'StatsCard',
    props: {
        title: {
            type: String,
            required: true
        },
        value: {
            type: [Number, String],
            required: true
        },
        description: {
            type: String,
            default: ''
        },
        type: {
            type: String,
            default: 'default', // 'default', 'primary', 'success', 'warning', 'danger', 'info'
            validator: (value) => ['default', 'primary', 'success', 'warning', 'danger', 'info'].includes(value)
        },
        icon: {
            type: String,
            default: '📊'
        },
        trend: {
            type: Number,
            default: null // positive or negative percentage
        },
        trendPeriod: {
            type: String,
            default: 'من آخر فترة'
        },
        badge: {
            type: String,
            default: ''
        },
        badgeType: {
            type: String,
            default: 'info',
            validator: (value) => ['info', 'success', 'warning', 'danger'].includes(value)
        },
        format: {
            type: String,
            default: 'number', // 'number', 'currency', 'percentage', 'custom'
            validator: (value) => ['number', 'currency', 'percentage', 'custom'].includes(value)
        },
        currency: {
            type: String,
            default: 'SAR'
        },
        precision: {
            type: Number,
            default: 0
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    
    setup(props) {
        // تنسيق القيمة
        const formattedValue = computed(() => {
            if (props.loading) return '...'
            
            const value = parseFloat(props.value)
            
            switch (props.format) {
                case 'currency':
                    return new Intl.NumberFormat('ar-SA', {
                        style: 'currency',
                        currency: props.currency,
                        minimumFractionDigits: props.precision
                    }).format(value)
                
                case 'percentage':
                    return new Intl.NumberFormat('ar-SA', {
                        style: 'percent',
                        minimumFractionDigits: props.precision
                    }).format(value / 100)
                
                case 'custom':
                    return props.value
                
                default: // number
                    return new Intl.NumberFormat('ar-SA', {
                        minimumFractionDigits: props.precision,
                        maximumFractionDigits: props.precision
                    }).format(value)
            }
        })

        // اتجاه المؤشر
        const showTrend = computed(() => props.trend !== null)
        const isPositiveTrend = computed(() => props.trend > 0)
        const isNegativeTrend = computed(() => props.trend < 0)

        const trendValue = computed(() => {
            return Math.abs(props.trend).toFixed(1) + '%'
        })

        const trendLabel = computed(() => {
            return props.trendPeriod
        })

        const trendClass = computed(() => {
            return {
                'trend-positive': isPositiveTrend.value,
                'trend-negative': isNegativeTrend.value,
                'trend-neutral': !isPositiveTrend.value && !isNegativeTrend.value
            }
        })

        const trendIcon = computed(() => {
            if (isPositiveTrend.value) return 'trend-up'
            if (isNegativeTrend.value) return 'trend-down'
            return 'trend-neutral'
        })

        // كلاسات التصميم
        const cardClass = computed(() => {
            return `stats-card-${props.type}`
        })

        const iconClass = computed(() => {
            return `stats-icon-${props.type}`
        })

        const badgeClass = computed(() => {
            return `stats-badge-${props.badgeType}`
        })

        return {
            formattedValue,
            showTrend,
            trendValue,
            trendLabel,
            trendClass,
            trendIcon,
            cardClass,
            iconClass,
            badgeClass
        }
    }
}
</script>

<style scoped>
.stats-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    position: relative;
    transition: all 0.3s ease;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    min-height: 120px;
}

.stats-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

/* أنواع البطاقات */
.stats-card-default {
    border-left: 4px solid #6b7280;
}

.stats-card-primary {
    border-left: 4px solid #3b82f6;
}

.stats-card-success {
    border-left: 4px solid #10b981;
}

.stats-card-warning {
    border-left: 4px solid #f59e0b;
}

.stats-card-danger {
    border-left: 4px solid #ef4444;
}

.stats-card-info {
    border-left: 4px solid #06b6d4;
}

/* الأيقونة */
.stats-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.stats-icon-default {
    background: #f3f4f6;
    color: #6b7280;
}

.stats-icon-primary {
    background: #dbeafe;
    color: #3b82f6;
}

.stats-icon-success {
    background: #d1fae5;
    color: #10b981;
}

.stats-icon-warning {
    background: #fef3c7;
    color: #f59e0b;
}

.stats-icon-danger {
    background: #fee2e2;
    color: #ef4444;
}

.stats-icon-info {
    background: #cffafe;
    color: #06b6d4;
}

/* المحتوى */
.stats-content {
    flex: 1;
    min-width: 0;
}

.stats-title {
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-value {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
    line-height: 1.2;
}

.stats-description {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 4px;
}

/* المؤشر */
.stats-trend {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 500;
    margin-top: 4px;
}

.trend-icon {
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.trend-positive {
    color: #10b981;
}

.trend-positive .trend-icon {
    background: #d1fae5;
}

.trend-negative {
    color: #ef4444;
}

.trend-negative .trend-icon {
    background: #fee2e2;
}

.trend-neutral {
    color: #6b7280;
}

.trend-neutral .trend-icon {
    background: #f3f4f6;
}

.trend-value {
    font-weight: 600;
}

.trend-label {
    color: #9ca3af;
    font-weight: 400;
}

/* أيقونات المؤشر */
.trend-up::before {
    content: '↗';
}

.trend-down::before {
    content: '↘';
}

.trend-neutral::before {
    content: '→';
}

/* البادجات */
.stats-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-badge-info {
    background: #dbeafe;
    color: #3b82f6;
}

.stats-badge-success {
    background: #d1fae5;
    color: #10b981;
}

.stats-badge-warning {
    background: #fef3c7;
    color: #f59e0b;
}

.stats-badge-danger {
    background: #fee2e2;
    color: #ef4444;
}

/* حالة التحميل */
.stats-card.loading {
    opacity: 0.7;
    pointer-events: none;
}

.stats-card.loading .stats-value {
    background: #f3f4f6;
    color: transparent;
    border-radius: 4px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        opacity: 0.6;
    }
    50% {
        opacity: 0.8;
    }
    100% {
        opacity: 0.6;
    }
}

/* التصميم المضغوط */
.stats-card.compact {
    padding: 16px;
    min-height: 100px;
}

.stats-card.compact .stats-icon {
    width: 44px;
    height: 44px;
    font-size: 20px;
}

.stats-card.compact .stats-value {
    font-size: 22px;
}

.stats-card.compact .stats-title {
    font-size: 12px;
}

/* التصميم الأفقي */
.stats-card.horizontal {
    flex-direction: row;
    align-items: center;
    text-align: center;
}

.stats-card.horizontal .stats-content {
    text-align: center;
}

.stats-card.horizontal .stats-trend {
    justify-content: center;
}

/* التجاوب */
@media (max-width: 768px) {
    .stats-card {
        padding: 20px;
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }

    .stats-icon {
        align-self: center;
    }

    .stats-trend {
        justify-content: center;
    }

    .stats-badge {
        position: static;
        align-self: flex-start;
    }
}

@media (max-width: 480px) {
    .stats-card {
        padding: 16px;
    }

    .stats-value {
        font-size: 24px;
    }

    .stats-icon {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }
}

/* تأثيرات خاصة */
.stats-card.glow {
    position: relative;
    overflow: hidden;
}

.stats-card.glow::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, currentColor, transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stats-card.glow:hover::before {
    opacity: 1;
}

.stats-card.glow.stats-card-primary::before {
    background: linear-gradient(90deg, transparent, #3b82f6, transparent);
}

.stats-card.glow.stats-card-success::before {
    background: linear-gradient(90deg, transparent, #10b981, transparent);
}

/* تدرج الألوان */
.stats-card.gradient {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: none;
}

.stats-card.gradient.stats-card-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.stats-card.gradient.stats-card-primary .stats-title,
.stats-card.gradient.stats-card-primary .stats-value,
.stats-card.gradient.stats-card-primary .stats-description {
    color: white;
}

.stats-card.gradient.stats-card-primary .stats-icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* الظلال الملونة */
.stats-card.shadow-primary {
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
}

.stats-card.shadow-success {
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.1);
}

.stats-card.shadow-warning {
    box-shadow: 0 4px 6px rgba(245, 158, 11, 0.1);
}

.stats-card.shadow-danger {
    box-shadow: 0 4px 6px rgba(239, 68, 68, 0.1);
}
</style>
