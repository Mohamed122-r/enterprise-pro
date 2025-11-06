<template>
    <div class="pagination-container" v-if="totalPages > 1">
        <div class="pagination-info" v-if="showInfo">
            عرض {{ startItem }} - {{ endItem }} من {{ totalItems }}
        </div>
        
        <nav class="pagination" aria-label="التصفح">
            <ul class="pagination-list">
                <!-- زر الصفحة الأولى -->
                <li class="pagination-item">
                    <button 
                        class="pagination-link first" 
                        :disabled="currentPage === 1"
                        @click="goToPage(1)"
                        aria-label="الصفحة الأولى"
                    >
                        <i class="first-icon"></i>
                    </button>
                </li>

                <!-- زر الصفحة السابقة -->
                <li class="pagination-item">
                    <button 
                        class="pagination-link prev" 
                        :disabled="currentPage === 1"
                        @click="goToPage(currentPage - 1)"
                        aria-label="الصفحة السابقة"
                    >
                        <i class="prev-icon"></i>
                    </button>
                </li>

                <!-- صفحات الرقم -->
                <li 
                    v-for="page in visiblePages" 
                    :key="page"
                    class="pagination-item"
                >
                    <button 
                        class="pagination-link" 
                        :class="{ active: page === currentPage }"
                        @click="goToPage(page)"
                        :aria-label="`الصفحة ${page}`"
                        :aria-current="page === currentPage ? 'page' : null"
                    >
                        {{ page }}
                    </button>
                </li>

                <!-- زر الصفحة التالية -->
                <li class="pagination-item">
                    <button 
                        class="pagination-link next" 
                        :disabled="currentPage === totalPages"
                        @click="goToPage(currentPage + 1)"
                        aria-label="الصفحة التالية"
                    >
                        <i class="next-icon"></i>
                    </button>
                </li>

                <!-- زر الصفحة الأخيرة -->
                <li class="pagination-item">
                    <button 
                        class="pagination-link last" 
                        :disabled="currentPage === totalPages"
                        @click="goToPage(totalPages)"
                        aria-label="الصفحة الأخيرة"
                    >
                        <i class="last-icon"></i>
                    </button>
                </li>
            </ul>
        </nav>

        <!-- اختيار عدد العناصر لكل صفحة -->
        <div class="page-size-selector" v-if="showPageSize">
            <label for="pageSize">عناصر لكل صفحة:</label>
            <select 
                id="pageSize" 
                v-model="localPageSize" 
                @change="onPageSizeChange"
                class="page-size-select"
            >
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</template>

<script>
import { ref, computed, watch } from 'vue'

export default {
    name: 'Pagination',
    props: {
        // البيانات الأساسية
        currentPage: {
            type: Number,
            required: true,
            validator: (value) => value > 0
        },
        totalItems: {
            type: Number,
            required: true,
            default: 0
        },
        pageSize: {
            type: Number,
            default: 10
        },
        
        // الإعدادات
        showInfo: {
            type: Boolean,
            default: true
        },
        showPageSize: {
            type: Boolean,
            default: true
        },
        maxVisiblePages: {
            type: Number,
            default: 5
        },
        
        // التخصيص
        theme: {
            type: String,
            default: 'default', // 'default', 'minimal', 'compact'
            validator: (value) => ['default', 'minimal', 'compact'].includes(value)
        }
    },
    
    emits: ['page-change', 'page-size-change'],
    
    setup(props, { emit }) {
        const localPageSize = ref(props.pageSize)

        // الحسابات
        const totalPages = computed(() => {
            return Math.ceil(props.totalItems / localPageSize.value)
        })

        const startItem = computed(() => {
            return (props.currentPage - 1) * localPageSize.value + 1
        })

        const endItem = computed(() => {
            const end = props.currentPage * localPageSize.value
            return end > props.totalItems ? props.totalItems : end
        })

        const visiblePages = computed(() => {
            const pages = []
            let startPage = 1
            let endPage = totalPages.value

            if (totalPages.value > props.maxVisiblePages) {
                const maxPagesBeforeCurrentPage = Math.floor(props.maxVisiblePages / 2)
                const maxPagesAfterCurrentPage = Math.ceil(props.maxVisiblePages / 2) - 1

                if (props.currentPage <= maxPagesBeforeCurrentPage) {
                    // نحن في البداية
                    startPage = 1
                    endPage = props.maxVisiblePages
                } else if (props.currentPage + maxPagesAfterCurrentPage >= totalPages.value) {
                    // نحن في النهاية
                    startPage = totalPages.value - props.maxVisiblePages + 1
                    endPage = totalPages.value
                } else {
                    // نحن في المنتصف
                    startPage = props.currentPage - maxPagesBeforeCurrentPage
                    endPage = props.currentPage + maxPagesAfterCurrentPage
                }
            }

            // إضافة نقاط إذا لزم الأمر
            if (startPage > 1) {
                pages.push(1)
                if (startPage > 2) {
                    pages.push('...')
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                pages.push(i)
            }

            if (endPage < totalPages.value) {
                if (endPage < totalPages.value - 1) {
                    pages.push('...')
                }
                pages.push(totalPages.value)
            }

            return pages
        })

        // الدوال
        const goToPage = (page) => {
            if (page === '...' || page < 1 || page > totalPages.value || page === props.currentPage) {
                return
            }
            emit('page-change', page)
        }

        const onPageSizeChange = () => {
            emit('page-size-change', parseInt(localPageSize.value))
            // العودة للصفحة الأولى عند تغيير حجم الصفحة
            if (props.currentPage !== 1) {
                emit('page-change', 1)
            }
        }

        // المراقبة
        watch(() => props.pageSize, (newSize) => {
            localPageSize.value = newSize
        })

        return {
            localPageSize,
            totalPages,
            startItem,
            endItem,
            visiblePages,
            goToPage,
            onPageSizeChange
        }
    }
}
</script>

<style scoped>
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    margin-top: 20px;
    border-top: 1px solid #e9ecef;
    flex-wrap: wrap;
    gap: 15px;
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
}

.pagination {
    display: flex;
    align-items: center;
}

.pagination-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 4px;
}

.pagination-item {
    margin: 0;
}

.pagination-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    background-color: #fff;
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    user-select: none;
}

.pagination-link:hover:not(:disabled) {
    background-color: #e9ecef;
    border-color: #dee2e6;
    color: #0056b3;
}

.pagination-link.active {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

.pagination-link:disabled {
    color: #6c757d;
    background-color: #f8f9fa;
    border-color: #dee2e6;
    cursor: not-allowed;
    opacity: 0.6;
}

.pagination-link:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

/* الأزرار الخاصة */
.pagination-link.first,
.pagination-link.prev,
.pagination-link.next,
.pagination-link.last {
    font-size: 12px;
}

.pagination-link.first:hover:not(:disabled),
.pagination-link.prev:hover:not(:disabled),
.pagination-link.next:hover:not(:disabled),
.pagination-link.last:hover:not(:disabled) {
    background-color: #f8f9fa;
}

/* نقاط التقسيم */
.pagination-link[disabled]:not(.active) {
    background-color: transparent;
    border-color: transparent;
    color: #6c757d;
    cursor: default;
}

/* اختيار حجم الصفحة */
.page-size-selector {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #6c757d;
}

.page-size-select {
    padding: 6px 10px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background-color: #fff;
    font-size: 14px;
    cursor: pointer;
    transition: border-color 0.2s;
}

.page-size-select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

/* الثيمات */
.pagination-container.minimal .pagination-link {
    border: none;
    background: transparent;
}

.pagination-container.minimal .pagination-link:hover:not(:disabled) {
    background-color: #f8f9fa;
}

.pagination-container.minimal .pagination-link.active {
    background-color: #007bff;
    color: #fff;
}

.pagination-container.compact .pagination-link {
    min-width: 32px;
    height: 32px;
    padding: 4px 8px;
    font-size: 12px;
}

.pagination-container.compact .pagination-info {
    font-size: 12px;
}

.pagination-container.compact .page-size-selector {
    font-size: 12px;
}

.pagination-container.compact .page-size-select {
    padding: 4px 8px;
    font-size: 12px;
}

/* الأيقونات */
.first-icon::before { content: '⟪'; }
.prev-icon::before { content: '‹'; }
.next-icon::before { content: '›'; }
.last-icon::before { content: '⟫'; }

/* التجاوب */
@media (max-width: 768px) {
    .pagination-container {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }

    .pagination-info {
        order: 1;
    }

    .pagination {
        order: 2;
    }

    .page-size-selector {
        order: 3;
    }

    .pagination-list {
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination-link.first,
    .pagination-link.last {
        display: none;
    }
}

@media (max-width: 480px) {
    .pagination-link {
        min-width: 36px;
        height: 36px;
        padding: 6px 8px;
        font-size: 12px;
    }

    .pagination-info {
        font-size: 12px;
    }

    .page-size-selector {
        font-size: 12px;
    }

    .page-size-select {
        padding: 4px 6px;
        font-size: 12px;
    }
}

/* تأثيرات إضافية */
.pagination-link {
    position: relative;
    overflow: hidden;
}

.pagination-link::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(0, 123, 255, 0.1);
    border-radius: 50%;
    transition: all 0.3s ease;
    transform: translate(-50%, -50%);
}

.pagination-link:active::after {
    width: 100%;
    height: 100%;
    border-radius: 6px;
}

/* تحسينات الوصول */
.pagination-link:focus-visible {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

/* حالة التحميل */
.pagination-link.loading {
    position: relative;
    color: transparent;
}

.pagination-link.loading::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    transform: translate(-50%, -50%);
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* تخصيص الألوان */
.pagination-link.primary.active { background-color: #007bff; border-color: #007bff; }
.pagination-link.success.active { background-color: #28a745; border-color: #28a745; }
.pagination-link.danger.active { background-color: #dc3545; border-color: #dc3545; }
.pagination-link.warning.active { background-color: #ffc107; border-color: #ffc107; color: #212529; }
.pagination-link.info.active { background-color: #17a2b8; border-color: #17a2b8; }
.pagination-link.dark.active { background-color: #343a40; border-color: #343a40; }

.pagination-link.primary:hover:not(:disabled) { color: #0056b3; }
.pagination-link.success:hover:not(:disabled) { color: #1e7e34; }
.pagination-link.danger:hover:not(:disabled) { color: #c82333; }
.pagination-link.warning:hover:not(:disabled) { color: #e0a800; }
.pagination-link.info:hover:not(:disabled) { color: #138496; }
.pagination-link.dark:hover:not(:disabled) { color: #23272b; }
</style>
