<template>
    <div class="login-container">
        <div class="login-background"></div>
        
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo">
                    <i class="logo-icon">🚀</i>
                </div>
                <h1 class="app-title">Enterprise Pro</h1>
                <p class="app-subtitle">Complete Business Management System</p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="handleLogin" class="login-form">
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="icon">📧</i>
                        Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        v-model="form.email"
                        required
                        class="form-input"
                        placeholder="Enter your email"
                        :class="{ 'error': errors.email }"
                    >
                    <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="icon">🔒</i>
                        Password
                    </label>
                    <div class="password-input-container">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            v-model="form.password"
                            required
                            class="form-input"
                            placeholder="Enter your password"
                            :class="{ 'error': errors.password }"
                        >
                        <button
                            type="button"
                            class="password-toggle"
                            @click="showPassword = !showPassword"
                        >
                            <i class="icon">{{ showPassword ? '👁️' : '👁️‍🗨️' }}</i>
                        </button>
                    </div>
                    <span v-if="errors.password" class="error-text">{{ errors.password }}</span>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" v-model="form.remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="/forgot-password" class="forgot-password">
                        Forgot password?
                    </a>
                </div>

                <!-- Login Button -->
                <button
                    type="submit"
                    class="login-btn"
                    :disabled="loading"
                    :class="{ 'loading': loading }"
                >
                    <span v-if="!loading">
                        <i class="icon">🔑</i>
                        Sign In
                    </span>
                    <span v-else>
                        <div class="spinner"></div>
                        Signing In...
                    </span>
                </button>

                <!-- Register Link -->
                <div class="register-link">
                    <p>Don't have an account? 
                        <router-link to="/register" class="link">
                            Create new account
                        </router-link>
                    </p>
                </div>
            </form>

            <!-- Demo Section -->
            <div class="demo-section">
                <div class="demo-divider">
                    <span>Or try demo version</span>
                </div>
                <button @click="fillDemoCredentials" class="demo-btn">
                    <i class="icon">🎯</i>
                    Demo Login
                </button>
            </div>
        </div>

        <!-- Notifications -->
        <div v-if="showNotification" class="notification" :class="notificationType">
            <i class="notification-icon">{{ notificationIcon }}</i>
            <span>{{ notificationMessage }}</span>
            <button @click="showNotification = false" class="notification-close">×</button>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'

export default {
    name: 'Login',
    setup() {
        const router = useRouter()
        const authStore = useAuthStore()
        
        const loading = ref(false)
        const showPassword = ref(false)
        const showNotification = ref(false)
        const notificationMessage = ref('')
        const notificationType = ref('success')

        const form = reactive({
            email: '',
            password: '',
            remember: false
        })

        const errors = reactive({
            email: '',
            password: ''
        })

        // Notifications
        const notificationIcon = computed(() => {
            return notificationType.value === 'success' ? '✅' : '❌'
        })

        const showNotificationMessage = (message, type = 'success') => {
            notificationMessage.value = message
            notificationType.value = type
            showNotification.value = true
            setTimeout(() => {
                showNotification.value = false
            }, 5000)
        }

        // Form Validation
        const validateForm = () => {
            let isValid = true
            
            // Reset errors
            errors.email = ''
            errors.password = ''

            if (!form.email.trim()) {
                errors.email = 'Email is required'
                isValid = false
            } else if (!isValidEmail(form.email)) {
                errors.email = 'Please enter a valid email'
                isValid = false
            }

            if (!form.password) {
                errors.password = 'Password is required'
                isValid = false
            } else if (form.password.length < 6) {
                errors.password = 'Password must be at least 6 characters'
                isValid = false
            }

            return isValid
        }

        const isValidEmail = (email) => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            return emailRegex.test(email)
        }

        // Login Handler - متوافق مع الـ Store الجديد
        const handleLogin = async () => {
            if (!validateForm()) return

            loading.value = true

            try {
                const result = await authStore.login(form)
                
                if (result.success) {
                    showNotificationMessage('Login successful!', 'success')
                    
                    // التوجيه للوحة التحكم
                    setTimeout(() => {
                        router.push('/')
                    }, 1000)
                } else {
                    showNotificationMessage(
                        result.message || 'Login failed. Please check your credentials.', 
                        'error'
                    )
                }
                
            } catch (error) {
                showNotificationMessage(
                    'An unexpected error occurred. Please try again.', 
                    'error'
                )
                console.error('Login error:', error)
            } finally {
                loading.value = false
            }
        }

        // Fill demo credentials
        const fillDemoCredentials = () => {
            form.email = 'admin@enterprise.com'
            form.password = 'password123'
            showNotificationMessage('Demo credentials filled', 'success')
        }

        return {
            form,
            errors,
            loading,
            showPassword,
            showNotification,
            notificationMessage,
            notificationType,
            notificationIcon,
            handleLogin,
            fillDemoCredentials
        }
    }
}
</script>

<style scoped>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    position: relative;
}

.login-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="%23ffffff" fill-opacity="0.05" points="0,1000 1000,0 1000,1000"/></svg>');
}

.login-card {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 420px;
    position: relative;
    z-index: 1;
}

/* Logo */
.logo-section {
    text-align: center;
    margin-bottom: 30px;
}

.logo {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
}

.logo-icon {
    font-size: 32px;
}

.app-title {
    font-size: 28px;
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 5px 0;
}

.app-subtitle {
    color: #718096;
    margin: 0;
    font-size: 14px;
}

/* Form */
.login-form {
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    color: #4a5568;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #fafafa;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input.error {
    border-color: #e53e3e;
    background: #fff5f5;
}

.password-input-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #718096;
    padding: 5px;
}

.error-text {
    color: #e53e3e;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

/* Form Options */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    color: #4a5568;
}

.checkbox-label input {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #cbd5e0;
    border-radius: 4px;
    position: relative;
    transition: all 0.3s ease;
}

.checkbox-label input:checked + .checkmark {
    background: #667eea;
    border-color: #667eea;
}

.checkbox-label input:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    color: white;
    font-size: 12px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.forgot-password {
    color: #667eea;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s ease;
}

.forgot-password:hover {
    color: #5a67d8;
    text-decoration: underline;
}

/* Login Button */
.login-btn {
    width: 100%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.login-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.login-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.login-btn.loading {
    background: #a0aec0;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Register Link */
.register-link {
    text-align: center;
    color: #718096;
    font-size: 14px;
}

.register-link .link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.register-link .link:hover {
    text-decoration: underline;
}

/* Demo Section */
.demo-section {
    border-top: 1px solid #e2e8f0;
    padding-top: 25px;
}

.demo-divider {
    text-align: center;
    margin-bottom: 15px;
    position: relative;
}

.demo-divider span {
    background: white;
    padding: 0 15px;
    color: #718096;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.demo-btn {
    width: 100%;
    background: #48bb78;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.demo-btn:hover {
    background: #38a169;
    transform: translateY(-1px);
}

/* Notifications */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 1000;
    border-left: 4px solid;
    animation: slideIn 0.3s ease;
}

.notification.success {
    border-left-color: #48bb78;
}

.notification.error {
    border-left-color: #e53e3e;
}

.notification-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #718096;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 480px) {
    .login-container {
        padding: 10px;
    }
    
    .login-card {
        padding: 30px 20px;
    }
    
    .form-options {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}

/* Icons */
.icon {
    font-style: normal;
}
</style>
