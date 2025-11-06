<template>
    <div class="invoice-details">
        <div class="details-header">
            <div class="header-actions">
                <button class="btn-back" @click="$router.back()">
                    <i class="back-icon"></i> رجوع للقائمة
                </button>
                <div class="action-buttons">
                    <button class="btn btn-outline" @click="printInvoice">
                        <i class="print-icon"></i> طباعة
                    </button>
                    <button class="btn btn-outline" @click="downloadPDF">
                        <i class="download-icon"></i> تحميل PDF
                    </button>
                    <button class="btn btn-primary" @click="editInvoice" v-if="invoice.status !== 'paid'">
                        <i class="edit-icon"></i> تعديل
                    </button>
                </div>
            </div>
        </div>

        <div class="invoice-container" v-if="invoice">
            <!-- ترويسة الفاتورة -->
            <div class="invoice-header">
                <div class="company-info">
                    <h1>{{ company.name }}</h1>
                    <p>{{ company.address }}</p>
                    <p>هاتف: {{ company.phone }} | بريد: {{ company.email }}</p>
                </div>
                <div class="invoice-info">
                    <h2>فاتورة</h2>
                    <div class="invoice-meta">
                        <div class="meta-item">
                            <span class="label">رقم الفاتورة:</span>
                            <span class="value">{{ invoice.invoice_number }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="label">تاريخ الفاتورة:</span>
                            <span class="value">{{ formatDate(invoice.invoice_date) }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="label">تاريخ الاستحقاق:</span>
                            <span class="value">{{ formatDate(invoice.due_date) }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="label">الحالة:</span>
                            <span class="value status-badge" :class="invoice.status">
                                {{ getStatusText(invoice.status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- معلومات العميل -->
            <div class="client-section">
                <div class="section-title">فاتورة إلى:</div>
                <div class="client-info">
                    <h3>{{ invoice.client.name }}</h3>
                    <p>{{ invoice.client.company }}</p>
                    <p>{{ invoice.client.email }}</p>
                    <p>{{ invoice.client.address }}</p>
                </div>
            </div>

            <!-- جدول العناصر -->
            <div class="items-section">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="text-right">#</th>
                            <th class="text-right">الوصف</th>
                            <th class="text-right">الكمية</th>
                            <th class="text-right">سعر الوحدة</th>
                            <th class="text-right">المجموع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in invoice.items" :key="index">
                            <td>{{ index + 1 }}</td>
                            <td class="description">{{ item.description }}</td>
                            <td>{{ item.quantity }}</td>
                            <td>{{ formatCurrency(item.unit_price) }}</td>
                            <td>{{ formatCurrency(item.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- الملخص -->
            <div class="summary-section">
                <div class="summary-table">
                    <div class="summary-row">
                        <span>المجموع الفرعي:</span>
                        <span>{{ formatCurrency(invoice.totals.subtotal) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>الضريبة ({{ invoice.tax_rate }}%):</span>
                        <span>{{ formatCurrency(invoice.totals.tax) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>الخصم:</span>
                        <span>-{{ formatCurrency(invoice.discount) }}</span>
                    </div>
                    <div class="summary-row total">
                        <span>المجموع الكلي:</span>
                        <span>{{ formatCurrency(invoice.totals.grand_total) }}</span>
                    </div>
                    <div class="summary-row paid" v-if="invoice.paid_amount > 0">
                        <span>المبلغ المدفوع:</span>
                        <span>{{ formatCurrency(invoice.paid_amount) }}</span>
                    </div>
                    <div class="summary-row balance" v-if="invoice.totals.grand_total - invoice.paid_amount > 0">
                        <span>المبلغ المتبقي:</span>
                        <span>{{ formatCurrency(invoice.totals.grand_total - invoice.paid_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- المدفوعات -->
            <div class="payments-section" v-if="invoice.payments && invoice.payments.length > 0">
                <div class="section-title">سجل المدفوعات</div>
                <div class="payments-list">
                    <div v-for="payment in invoice.payments" :key="payment.id" class="payment-item">
                        <div class="payment-info">
                            <span class="payment-amount">{{ formatCurrency(payment.amount) }}</span>
                            <span class="payment-date">{{ formatDate(payment.payment_date) }}</span>
                        </div>
                        <div class="payment-meta">
                            <span class="payment-method">{{ getPaymentMethodText(payment.method) }}</span>
                            <span class="payment-status" :class="payment.status">
                                {{ getPaymentStatusText(payment.status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ملاحظات وشروط -->
            <div class="notes-section" v-if="invoice.notes || invoice.terms">
                <div class="notes-row" v-if="invoice.notes">
                    <div class="section-title">ملاحظات</div>
                    <p>{{ invoice.notes }}</p>
                </div>
                <div class="notes-row" v-if="invoice.terms">
                    <div class="section-title">الشروط والأحكام</div>
                    <p>{{ invoice.terms }}</p>
                </div>
            </div>

            <!-- إجراءات الدفع -->
            <div class="payment-actions" v-if="invoice.status !== 'paid' && invoice.status !== 'cancelled'">
                <div class="section-title">إجراءات الدفع</div>
                <div class="payment-options">
                    <button class="btn btn-success" @click="recordPayment">
                        <i class="payment-icon"></i> تسديد دفعة
                    </button>
                    <button class="btn btn-warning" @click="markAsPaid" v-if="invoice.totals.grand_total - invoice.paid_amount === 0">
                        <i class="check-icon"></i> تعيين كمدفوع
                    </button>
                    <button class="btn btn-danger" @click="cancelInvoice">
                        <i class="cancel-icon"></i> إلغاء الفاتورة
                    </button>
                </div>
            </div>
        </div>

        <!-- تحميل -->
        <div v-else class="loading-state">
            <div class="loading-spinner"></div>
            <p>جاري تحميل بيانات الفاتورة...</p>
        </div>

        <!-- مودال تسديد الدفعة -->
        <div v-if="showPaymentModal" class="modal-overlay">
            <div class="modal">
                <div class="modal-header">
                    <h3>تسديد دفعة</h3>
                    <button class="close-btn" @click="closePaymentModal">&times;</button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitPayment">
                        <div class="form-group">
                            <label>مبلغ الدفعة <span class="required">*</span></label>
                            <input type="number" v-model="paymentForm.amount" :max="invoice.totals.grand_total - invoice.paid_amount" 
                                   step="0.01" required class="form-control">
                            <small>الحد الأقصى: {{ formatCurrency(invoice.totals.grand_total - invoice.paid_amount) }}</small>
                        </div>
                        <div class="form-group">
                            <label>طريقة الدفع <span class="required">*</span></label>
                            <select v-model="paymentForm.method" required class="form-control">
                                <option value="cash">نقدي</option>
                                <option value="bank_transfer">تحويل بنكي</option>
                                <option value="credit_card">بطاقة ائتمان</option>
                                <option value="check">شيك</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>تاريخ الدفع <span class="required">*</span></label>
                            <input type="date" v-model="paymentForm.payment_date" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label>ملاحظات</label>
                            <textarea v-model="paymentForm.notes" rows="3" class="form-control"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closePaymentModal">إلغاء</button>
                    <button class="btn btn-primary" @click="submitPayment" :disabled="processingPayment">
                        {{ processingPayment ? 'جاري المعالجة...' : 'تسديد الدفعة' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export default {
    name: 'InvoiceDetails',
    setup() {
        const route = useRoute()
        const router = useRouter()
        
        const invoice = ref(null)
        const showPaymentModal = ref(false)
        const processingPayment = ref(false)

        const company = ref({
            name: 'شركة التقنية المتطورة',
            address: 'حي الملقا، الرياض، المملكة العربية السعودية',
            phone: '+966112233445',
            email: 'info@techcompany.com'
        })

        const paymentForm = reactive({
            amount: 0,
            method: 'bank_transfer',
            payment_date: new Date().toISOString().split('T')[0],
            notes: ''
        })

        // تحميل بيانات الفاتورة
        const loadInvoice = async () => {
            try {
                // محاكاة جلب البيانات من API
                await new Promise(resolve => setTimeout(resolve, 1000))
                
                invoice.value = {
                    id: route.params.id,
                    invoice_number: 'INV-2024-001',
                    invoice_date: '2024-01-15',
                    due_date: '2024-02-14',
                    status: 'sent',
                    tax_rate: 15,
                    discount: 200,
                    paid_amount: 0,
                    notes: 'شكراً لتعاملكم معنا. نأمل أن تجدوا خدماتنا مرضية.',
                    terms: 'يجب سداد المبلغ خلال 30 يوم من تاريخ الفاتورة. يتضمن التأخير في السداد رسوم تأخير بنسبة 2% شهرياً.',
                    client: {
                        name: 'أحمد محمد',
                        company: 'شركة التقنية المتطورة',
                        email: 'ahmed@techcompany.com',
                        address: 'حي الملقا، شارع الملك فهد، الرياض'
                    },
                    items: [
                        {
                            description: 'تطوير نظام إدارة المؤسسات',
                            quantity: 1,
                            unit_price: 5000,
                            total: 5000
                        },
                        {
                            description: 'استشارات تقنية متخصصة',
                            quantity: 10,
                            unit_price: 300,
                            total: 3000
                        },
                        {
                            description: 'صيانة ودعم فني',
                            quantity: 3,
                            unit_price: 500,
                            total: 1500
                        }
                    ],
                    totals: {
                        subtotal: 9500,
                        tax: 1425,
                        grand_total: 10725
                    },
                    payments: [
                        {
                            id: 1,
                            amount: 5000,
                            method: 'bank_transfer',
                            payment_date: '2024-01-20',
                            status: 'completed'
                        }
                    ]
                }

                // حساب المبلغ المدفوع
                invoice.value.paid_amount = invoice.value.payments.reduce((sum, payment) => sum + payment.amount, 0)
            } catch (error) {
                console.error('Error loading invoice:', error)
                alert('حدث خطأ في تحميل بيانات الفاتورة')
            }
        }

        // الدوال
        const formatDate = (dateString) => {
            return new Date(dateString).toLocaleDateString('ar-SA')
        }

        const formatCurrency = (amount) => {
            return new Intl.NumberFormat('ar-SA', {
                style: 'currency',
                currency: 'SAR'
            }).format(amount)
        }

        const getStatusText = (status) => {
            const statusMap = {
                draft: 'مسودة',
                sent: 'مرسلة',
                paid: 'مدفوعة',
                overdue: 'متأخرة',
                cancelled: 'ملغاة'
            }
            return statusMap[status] || status
        }

        const getPaymentMethodText = (method) => {
            const methodMap = {
                cash: 'نقدي',
                bank_transfer: 'تحويل بنكي',
                credit_card: 'بطاقة ائتمان',
                check: 'شيك'
            }
            return methodMap[method] || method
        }

        const getPaymentStatusText = (status) => {
            const statusMap = {
                pending: 'قيد المعالجة',
                completed: 'مكتمل',
                failed: 'فاشل',
                refunded: 'تم الاسترداد'
            }
            return statusMap[status] || status
        }

        const printInvoice = () => {
            window.print()
        }

        const downloadPDF = () => {
            alert('جاري تحميل الفاتورة كملف PDF...')
            // محاكاة تحميل PDF
        }

        const editInvoice = () => {
            router.push(`/accounting/invoices/edit/${invoice.value.id}`)
        }

        const recordPayment = () => {
            paymentForm.amount = invoice.value.totals.grand_total - invoice.value.paid_amount
            showPaymentModal.value = true
        }

        const closePaymentModal = () => {
            showPaymentModal.value = false
            Object.assign(paymentForm, {
                amount: 0,
                method: 'bank_transfer',
                payment_date: new Date().toISOString().split('T')[0],
                notes: ''
            })
        }

        const submitPayment = async () => {
            processingPayment.value = true
            
            try {
                // محاكاة API call
                await new Promise(resolve => setTimeout(resolve, 1500))
                
                // إضافة الدفعة للفاتورة
                invoice.value.payments.push({
                    id: Date.now(),
                    amount: parseFloat(paymentForm.amount),
                    method: paymentForm.method,
                    payment_date: paymentForm.payment_date,
                    status: 'completed'
                })
                
                // تحديث المبلغ المدفوع
                invoice.value.paid_amount += parseFloat(paymentForm.amount)
                
                // تحديث حالة الفاتورة إذا تم سداد المبلغ بالكامل
                if (invoice.value.paid_amount >= invoice.value.totals.grand_total) {
                    invoice.value.status = 'paid'
                }
                
                closePaymentModal()
                alert('تم تسجيل الدفعة بنجاح!')
            } catch (error) {
                console.error('Error recording payment:', error)
                alert('حدث خطأ أثناء تسجيل الدفعة')
            } finally {
                processingPayment.value = false
            }
        }

        const markAsPaid = async () => {
            if (confirm('هل تريد تعيين هذه الفاتورة كمدفوعة بالكامل؟')) {
                try {
                    // محاكاة API call
                    await new Promise(resolve => setTimeout(resolve, 1000))
                    
                    invoice.value.status = 'paid'
                    invoice.value.paid_amount = invoice.value.totals.grand_total
                    
                    alert('تم تعيين الفاتورة كمدفوعة بنجاح!')
                } catch (error) {
                    console.error('Error marking as paid:', error)
                    alert('حدث خطأ أثناء تعيين الفاتورة كمدفوعة')
                }
            }
        }

        const cancelInvoice = async () => {
            if (confirm('هل أنت متأكد من إلغاء هذه الفاتورة؟ لا يمكن التراجع عن هذا الإجراء.')) {
                try {
                    // محاكاة API call
                    await new Promise(resolve => setTimeout(resolve, 1000))
                    
                    invoice.value.status = 'cancelled'
                    
                    alert('تم إلغاء الفاتورة بنجاح!')
                } catch (error) {
                    console.error('Error cancelling invoice:', error)
                    alert('حدث خطأ أثناء إلغاء الفاتورة')
                }
            }
        }

        // دورة الحياة
        onMounted(() => {
            loadInvoice()
        })

        return {
            invoice,
            company,
            showPaymentModal,
            processingPayment,
            paymentForm,
            formatDate,
            formatCurrency,
            getStatusText,
            getPaymentMethodText,
            getPaymentStatusText,
            printInvoice,
            downloadPDF,
            editInvoice,
            recordPayment,
            closePaymentModal,
            submitPayment,
            markAsPaid,
            cancelInvoice
        }
    }
}
</script>

<style scoped>
.invoice-details {
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.details-header {
    margin-bottom: 30px;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

.action-buttons {
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
    transition: all 0.2s;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-success {
    background: #27ae60;
    color: white;
}

.btn-success:hover {
    background: #219a52;
}

.btn-warning {
    background: #f39c12;
    color: white;
}

.btn-warning:hover {
    background: #e67e22;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.btn-outline {
    background: transparent;
    border: 1px solid #bdc3c7;
    color: #7f8c8d;
}

.btn-outline:hover {
    background: #f8f9fa;
}

/* تصميم الفاتورة */
.invoice-container {
    background: white;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #34495e;
}

.company-info h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 24px;
}

.company-info p {
    margin: 5px 0;
    color: #7f8c8d;
}

.invoice-info h2 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 28px;
    text-align: right;
}

.invoice-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.meta-item {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.meta-item .label {
    font-weight: 600;
    color: #2c3e50;
}

.meta-item .value {
    color: #7f8c8d;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.draft { background: #fdebd0; color: #f39c12; }
.status-badge.sent { background: #d5f4e6; color: #27ae60; }
.status-badge.paid { background: #d5f4e6; color: #27ae60; }
.status-badge.overdue { background: #fadbd8; color: #e74c3c; }
.status-badge.cancelled { background: #f2f3f4; color: #7f8c8d; }

/* أقسام الفاتورة */
.client-section,
.items-section,
.summary-section,
.payments-section,
.notes-section,
.payment-actions {
    margin-bottom: 30px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #ecf0f1;
}

.client-info h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.client-info p {
    margin: 5px 0;
    color: #7f8c8d;
}

/* جدول العناصر */
.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th,
.items-table td {
    padding: 12px;
    text-align: right;
    border-bottom: 1px solid #ecf0f1;
}

.items-table th {
    background: #34495e;
    color: white;
    font-weight: 500;
}

.items-table .description {
    text-align: right;
    min-width: 200px;
}

/* الملخص */
.summary-table {
    max-width: 300px;
    margin-left: auto;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #ecf0f1;
}

.summary-row.total {
    font-weight: 700;
    font-size: 16px;
    color: #2c3e50;
    border-top: 2px solid #3498db;
    padding-top: 12px;
    margin-top: 8px;
}

.summary-row.paid {
    color: #27ae60;
}

.summary-row.balance {
    color: #e74c3c;
    font-weight: 600;
}

/* المدفوعات */
.payments-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.payment-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.payment-amount {
    font-weight: 600;
    color: #2c3e50;
    font-size: 16px;
}

.payment-date {
    color: #7f8c8d;
    font-size: 14px;
}

.payment-meta {
    display: flex;
    flex-direction: column;
    gap: 5px;
    align-items: flex-end;
}

.payment-method {
    color: #7f8c8d;
    font-size: 14px;
}

.payment-status {
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
}

.payment-status.completed { background: #d5f4e6; color: #27ae60; }
.payment-status.pending { background: #fdebd0; color: #f39c12; }
.payment-status.failed { background: #fadbd8; color: #e74c3c; }

/* إجراءات الدفع */
.payment-options {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* الملاحظات */
.notes-row {
    margin-bottom: 20px;
}

.notes-row p {
    margin: 10px 0 0 0;
    color: #7f8c8d;
    line-height: 1.6;
}

/* التحميل */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-state p {
    margin: 0;
    color: #7f8c8d;
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

.form-group {
    margin-bottom: 20px;
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
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
}

.form-group small {
    color: #7f8c8d;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #ecf0f1;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* الأيقونات */
.back-icon::before { content: '←'; }
.print-icon::before { content: '🖨️'; }
.download-icon::before { content: '📥'; }
.edit-icon::before { content: '✏️'; }
.payment-icon::before { content: '💳'; }
.check-icon::before { content: '✓'; }
.cancel-icon::before { content: '✕'; }

@media (max-width: 768px) {
    .invoice-details {
        padding: 10px;
    }
    
    .header-actions {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .action-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .invoice-container {
        padding: 20px;
    }
    
    .invoice-header {
        flex-direction: column;
        gap: 20px;
    }
    
    .items-table {
        font-size: 12px;
    }
    
    .items-table th,
    .items-table td {
        padding: 8px 4px;
    }
    
    .payment-options {
        flex-direction: column;
    }
    
    .modal-footer {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

@media print {
    .details-header,
    .payment-actions {
        display: none;
    }
    
    .invoice-details {
        padding: 0;
        background: white;
    }
    
    .invoice-container {
        box-shadow: none;
        padding: 0;
    }
}
</style>
