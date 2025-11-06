<template>
    <div class="attendance-list">
        <div class="header">
            <h2>قائمة الحضور والانصراف</h2>
            <div class="actions">
                <button class="btn btn-primary" @click="exportReport">
                    <i class="export-icon"></i> تصدير التقرير
                </button>
                <button class="btn btn-secondary" @click="showFilters = !showFilters">
                    <i class="filter-icon"></i> الفلاتر
                </button>
            </div>
        </div>

        <!-- فلاتر البحث -->
        <div class="filters-section" v-if="showFilters">
            <div class="filter-row">
                <div class="filter-group">
                    <label>من تاريخ</label>
                    <input type="date" v-model="filters.startDate" class="form-control">
                </div>
                <div class="filter-group">
                    <label>إلى تاريخ</label>
                    <input type="date" v-model="filters.endDate" class="form-control">
                </div>
                <div class="filter-group">
                    <label>القسم</label>
                    <select v-model="filters.department" class="form-control">
                        <option value="">جميع الأقسام</option>
                        <option value="it">تقنية المعلومات</option>
                        <option value="hr">الموارد البشرية</option>
                        <option value="sales">المبيعات</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>الحالة</label>
                    <select v-model="filters.status" class="form-control">
                        <option value="">جميع الحالات</option>
                        <option value="present">حاضر</option>
                        <option value="absent">غائب</option>
                        <option value="late">متأخر</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" @click="applyFilters">تطبيق</button>
                <button class="btn btn-outline" @click="resetFilters">إعادة تعيين</button>
            </div>
        </div>

        <!-- إحصائيات سريعة -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon present"></div>
                <div class="stat-info">
                    <h3>{{ stats.totalPresent }}</h3>
                    <p>حضور</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon absent"></div>
                <div class="stat-info">
                    <h3>{{ stats.totalAbsent }}</h3>
                    <p>غياب</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon late"></div>
                <div class="stat-info">
                    <h3>{{ stats.totalLate }}</h3>
                    <p>تأخير</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon percentage"></div>
                <div class="stat-info">
                    <h3>{{ stats.attendanceRate }}%</h3>
                    <p>نسبة الحضور</p>
                </div>
            </div>
        </div>

        <!-- جدول الحضور -->
        <div class="table-container">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الموظف</th>
                        <th>القسم</th>
                        <th>التاريخ</th>
                        <th>وقت الدخول</th>
                        <th>وقت الخروج</th>
                        <th>المدة</th>
                        <th>الحالة</th>
                        <th>ملاحظات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(record, index) in attendanceRecords" :key="record.id" 
                        :class="getStatusClass(record.status)">
                        <td>{{ (currentPage - 1) * pageSize + index + 1 }}</td>
                        <td>
                            <div class="employee-info">
                                <img :src="record.employee.avatar" :alt="record.employee.name" class="avatar">
                                <div>
                                    <div class="employee-name">{{ record.employee.name }}</div>
                                    <div class="employee-id">{{ record.employee.id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ record.employee.department }}</td>
                        <td>{{ formatDate(record.date) }}</td>
                        <td>{{ record.checkIn }}</td>
                        <td>{{ record.checkOut || '--' }}</td>
                        <td>{{ calculateDuration(record.checkIn, record.checkOut) }}</td>
                        <td>
                            <span class="status-badge" :class="record.status">
                                {{ getStatusText(record.status) }}
                            </span>
                        </td>
                        <td>
                            <span v-if="record.notes" class="notes" :title="record.notes">
                                {{ truncateText(record.notes, 30) }}
                            </span>
                            <span v-else class="no-notes">--</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon edit" @click="editRecord(record)" title="تعديل">
                                    <i class="edit-icon"></i>
                                </button>
                                <button class="btn-icon delete" @click="deleteRecord(record.id)" title="حذف">
                                    <i class="delete-icon"></i>
                                </button>
                                <button class="btn-icon view" @click="viewDetails(record)" title="تفاصيل">
                                    <i class="view-icon"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- حالة عدم وجود بيانات -->
            <div v-if="attendanceRecords.length === 0" class="empty-state">
                <i class="empty-icon"></i>
                <h3>لا توجد سجلات حضور</h3>
                <p>لم يتم العثور على أي سجلات حضور تطابق معايير البحث الخاصة بك.</p>
            </div>
        </div>

        <!-- الترقيم -->
        <div class="pagination-container" v-if="attendanceRecords.length > 0">
            <div class="pagination-info">
                عرض {{ (currentPage - 1) * pageSize + 1 }} - {{ Math.min(currentPage * pageSize, totalRecords) }} من {{ totalRecords }}
            </div>
            <div class="pagination">
                <button class="pagination-btn" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
                    السابق
                </button>
                <button v-for="page in visiblePages" :key="page" 
                        class="pagination-btn" 
                        :class="{ active: page === currentPage }"
                        @click="changePage(page)">
                    {{ page }}
                </button>
                <button class="pagination-btn" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">
                    التالي
                </button>
            </div>
        </div>

        <!-- مودال التعديل -->
        <div v-if="showEditModal" class="modal-overlay">
            <div class="modal">
                <div class="modal-header">
                    <h3>تعديل سجل الحضور</h3>
                    <button class="close-btn" @click="closeEditModal">&times;</button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveEdit">
                        <div class="form-row">
                            <div class="form-group">
                                <label>وقت الدخول</label>
                                <input type="time" v-model="editForm.checkIn" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>وقت الخروج</label>
                                <input type="time" v-model="editForm.checkOut" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>الحالة</label>
                            <select v-model="editForm.status" class="form-control" required>
                                <option value="present">حاضر</option>
                                <option value="absent">غائب</option>
                                <option value="late">متأخر</option>
                                <option value="vacation">إجازة</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>ملاحظات</label>
                            <textarea v-model="editForm.notes" class="form-control" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closeEditModal">إلغاء</button>
                    <button class="btn btn-primary" @click="saveEdit">حفظ التغييرات</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'

export default {
    name: 'AttendanceList',
    setup() {
        // البيانات التفاعلية
        const showFilters = ref(false)
        const showEditModal = ref(false)
        const currentPage = ref(1)
        const pageSize = 10
        const totalRecords = ref(0)

        // الفلاتر
        const filters = reactive({
            startDate: '',
            endDate: '',
            department: '',
            status: ''
        })

        // نموذج التعديل
        const editForm = reactive({
            id: null,
            checkIn: '',
            checkOut: '',
            status: '',
            notes: ''
        })

        // الإحصائيات
        const stats = reactive({
            totalPresent: 0,
            totalAbsent: 0,
            totalLate: 0,
            attendanceRate: 0
        })

        // بيانات الحضور (بيانات وهمية للعرض)
        const attendanceRecords = ref([
            {
                id: 1,
                employee: {
                    id: 'EMP001',
                    name: 'أحمد محمد',
                    department: 'تقنية المعلومات',
                    avatar: '/images/avatars/1.jpg'
                },
                date: '2024-01-15',
                checkIn: '08:00',
                checkOut: '17:00',
                status: 'present',
                notes: ''
            },
            {
                id: 2,
                employee: {
                    id: 'EMP002',
                    name: 'فاطمة أحمد',
                    department: 'الموارد البشرية',
                    avatar: '/images/avatars/2.jpg'
                },
                date: '2024-01-15',
                checkIn: '08:15',
                checkOut: '16:45',
                status: 'late',
                notes: 'تأخر 15 دقيقة'
            },
            {
                id: 3,
                employee: {
                    id: 'EMP003',
                    name: 'خالد عبدالله',
                    department: 'المبيعات',
                    avatar: '/images/avatars/3.jpg'
                },
                date: '2024-01-15',
                checkIn: '',
                checkOut: '',
                status: 'absent',
                notes: 'إجازة مرضية'
            }
        ])

        // الحسابات
        const totalPages = computed(() => Math.ceil(totalRecords.value / pageSize))
        
        const visiblePages = computed(() => {
            const pages = []
            const startPage = Math.max(1, currentPage.value - 2)
            const endPage = Math.min(totalPages.value, startPage + 4)
            
            for (let i = startPage; i <= endPage; i++) {
                pages.push(i)
            }
            return pages
        })

        // الدوال
        const applyFilters = () => {
            currentPage.value = 1
            loadAttendanceData()
        }

        const resetFilters = () => {
            Object.assign(filters, {
                startDate: '',
                endDate: '',
                department: '',
                status: ''
            })
            applyFilters()
        }

        const loadAttendanceData = () => {
            // محاكاة جلب البيانات من API
            setTimeout(() => {
                totalRecords.value = attendanceRecords.value.length
                calculateStats()
            }, 500)
        }

        const calculateStats = () => {
            const present = attendanceRecords.value.filter(r => r.status === 'present').length
            const absent = attendanceRecords.value.filter(r => r.status === 'absent').length
            const late = attendanceRecords.value.filter(r => r.status === 'late').length
            const total = attendanceRecords.value.length

            stats.totalPresent = present
            stats.totalAbsent = absent
            stats.totalLate = late
            stats.attendanceRate = total > 0 ? Math.round((present / total) * 100) : 0
        }

        const changePage = (page) => {
            currentPage.value = page
            loadAttendanceData()
        }

        const editRecord = (record) => {
            Object.assign(editForm, {
                id: record.id,
                checkIn: record.checkIn,
                checkOut: record.checkOut,
                status: record.status,
                notes: record.notes
            })
            showEditModal.value = true
        }

        const saveEdit = () => {
            const index = attendanceRecords.value.findIndex(r => r.id === editForm.id)
            if (index !== -1) {
                Object.assign(attendanceRecords.value[index], editForm)
            }
            closeEditModal()
            calculateStats()
        }

        const closeEditModal = () => {
            showEditModal.value = false
            Object.assign(editForm, {
                id: null,
                checkIn: '',
                checkOut: '',
                status: '',
                notes: ''
            })
        }

        const deleteRecord = (id) => {
            if (confirm('هل أنت متأكد من حذف هذا السجل؟')) {
                attendanceRecords.value = attendanceRecords.value.filter(r => r.id !== id)
                calculateStats()
            }
        }

        const viewDetails = (record) => {
            alert(`تفاصيل الحضور لـ ${record.employee.name}\nالتاريخ: ${record.date}\nالحالة: ${getStatusText(record.status)}`)
        }

        const exportReport = () => {
            // محاكاة تصدير التقرير
            alert('جاري تصدير التقرير...')
        }

        const getStatusClass = (status) => {
            return `status-${status}`
        }

        const getStatusText = (status) => {
            const statusMap = {
                present: 'حاضر',
                absent: 'غائب',
                late: 'متأخر',
                vacation: 'إجازة'
            }
            return statusMap[status] || status
        }

        const formatDate = (dateString) => {
            return new Date(dateString).toLocaleDateString('ar-EG')
        }

        const calculateDuration = (checkIn, checkOut) => {
            if (!checkIn || !checkOut) return '--'
            
            const [inHours, inMinutes] = checkIn.split(':').map(Number)
            const [outHours, outMinutes] = checkOut.split(':').map(Number)
            
            const totalInMinutes = inHours * 60 + inMinutes
            const totalOutMinutes = outHours * 60 + outMinutes
            
            const durationMinutes = totalOutMinutes - totalInMinutes
            const hours = Math.floor(durationMinutes / 60)
            const minutes = durationMinutes % 60
            
            return `${hours} س ${minutes} د`
        }

        const truncateText = (text, length) => {
            return text.length > length ? text.substring(0, length) + '...' : text
        }

        // دورة الحياة
        onMounted(() => {
            loadAttendanceData()
        })

        return {
            showFilters,
            showEditModal,
            currentPage,
            pageSize,
            totalRecords,
            filters,
            editForm,
            stats,
            attendanceRecords,
            totalPages,
            visiblePages,
            applyFilters,
            resetFilters,
            changePage,
            editRecord,
            saveEdit,
            closeEditModal,
            deleteRecord,
            viewDetails,
            exportReport,
            getStatusClass,
            getStatusText,
            formatDate,
            calculateDuration,
            truncateText
        }
    }
}
</script>

<style scoped>
.attendance-list {
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.header h2 {
    color: #2c3e50;
    margin: 0;
}

.actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-outline {
    background: transparent;
    border: 1px solid #bdc3c7;
    color: #7f8c8d;
}

.filters-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.filter-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon.present { background: #e8f5e8; color: #27ae60; }
.stat-icon.absent { background: #fde8e8; color: #e74c3c; }
.stat-icon.late { background: #fff3e0; color: #f39c12; }
.stat-icon.percentage { background: #e3f2fd; color: #3498db; }

.stat-info h3 {
    margin: 0;
    font-size: 24px;
    color: #2c3e50;
}

.stat-info p {
    margin: 0;
    color: #7f8c8d;
    font-size: 14px;
}

.table-container {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.attendance-table {
    width: 100%;
    border-collapse: collapse;
}

.attendance-table th,
.attendance-table td {
    padding: 12px;
    text-align: right;
    border-bottom: 1px solid #ecf0f1;
}

.attendance-table th {
    background: #34495e;
    color: white;
    font-weight: 500;
}

.employee-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.employee-name {
    font-weight: 500;
    color: #2c3e50;
}

.employee-id {
    font-size: 12px;
    color: #7f8c8d;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.present { background: #d5f4e6; color: #27ae60; }
.status-badge.absent { background: #fadbd8; color: #e74c3c; }
.status-badge.late { background: #fdebd0; color: #f39c12; }

.notes {
    color: #7f8c8d;
    cursor: help;
}

.no-notes {
    color: #bdc3c7;
    font-style: italic;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn-icon {
    padding: 6px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background: transparent;
}

.btn-icon.edit { color: #3498db; }
.btn-icon.delete { color: #e74c3c; }
.btn-icon.view { color: #27ae60; }

.btn-icon:hover {
    background: #f8f9fa;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: white;
    border-top: 1px solid #ecf0f1;
}

.pagination-info {
    color: #7f8c8d;
    font-size: 14px;
}

.pagination {
    display: flex;
    gap: 5px;
}

.pagination-btn {
    padding: 8px 12px;
    border: 1px solid #ddd;
    background: white;
    color: #34495e;
    cursor: pointer;
    border-radius: 4px;
}

.pagination-btn:hover:not(:disabled) {
    background: #3498db;
    color: white;
}

.pagination-btn.active {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

.pagination-btn:disabled {
    background: #ecf0f1;
    color: #bdc3c7;
    cursor: not-allowed;
}

/* المودال */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow: auto;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #ecf0f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #7f8c8d;
}

.modal-body {
    padding: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #ecf0f1;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* حالة الصفوف */
.status-present { background: #f8fff8; }
.status-absent { background: #fff8f8; }
.status-late { background: #fffbf0; }

@media (max-width: 768px) {
    .header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .stats-cards {
        grid-template-columns: 1fr 1fr;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .pagination-container {
        flex-direction: column;
        gap: 15px;
    }
}
</style>
