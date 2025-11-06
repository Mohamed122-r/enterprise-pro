<template>
    <div class="invoice-form">
        <div class="form-header">
            <h2>{{ isEdit ? 'تعديل فاتورة' : 'إنشاء فاتورة جديدة' }}</h2>
            <button class="btn-back" @click="$router.back()">
                <i class="back-icon"></i> رجوع
            </button>
        </div>

        <form @submit.prevent="submitForm" class="invoice-form-content">
            <!-- معلومات الفاتورة الأساسية -->
            <div class="form-section">
                <h3>معلومات الفاتورة</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="invoiceNumber">رقم الفاتورة <span class="required">*</span></label>
                        <input type="text" id="invoiceNumber" v-model="form.invoice_number" required class="form-control">
                        <span class="error" v-if="errors.invoice_number">{{ errors.invoice_number }}</span>
                    </div>
                    <div class="form-group">
                        <label for="invoiceDate">تاريخ الفاتورة <span class="required">*</span></label>
                        <input type="date" id="invoiceDate" v-model="form.invoice_date" required class="form-control">
                        <span class="error" v-if="errors.invoice_date">{{ errors.invoice_date }}</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dueDate">تاريخ الاستحقاق <span class="required">*</span></label>
                        <input type="date" id="dueDate" v-model="form.due_date" required class="form-control">
                        <span class="error" v-if="errors.due_date">{{ errors.due_date }}</span>
                    </div>
                    <div class="form-group">
                        <label for="status">حالة الفاتورة</label>
                        <select id="status" v-model="form.status" class="form-control">
                            <option value="draft">مسودة</option>
                            <option value="sent">مرسلة</option>
                            <option value="paid">مدفوعة</option>
                            <option value="overdue">متأخرة</option>
                            <option value="cancelled">ملغاة</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- معلومات العميل -->
            <div class="form-section">
                <h3>معلومات العميل</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="client">العميل <span class="required">*</span></label>
                        <select id="client" v-model="form.client_id" required class="form-control">
                            <option value="">اختر العميل</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">
                                {{ client.name }} - {{ client.company }}
                            </option>
                        </select>
                        <span class="error" v-if="errors.client_id">{{ errors.client_id }}</span>
                    </div>
                    <div class="form-group">
                        <label for="clientEmail">بريد العميل</label>
                        <input type="email" id="clientEmail" v-model="form.client_email" class="form-control">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="clientAddress">عنوان العميل</label>
                    <textarea id="clientAddress" v-model="form.client_address" rows="3" class="form-control"></textarea>
                </div>
            </div>

            <!-- عناصر الفاتورة -->
            <div class="form-section">
                <h3>عناصر الفاتورة</h3>
                <div class="invoice-items">
                    <div class="items-header">
                        <span>الوصف</span>
                        <span>الكمية</span>
                        <span>السعر</span>
                        <span>المجموع</span>
                        <span>الإجراءات</span>
                    </div>
                    
                    <div v-for="(item, index) in form.items" :key="index" class="invoice-item">
                        <input type="text" v-model="item.description" placeholder="وصف العنصر" class="item-input">
                        <input type="number" v-model="item.quantity" min="1" @input="calculateItemTotal(item)" class="item-input">
                        <input type="number" v-model="item.unit_price" min="0" step="0.01" @input="calculateItemTotal(item)" class="item-input">
                        <span class="item-total">{{ formatCurrency(item.total) }}</span>
                        <button type="button" @click="removeItem(index)" class="btn-remove-item">
                            <i class="remove-icon"></i>
                        </button>
                    </div>

                    <button type="button" @click="addItem" class="btn-add-item">
                        <i class="add-icon"></i> إضافة عنصر
                    </button>
                </div>
            </div>

            <!-- الملخص والحسابات -->
            <div class="form-section">
                <h3>ملخص الفاتورة</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span>المجموع الفرعي:</span>
                        <span>{{ formatCurrency(totals.subtotal) }}</span>
                    </div>
                    <div class="summary-item">
                        <span>الضريبة ({{ form.tax_rate }}%):</span>
                        <span>{{ formatCurrency(totals.tax) }}</span>
                    </div>
                    <div class="summary-item">
                        <span>الخصم:</span>
                        <span>-{{ formatCurrency(totals.discount) }}</span>
                    </div>
                    <div class="summary-item total">
                        <span>المجموع الكلي:</span>
                        <span>{{ formatCurrency(totals.grandTotal) }}</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="taxRate">نسبة الضريبة (%)</label>
                        <input type="number" id="taxRate" v-model="form.tax_rate" min="0" max="100" step="0.01" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="discount">الخصم</label>
                        <input type="number" id="discount" v-model="form.discount" min="0" step="0.01" class="form-control">
                    </div>
                </div>
            </div>

            <!-- ملاحظات -->
            <div class="form-section">
                <h3>ملاحظات وشروط</h3>
                <div class="form-group full-width">
                    <label for="notes">ملاحظات</label>
                    <textarea id="notes" v-model="form.notes" rows="4" 
                              placeholder="أي ملاحظات إضافية حول الفاتورة..." 
                              class="form-control"></textarea>
                </div>
                <div class="form-group full-width">
                    <label for="terms">الشروط والأحكام</label>
                    <textarea id="terms" v-model="form.terms" rows="3" 
                              placeholder="شروط وأحكام الدفع..." 
                              class="form-control"></textarea>
                </div>
            </div>

            <!-- أزرار الإجراءات -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" @click="$router.back()">
                    إلغاء
                </button>
                <button type="button" class="btn btn-outline" @click="saveDraft" v-if="!isEdit">
                    حفظ كمسودة
                </button>
                <button type="submit" class="btn btn-primary" :disabled="loading">
                    <span v-if="loading">جاري الحفظ...</span>
                    <span v-else>{{ isEdit ? 'تحديث الفاتورة' : 'إنشاء الفاتورة' }}</span>
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
                    <p>تم {{ isEdit ? 'تحديث' : 'إنشاء' }} الفاتورة بنجاح.</p>
                    <div class="invoice-preview">
                        <strong>رقم الفاتورة:</strong> {{ form.invoice_number }}<br>
                        <strong>المجموع:</strong> {{ formatCurrency(totals.grandTotal) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" @click="redirectToList">العودة للقائمة</button>
                    <button class="btn btn-outline" @click="printInvoice" v-if="!isEdit">طباعة الفاتورة</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export default {
    name: 'InvoiceForm',
    setup() {
        const route = useRoute()
        const router = useRouter()
        
        const loading = ref(false)
        const showSuccessModal = ref(false)

        const isEdit = computed(() => route.params.id !== undefined)

        // بيانات العملاء (يمكن جلبها من API)
        const clients = ref([
            { id: 1, name: 'أحمد محمد', company: 'شركة التقنية المتطورة', email: 'ahmed@tech.com' },
            { id: 2, name: 'فاطمة أحمد', company: 'مؤسسة النهضة', email: 'fatima@nahda.com' },
            { id: 3, name: 'خالد عبدالله', company: 'شركة المستقبل', email: 'khaled@future.com' }
        ])

        // نموذج البيانات
        const form = reactive({
            invoice_number: '',
            invoice_date: new Date().toISOString().split('T')[0],
            due_date: '',
            status: 'draft',
            client_id: '',
            client_email: '',
            client_address: '',
            items: [
                { description: '', quantity: 1, unit_price: 0, total: 0 }
            ],
            tax_rate: 15,
            discount: 0,
            notes: '',
            terms: 'يجب سداد المبلغ خلال 30 يوم من تاريخ الفاتورة.'
        })

        // الأخطاء
        const errors = reactive({
            invoice_number: '',
            invoice_date: '',
            due_date: '',
            client_id: ''
        })

        // الحسابات
        const totals = computed(() => {
            const subtotal = form.items.reduce((sum, item) => sum + (item.total || 0), 0)
            const tax = (subtotal * form.tax_rate) / 100
            const discount = form.discount || 0
            const grandTotal = subtotal + tax - discount

            return {
                subtotal,
                tax,
                discount,
                grandTotal
            }
        })

        // الدوال
        const submitForm = async () => {
            if (!validateForm()) return

            loading.value = true
            
            try {
                // محاكاة API call
                await new Promise(resolve => setTimeout(resolve, 1500))
                
                console.log('Invoice form submitted:', form)
                showSuccessModal.value = true
            } catch (error) {
                console.error('Error saving invoice:', error)
                alert('حدث خطأ أثناء حفظ الفاتورة')
            } finally {
                loading.value = false
            }
        }

        const validateForm = () => {
            let isValid = true
            
            // إعادة تعيين الأخطاء
            Object.keys(errors).forEach(key => errors[key] = '')
            
            if (!form.invoice_number.trim()) {
                errors.invoice_number = 'رقم الفاتورة مطلوب'
                isValid = false
            }
            
            if (!form.invoice_date) {
                errors.invoice_date = 'تاريخ الفاتورة مطلوب'
                isValid = false
            }
            
            if (!form.due_date) {
                errors.due_date = 'تاريخ الاستحقاق مطلوب'
                isValid = false
            }
            
            if (!form.client_id) {
                errors.client_id = 'العميل مطلوب'
                isValid = false
            }
            
            // التحقق من عناصر الفاتورة
            const hasEmptyItems = form.items.some(item => !item.description.trim() || item.quantity <= 0)
            if (hasEmptyItems) {
                alert('يرجى ملء جميع عناصر الفاتورة بشكل صحيح')
                isValid = false
            }
            
            return isValid
        }

        const addItem = () => {
            form.items.push({
                description: '',
                quantity: 1,
                unit_price: 0,
                total: 0
            })
        }

        const removeItem = (index) => {
            if (form.items.length > 1) {
                form.items.splice(index, 1)
            }
        }

        const calculateItemTotal = (item) => {
            item.total = (item.quantity || 0) * (item.unit_price || 0)
        }

        const saveDraft = () => {
            form.status = 'draft'
            submitForm()
        }

        const formatCurrency = (amount) => {
            return new Intl.NumberFormat('ar-SA', {
                style: 'currency',
                currency: 'SAR'
            }).format(amount)
        }

        const redirectToList = () => {
            router.push('/accounting/invoices')
        }

        const printInvoice = () => {
            window.print()
        }

        // تحميل البيانات إذا كان تعديل
        onMounted(() => {
            // تعيين تاريخ الاستحقاق (30 يوم من الآن)
            const dueDate = new Date()
            dueDate.setDate(dueDate.getDate() + 30)
            form.due_date = dueDate.toISOString().split('T')[0]

            if (isEdit.value) {
                // محاكاة جلب بيانات الفاتورة
                setTimeout(() => {
                    Object.assign(form, {
                        invoice_number: 'INV-2024-001',
                        invoice_date: '2024-01-15',
                        due_date: '2024-02-14',
                        status: 'sent',
                        client_id: 1,
                        client_email: 'ahmed@tech.com',
                        client_address: 'حي الملقا، الرياض، المملكة العربية السعودية',
                        items: [
                            { description: 'تطوير نظام إدارة', quantity: 10, unit_price: 500, total: 5000 },
                            { description: 'استشارات تقنية', quantity: 5, unit_price: 300, total: 1500 }
                        ],
                        tax_rate: 15,
                        discount: 200,
                        notes: 'شكراً لتعاملكم معنا',
                        terms: 'يجب سداد المبلغ خلال 30 يوم من تاريخ الفاتورة.'
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
            clients,
            totals,
            submitForm,
            addItem,
            removeItem,
            calculateItemTotal,
            saveDraft,
            formatCurrency,
            redirectToList,
            printInvoice
        }
    }
}
</script>

<style scoped>
.invoice-form {
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

.invoice-form-content {
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

/* عناصر الفاتورة */
.invoice-items {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    overflow: hidden;
}

.items-header {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 10px;
    padding: 15px;
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 1px solid #e9ecef;
}

.invoice-item {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 10px;
    padding: 15px;
    border-bottom: 1px solid #f8f9fa;
    align-items: center;
}

.invoice-item:last-child {
    border-bottom: none;
}

.item-input {
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.item-input:focus {
    outline: none;
    border-color: #3498db;
}

.item-total {
    font-weight: 600;
    color: #2c3e50;
}

.btn-remove-item {
    background: #e74c3c;
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-remove-item:hover {
    background: #c0392b;
}

.btn-add-item {
    background: #27ae60;
    color: white;
    border: none;
    padding: 10px 15px;
    margin: 15px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.btn-add-item:hover {
    background: #219a52;
}

/* ملخص الفاتورة */
.summary-grid {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item.total {
    font-weight: 700;
    font-size: 16px;
    color: #2c3e50;
    border-top: 2px solid #3498db;
    padding-top: 12px;
    margin-top: 8px;
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
    min-width: 120px;
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
    max-width: 500px;
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
    margin: 0 0 15px 0;
    color: #2c3e50;
}

.invoice-preview {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    text-align: right;
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
.remove-icon::before { content: '×'; }
.add-icon::before { content: '+'; }

@media (max-width: 768px) {
    .invoice-form {
        padding: 10px;
    }
    
    .form-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .invoice-form-content {
        padding: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .items-header,
    .invoice-item {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    .items-header span:last-child,
    .invoice-item .btn-remove-item {
        grid-column: 1;
        justify-self: start;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
    
    .modal-footer {
        flex-direction: column;
    }
}
</style>
