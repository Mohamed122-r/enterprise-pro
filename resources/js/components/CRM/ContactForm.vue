<template>
    <div class="contact-form">
        <div class="form-header">
            <h2>{{ isEdit ? 'تعديل جهة اتصال' : 'إضافة جهة اتصال جديدة' }}</h2>
            <button class="btn-back" @click="$router.back()">
                <i class="back-icon"></i> رجوع
            </button>
        </div>

        <form @submit.prevent="submitForm" class="contact-form-content">
            <!-- المعلومات الأساسية -->
            <div class="form-section">
                <h3>المعلومات الأساسية</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">الاسم الكامل <span class="required">*</span></label>
                        <input type="text" id="name" v-model="form.name" required class="form-control">
                        <span class="error" v-if="errors.name">{{ errors.name }}</span>
                    </div>
                    <div class="form-group">
                        <label for="company">الشركة</label>
                        <input type="text" id="company" v-model="form.company" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jobTitle">المسمى الوظيفي</label>
                        <input type="text" id="jobTitle" v-model="form.jobTitle" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="department">القسم</label>
                        <select id="department" v-model="form.department" class="form-control">
                            <option value="">اختر القسم</option>
                            <option value="sales">المبيعات</option>
                            <option value="marketing">التسويق</option>
                            <option value="it">تقنية المعلومات</option>
                            <option value="hr">الموارد البشرية</option>
                            <option value="finance">المالية</option>
                            <option value="operations">العمليات</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- معلومات الاتصال -->
            <div class="form-section">
                <h3>معلومات الاتصال</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني <span class="required">*</span></label>
                        <input type="email" id="email" v-model="form.email" required class="form-control">
                        <span class="error" v-if="errors.email">{{ errors.email }}</span>
                    </div>
                    <div class="form-group">
                        <label for="phone">رقم الهاتف</label>
                        <input type="tel" id="phone" v-model="form.phone" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="mobile">الجوال</label>
                        <input type="tel" id="mobile" v-model="form.mobile" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="website">الموقع الإلكتروني</label>
                        <input type="url" id="website" v-model="form.website" class="form-control">
                    </div>
                </div>
            </div>

            <!-- العنوان -->
            <div class="form-section">
                <h3>العنوان</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="country">الدولة</label>
                        <select id="country" v-model="form.country" class="form-control">
                            <option value="">اختر الدولة</option>
                            <option value="sa">المملكة العربية السعودية</option>
                            <option value="ae">الإمارات العربية المتحدة</option>
                            <option value="qa">قطر</option>
                            <option value="kw">الكويت</option>
                            <option value="bh">البحرين</option>
                            <option value="om">عمان</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="city">المدينة</label>
                        <input type="text" id="city" v-model="form.city" class="form-control">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="address">العنوان التفصيلي</label>
                    <textarea id="address" v-model="form.address" rows="3" class="form-control"></textarea>
                </div>
            </div>

            <!-- معلومات إضافية -->
            <div class="form-section">
                <h3>معلومات إضافية</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="source">مصدر الجهة الاتصال</label>
                        <select id="source" v-model="form.source" class="form-control">
                            <option value="">اختر المصدر</option>
                            <option value="website">الموقع الإلكتروني</option>
                            <option value="referral">إحالة</option>
                            <option value="social">وسائل التواصل</option>
                            <option value="event">فعالية</option>
                            <option value="cold-call">اتصال مباشر</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">الحالة</label>
                        <select id="status" v-model="form.status" class="form-control">
                            <option value="active">نشط</option>
                            <option value="inactive">غير نشط</option>
                            <option value="lead">عميل محتمل</option>
                            <option value="customer">عميل</option>
                            <option value="partner">شريك</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="tags">الكلمات المفتاحية</label>
                    <input type="text" id="tags" v-model="tagInput" @keydown.enter.prevent="addTag" 
                           placeholder="اكتب كلمة مفتاحية واضغط Enter" class="form-control">
                    <div class="tags-container">
                        <span v-for="tag in form.tags" :key="tag" class="tag">
                            {{ tag }}
                            <button type="button" @click="removeTag(tag)" class="tag-remove">&times;</button>
                        </span>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="notes">ملاحظات</label>
                    <textarea id="notes" v-model="form.notes" rows="4" 
                              placeholder="أي ملاحظات إضافية عن جهة الاتصال..." 
                              class="form-control"></textarea>
                </div>
            </div>

            <!-- المرفقات -->
            <div class="form-section">
                <h3>المرفقات</h3>
                <div class="attachments-section">
                    <div class="upload-area" @click="triggerFileInput" @drop="handleDrop" @dragover.prevent>
                        <i class="upload-icon"></i>
                        <p>اسحب وأفلت الملفات هنا أو <span>اختر الملفات</span></p>
                        <input type="file" ref="fileInput" @change="handleFileSelect" multiple style="display: none;">
                    </div>
                    <div class="attachments-list" v-if="form.attachments.length > 0">
                        <div v-for="file in form.attachments" :key="file.name" class="attachment-item">
                            <i class="file-icon"></i>
                            <span class="file-name">{{ file.name }}</span>
                            <span class="file-size">{{ formatFileSize(file.size) }}</span>
                            <button type="button" @click="removeAttachment(file)" class="remove-attachment">
                                &times;
                            </button>
                        </div>
                    </div>
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
                    <p>تم {{ isEdit ? 'تحديث' : 'إضافة' }} جهة الاتصال بنجاح.</p>
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
    name: 'ContactForm',
    setup() {
        const route = useRoute()
        const router = useRouter()
        const fileInput = ref(null)
        const tagInput = ref('')
        
        const loading = ref(false)
        const showSuccessModal = ref(false)

        const isEdit = computed(() => route.params.id !== undefined)

        // نموذج البيانات
        const form = reactive({
            name: '',
            company: '',
            jobTitle: '',
            department: '',
            email: '',
            phone: '',
            mobile: '',
            website: '',
            country: '',
            city: '',
            address: '',
            source: '',
            status: 'active',
            tags: [],
            notes: '',
            attachments: []
        })

        // الأخطاء
        const errors = reactive({
            name: '',
            email: ''
        })

        // الدوال
        const submitForm = async () => {
            if (!validateForm()) return

            loading.value = true
            
            try {
                // محاكاة API call
                await new Promise(resolve => setTimeout(resolve, 1500))
                
                console.log('Form submitted:', form)
                showSuccessModal.value = true
            } catch (error) {
                console.error('Error saving contact:', error)
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
            
            return isValid
        }

        const isValidEmail = (email) => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            return emailRegex.test(email)
        }

        const resetForm = () => {
            Object.assign(form, {
                name: '',
                company: '',
                jobTitle: '',
                department: '',
                email: '',
                phone: '',
                mobile: '',
                website: '',
                country: '',
                city: '',
                address: '',
                source: '',
                status: 'active',
                tags: [],
                notes: '',
                attachments: []
            })
        }

        const addTag = () => {
            const tag = tagInput.value.trim()
            if (tag && !form.tags.includes(tag)) {
                form.tags.push(tag)
                tagInput.value = ''
            }
        }

        const removeTag = (tagToRemove) => {
            form.tags = form.tags.filter(tag => tag !== tagToRemove)
        }

        const triggerFileInput = () => {
            fileInput.value?.click()
        }

        const handleFileSelect = (event) => {
            const files = Array.from(event.target.files)
            files.forEach(file => {
                if (!form.attachments.some(f => f.name === file.name)) {
                    form.attachments.push(file)
                }
            })
            event.target.value = ''
        }

        const handleDrop = (event) => {
            event.preventDefault()
            const files = Array.from(event.dataTransfer.files)
            files.forEach(file => {
                if (!form.attachments.some(f => f.name === file.name)) {
                    form.attachments.push(file)
                }
            })
        }

        const removeAttachment = (fileToRemove) => {
            form.attachments = form.attachments.filter(file => file !== fileToRemove)
        }

        const formatFileSize = (bytes) => {
            if (bytes === 0) return '0 Bytes'
            const k = 1024
            const sizes = ['Bytes', 'KB', 'MB', 'GB']
            const i = Math.floor(Math.log(bytes) / Math.log(k))
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
        }

        const redirectToList = () => {
            router.push('/crm/contacts')
        }

        const addAnother = () => {
            showSuccessModal.value = false
            resetForm()
        }

        // تحميل البيانات إذا كان تعديل
        onMounted(() => {
            if (isEdit.value) {
                // محاكاة جلب بيانات جهة الاتصال
                setTimeout(() => {
                    Object.assign(form, {
                        name: 'أحمد محمد',
                        company: 'شركة التقنية المتطورة',
                        jobTitle: 'مدير تقنية المعلومات',
                        department: 'it',
                        email: 'ahmed@techcompany.com',
                        phone: '+966112233445',
                        mobile: '+966501112233',
                        website: 'https://techcompany.com',
                        country: 'sa',
                        city: 'الرياض',
                        address: 'حي الملقا، شارع الملك فهد',
                        source: 'website',
                        status: 'customer',
                        tags: ['عميل مميز', 'تقنية المعلومات'],
                        notes: 'عميل نشط ومهم للشركة'
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
            tagInput,
            fileInput,
            submitForm,
            resetForm,
            addTag,
            removeTag,
            triggerFileInput,
            handleFileSelect,
            handleDrop,
            removeAttachment,
            formatFileSize,
            redirectToList,
            addAnother
        }
    }
}
</script>

<style scoped>
.contact-form {
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

.contact-form-content {
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

.form-control:invalid {
    border-color: #e74c3c;
}

.error {
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

/* الكلمات المفتاحية */
.tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.tag {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.tag-remove {
    background: none;
    border: none;
    color: #1976d2;
    cursor: pointer;
    font-size: 14px;
    padding: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tag-remove:hover {
    background: #1976d2;
    color: white;
}

/* المرفقات */
.upload-area {
    border: 2px dashed #bdc3c7;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s;
    margin-bottom: 20px;
}

.upload-area:hover {
    border-color: #3498db;
}

.upload-icon {
    font-size: 48px;
    color: #bdc3c7;
    margin-bottom: 10px;
}

.upload-area p {
    margin: 0;
    color: #7f8c8d;
}

.upload-area span {
    color: #3498db;
    text-decoration: underline;
}

.attachments-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.attachment-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #e9ecef;
}

.file-icon {
    color: #3498db;
}

.file-name {
    flex: 1;
    font-weight: 500;
}

.file-size {
    color: #7f8c8d;
    font-size: 12px;
}

.remove-attachment {
    background: none;
    border: none;
    color: #e74c3c;
    cursor: pointer;
    font-size: 16px;
    padding: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-attachment:hover {
    background: #e74c3c;
    color: white;
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
.upload-icon::before { content: '📁'; }
.file-icon::before { content: '📄'; }

@media (max-width: 768px) {
    .contact-form {
        padding: 10px;
    }
    
    .form-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .contact-form-content {
        padding: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
