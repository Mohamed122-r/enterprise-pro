<template>
    <div class="user-form">
        <div class="form-header">
            <h2>{{ isEdit ? 'تعديل مستخدم' : 'إضافة مستخدم جديد' }}</h2>
            <button class="btn-back" @click="$router.back()">
                <i class="back-icon"></i> رجوع
            </button>
        </div>

        <form @submit.prevent="submitForm" class="user-form-content">
            <!-- المعلومات الشخصية -->
            <div class="form-section">
                <h3>المعلومات الشخصية</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">الاسم الكامل <span class="required">*</span></label>
                        <input type="text" id="name" v-model="form.name" required class="form-control">
                        <span class="error" v-if="errors.name">{{ errors.name }}</span>
                    </div>
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني <span class="required">*</span></label>
                        <input type="email" id="email" v-model="form.email" required class="form-control">
                        <span class="error" v-if="errors.email">{{ errors.email }}</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">رقم الهاتف</label>
                        <input type="tel" id="phone" v-model="form.phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="department">القسم <span class="required">*</span></label>
                        <select id="department" v-model="form.department_id" required class="form-control">
                            <option value="">اختر القسم</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                        <span class="error" v-if="errors.department_id">{{ errors.department_id }}</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jobTitle">المسمى الوظيفي</label>
                        <input type="text" id="jobTitle" v-model="form.job_title" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="hireDate">تاريخ التعيين</label>
                        <input type="date" id="hireDate" v-model="form.hire_date" class="form-control">
                    </div>
                </div>
            </div>

            <!-- معلومات الحساب -->
            <div class="form-section">
                <h3>معلومات الحساب</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">اسم المستخدم <span class="required">*</span></label>
                        <input type="text" id="username" v-model="form.username" required class="form-control">
                        <span class="error" v-if="errors.username">{{ errors.username }}</span>
                    </div>
                    <div class="form-group">
                        <label for="role">الدور <span class="required">*</span></label>
                        <select id="role" v-model="form.role_id" required class="form-control">
                            <option value="">اختر الدور</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                {{ role.name }}
                            </option>
                        </select>
                        <span class="error" v-if="errors.role_id">{{ errors.role_id }}</span>
                    </div>
                </div>

                <div class="form-row" v-if="!isEdit">
                    <div class="form-group">
                        <label for="password">كلمة المرور <span class="required">*</span></label>
                        <input type="password" id="password" v-model="form.password" required class="form-control">
                        <span class="error" v-if="errors.password">{{ errors.password }}</span>
                    </div>
                    <div class="form-group">
                        <label for="passwordConfirmation">تأكيد كلمة المرور <span class="required">*</span></label>
                        <input type="password" id="passwordConfirmation" v-model="form.password_confirmation" required class="form-control">
                        <span class="error" v-if="errors.password_confirmation">{{ errors.password_confirmation }}</span>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="form.is_active">
                        <span class="checkmark"></span>
                        الحساب مفعل
                    </label>
                </div>
            </div>

            <!-- الصلاحيات -->
            <div class="form-section">
                <h3>الصلاحيات</h3>
                <div class="permissions-grid">
                    <div v-for="permission in permissions" :key="permission.id" class="permission-item">
                        <label class="checkbox-label">
                            <input type="checkbox" :value="permission.id" v-model="form.permissions">
                            <span class="checkmark"></span>
                            {{ permission.name }}
                        </label>
                        <span class="permission-description">{{ permission.description }}</span>
                    </div>
                </div>
            </div>

            <!-- معلومات إضافية -->
            <div class="form-section">
                <h3>معلومات إضافية</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="salary">الراتب</label>
                        <input type="number" id="salary" v-model="form.salary" class="form-control" min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="status">الحالة الوظيفية</label>
                        <select id="status" v-model="form.status" class="form-control">
                            <option value="active">نشط</option>
                            <option value="inactive">غير نشط</option>
                            <option value="suspended">موقوف</option>
                            <option value="on_leave">في إجازة</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="notes">ملاحظات</label>
                    <textarea id="notes" v-model="form.notes" rows="4" 
                              placeholder="أي ملاحظات إضافية عن المستخدم..." 
                              class="form-control"></textarea>
                </div>
            </div>

            <!-- أزرار الإجراءات -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" @click="$router.back()">
                    إلغاء
                </button>
                <button type="button" class="btn btn-outline" @click="resetForm" v-if="!isEdit">
                    إعادة تعيين
                </button>
                <button type="submit" class="btn btn-primary" :disabled="loading">
                    <span v-if="loading">جاري الحفظ...</span>
                    <span v-else>{{ isEdit ? 'تحديث' : 'حفظ' }}</span>
                </button>
            </div>
        </form>

        <!-- تأكيد الحفظ -->
        <div v-if="showSuccessModal" class="modal-overlay">
            <div class="modal">
                <div class="modal-header">
                    <h3>تم بنجاح!</h3>
                </div>
                <div class="modal-body">
                    <p>تم {{ isEdit ? 'تحديث' : 'إضافة' }} المستخدم بنجاح.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" @click="redirectToList">العودة للقائمة</button>
                    <button class="btn btn-outline" @click="addAnother" v-if="!isEdit">إضافة آخر</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export default {
    name: 'UserForm',
    setup() {
        const route = useRoute()
        const router = useRouter()
        
        const loading = ref(false)
        const showSuccessModal = ref(false)

        const isEdit = computed(() => route.params.id !== undefined)

        // البيانات الثابتة (يمكن جلبها من API)
        const departments = ref([
            { id: 1, name: 'تقنية المعلومات' },
            { id: 2, name: 'الموارد البشرية' },
            { id: 3, name: 'المبيعات' },
            { id: 4, name: 'التسويق' },
            { id: 5, name: 'المالية' }
        ])

        const roles = ref([
            { id: 1, name: 'مدير النظام' },
            { id: 2, name: 'مدير قسم' },
            { id: 3, name: 'مشرف' },
            { id: 4, name: 'موظف' }
        ])

        const permissions = ref([
            { id: 1, name: 'عرض المستخدمين', description: 'القدرة على عرض قائمة المستخدمين' },
            { id: 2, name: 'إضافة مستخدمين', description: 'القدرة على إضافة مستخدمين جدد' },
            { id: 3, name: 'تعديل المستخدمين', description: 'القدرة على تعديل بيانات المستخدمين' },
            { id: 4, name: 'حذف المستخدمين', description: 'القدرة على حذف المستخدمين' },
            { id: 5, name: 'إدارة الصلاحيات', description: 'القدرة على إدارة صلاحيات المستخدمين' },
            { id: 6, name: 'عرض التقارير', description: 'القدرة على عرض التقارير' }
        ])

        // نموذج البيانات
        const form = reactive({
            name: '',
            email: '',
            phone: '',
            department_id: '',
            job_title: '',
            hire_date: '',
            username: '',
            role_id: '',
            password: '',
            password_confirmation: '',
            is_active: true,
            permissions: [],
            salary: '',
            status: 'active',
            notes: ''
        })

        // الأخطاء
        const errors = reactive({
            name: '',
            email: '',
            username: '',
            role_id: '',
            department_id: '',
            password: '',
            password_confirmation: ''
        })

        // الدوال
        const submitForm = async () => {
            if (!validateForm()) return

            loading.value = true
            
            try {
                // محاكاة API call
                await new Promise(resolve => setTimeout(resolve, 1500))
                
                console.log('User form submitted:', form)
                showSuccessModal.value = true
            } catch (error) {
                console.error('Error saving user:', error)
                alert('حدث خطأ أثناء حفظ البيانات')
            } finally {
                loading.value = false
            }
        }

        const validateForm = () => {
            let isValid = true
            
            // إعادة تعيين الأخطاء
            Object.keys(errors).forEach(key => errors[key] = '')
            
            if (!form.name.trim()) {
                errors.name = 'الاسم مطلوب'
                isValid = false
            }
            
            if (!form.email.trim()) {
                errors.email = 'البريد الإلكتروني مطلوب'
                isValid = false
            } else if (!isValidEmail(form.email)) {
                errors.email = 'البريد الإلكتروني غير صحيح'
                isValid = false
            }
            
            if (!form.username.trim()) {
                errors.username = 'اسم المستخدم مطلوب'
                isValid = false
            }
            
            if (!form.role_id) {
                errors.role_id = 'الدور مطلوب'
                isValid = false
            }
            
            if (!form.department_id) {
                errors.department_id = 'القسم مطلوب'
                isValid = false
            }
            
            if (!isEdit.value) {
                if (!form.password) {
                    errors.password = 'كلمة المرور مطلوبة'
                    isValid = false
                } else if (form.password.length < 6) {
                    errors.password = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'
                    isValid = false
                }
                
                if (form.password !== form.password_confirmation) {
                    errors.password_confirmation = 'كلمة المرور غير متطابقة'
                    isValid = false
                }
            }
            
            return isValid
        }

        const isValidEmail = (email) => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            return emailRegex.test(email)
        }

        const resetForm = () => {
            Object.assign(form, {
                name: '',
                email: '',
                phone: '',
                department_id: '',
                job_title: '',
                hire_date: '',
                username: '',
                role_id: '',
                password: '',
                password_confirmation: '',
                is_active: true,
                permissions: [],
                salary: '',
                status: 'active',
                notes: ''
            })
        }

        const redirectToList = () => {
            router.push('/users')
        }

        const addAnother = () => {
            showSuccessModal.value = false
            resetForm()
        }

        // تحميل البيانات إذا كان تعديل
        onMounted(() => {
            if (isEdit.value) {
                // محاكاة جلب بيانات المستخدم
                setTimeout(() => {
                    Object.assign(form, {
                        name: 'أحمد محمد',
                        email: 'ahmed@company.com',
                        phone: '+966501112233',
                        department_id: 1,
                        job_title: 'مدير تقنية المعلومات',
                        hire_date: '2023-01-15',
                        username: 'ahmedm',
                        role_id: 2,
                        is_active: true,
                        permissions: [1, 2, 3, 6],
                        salary: '15000',
                        status: 'active',
                        notes: 'موظف متميز وأداؤه جيد'
                    })
                }, 500)
            }
        })

        return {
            form,
            errors,
            loading,
            showSuccessModal,
            isEdit,
            departments,
            roles,
            permissions,
            submitForm,
            resetForm,
            redirectToList,
            addAnother
        }
    }
}
</script>

<style scoped>
.user-form {
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-header h2 {
    margin: 0;
    color: #2c3e50;
}

.btn-back {
    background: #95a5a6;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-form-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ecf0f1;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h3 {
    margin: 0 0 20px 0;
    color: #34495e;
    font-size: 18px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
}

.required {
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.error {
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

/* الصلاحيات */
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.permission-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.permission-description {
    display: block;
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
}

/* Checkbox مخصص */
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: normal;
    margin-bottom: 5px;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 4px;
    position: relative;
    transition: all 0.2s;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
    background: #3498db;
    border-color: #3498db;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    color: white;
    font-size: 14px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* أزرار الإجراءات */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    padding-top: 20px;
    border-top: 1px solid #ecf0f1;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    min-width: 100px;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-outline {
    background: transparent;
    border: 1px solid #bdc3c7;
    color: #7f8c8d;
}

.btn-outline:hover {
    background: #f8f9fa;
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
    max-width: 400px;
    overflow: hidden;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #ecf0f1;
    text-align: center;
}

.modal-header h3 {
    margin: 0;
    color: #27ae60;
}

.modal-body {
    padding: 20px;
    text-align: center;
}

.modal-body p {
    margin: 0;
    color: #2c3e50;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #ecf0f1;
    display: flex;
    justify-content: center;
    gap: 10px;
}

/* الأيقونات */
.back-icon::before { content: '←'; }

@media (max-width: 768px) {
    .user-form {
        padding: 10px;
    }
    
    .form-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .user-form-content {
        padding: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .permissions-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
