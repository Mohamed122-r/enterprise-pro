import { ref, reactive } from 'vue'

export function useForm(initialValues = {}) {
  const form = reactive({ ...initialValues })
  const errors = ref({})
  const loading = ref(false)
  const success = ref(false)

  const setError = (field, message) => {
    errors.value[field] = message
  }

  const clearErrors = (field = null) => {
    if (field) {
      delete errors.value[field]
    } else {
      errors.value = {}
    }
  }

  const setForm = (values) => {
    Object.keys(values).forEach(key => {
      form[key] = values[key]
    })
  }

  const resetForm = () => {
    Object.keys(form).forEach(key => {
      form[key] = initialValues[key] || ''
    })
    clearErrors()
    success.value = false
  }

  const hasErrors = () => {
    return Object.keys(errors.value).length > 0
  }

  const getFieldError = (field) => {
    return errors.value[field]
  }

  const setLoading = (isLoading) => {
    loading.value = isLoading
  }

  const setSuccess = (isSuccess) => {
    success.value = isSuccess
  }

  const handleApiError = (error) => {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
      return true
    }
    return false
  }

  const validate = (rules) => {
    clearErrors()
    
    for (const [field, rule] of Object.entries(rules)) {
      const value = form[field]
      
      if (rule.required && (!value || value === '')) {
        setError(field, rule.required)
        continue
      }
      
      if (rule.email && value && !isValidEmail(value)) {
        setError(field, rule.email)
        continue
      }
      
      if (rule.minLength && value && value.length < rule.minLength) {
        setError(field, rule.minLength.message)
        continue
      }
      
      if (rule.maxLength && value && value.length > rule.maxLength) {
        setError(field, rule.maxLength.message)
        continue
      }
      
      if (rule.pattern && value && !rule.pattern.test(value)) {
        setError(field, rule.pattern.message)
        continue
      }
      
      if (rule.custom && value) {
        const customError = rule.custom(value)
        if (customError) {
          setError(field, customError)
        }
      }
    }
    
    return !hasErrors()
  }

  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  const submit = async (submitFn, options = {}) => {
    const { validate: shouldValidate = true, rules = {} } = options
    
    if (shouldValidate && !validate(rules)) {
      return { success: false, errors: errors.value }
    }
    
    loading.value = true
    success.value = false
    clearErrors()
    
    try {
      const result = await submitFn(form)
      success.value = true
      return { success: true, data: result }
    } catch (error) {
      if (!handleApiError(error)) {
        errors.value.general = error.response?.data?.message || 'An error occurred'
      }
      return { success: false, errors: errors.value, error }
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    form,
    errors,
    loading,
    success,
    
    // Methods
    setError,
    clearErrors,
    setForm,
    resetForm,
    hasErrors,
    getFieldError,
    setLoading,
    setSuccess,
    validate,
    submit
  }
}