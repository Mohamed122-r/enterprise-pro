<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Opportunity Pipeline</h1>
        <p class="text-gray-600">Track and manage sales opportunities</p>
      </div>
      <button
        v-if="authStore.hasPermission('opportunities.create')"
        @click="showOpportunityForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Add Opportunity
      </button>
    </div>

    <!-- Pipeline Stages -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div
        v-for="stage in pipelineStages"
        :key="stage.key"
        class="bg-gray-50 rounded-lg p-4"
      >
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-medium text-gray-900">{{ stage.label }}</h3>
          <span class="bg-white rounded-full px-2 py-1 text-xs font-medium text-gray-600">
            {{ getOpportunitiesByStage(stage.key).length }}
          </span>
        </div>
        
        <div class="space-y-3">
          <div
            v-for="opportunity in getOpportunitiesByStage(stage.key)"
            :key="opportunity.id"
            class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm cursor-pointer hover:shadow-md transition-shadow"
            @click="viewOpportunity(opportunity)"
          >
            <div class="flex justify-between items-start mb-2">
              <h4 class="font-medium text-gray-900 text-sm">{{ opportunity.title }}</h4>
              <span class="text-xs font-medium text-gray-500">
                {{ formatCurrency(opportunity.value) }}
              </span>
            </div>
            
            <div class="flex items-center justify-between text-xs text-gray-500">
              <span>{{ opportunity.contact?.full_name }}</span>
              <span>{{ opportunity.probability }}%</span>
            </div>
            
            <div class="mt-2 flex items-center justify-between">
              <span class="text-xs text-gray-400">
                {{ formatDate(opportunity.expected_close_date) }}
              </span>
              <span class="text-xs font-medium" :class="getProbabilityColor(opportunity.probability)">
                {{ getProbabilityText(opportunity.probability) }}
              </span>
            </div>
          </div>
          
          <div
            v-if="getOpportunitiesByStage(stage.key).length === 0"
            class="text-center py-4 text-gray-400 text-sm"
          >
            No opportunities
          </div>
        </div>
      </div>
    </div>

    <!-- Opportunity Form Modal -->
    <OpportunityForm
      v-if="showOpportunityForm"
      :opportunity="selectedOpportunity"
      @save="handleOpportunitySave"
      @close="showOpportunityForm = false"
    />

    <!-- Opportunity Details Modal -->
    <OpportunityDetails
      v-if="showOpportunityDetails && selectedOpportunity"
      :opportunity="selectedOpportunity"
      @close="showOpportunityDetails = false"
      @update="handleOpportunityUpdate"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth'
import { useCrmStore } from '@/store/crm'
import { formatCurrency, formatDate } from '@/utils/formatters'
import OpportunityForm from './OpportunityForm.vue'
import OpportunityDetails from './OpportunityDetails.vue'

const authStore = useAuthStore()
const crmStore = useCrmStore()

const opportunities = ref([])
const showOpportunityForm = ref(false)
const showOpportunityDetails = ref(false)
const selectedOpportunity = ref(null)

const pipelineStages = [
  { key: 'prospect', label: 'Prospect', color: 'bg-gray-500' },
  { key: 'qualification', label: 'Qualification', color: 'bg-blue-500' },
  { key: 'proposal', label: 'Proposal', color: 'bg-yellow-500' },
  { key: 'negotiation', label: 'Negotiation', color: 'bg-orange-500' },
  { key: 'closed_won', label: 'Closed Won', color: 'bg-green-500' }
]

const loadOpportunities = async () => {
  try {
    await crmStore.fetchOpportunities()
    opportunities.value = crmStore.opportunities
  } catch (error) {
    console.error('Failed to load opportunities:', error)
  }
}

const getOpportunitiesByStage = (stage) => {
  return opportunities.value.filter(opp => opp.stage === stage)
}

const getProbabilityColor = (probability) => {
  if (probability >= 80) return 'text-green-600'
  if (probability >= 50) return 'text-yellow-600'
  return 'text-red-600'
}

const getProbabilityText = (probability) => {
  if (probability >= 80) return 'High'
  if (probability >= 50) return 'Medium'
  return 'Low'
}

const viewOpportunity = (opportunity) => {
  selectedOpportunity.value = opportunity
  showOpportunityDetails.value = true
}

const handleOpportunitySave = () => {
  showOpportunityForm.value = false
  selectedOpportunity.value = null
  loadOpportunities()
}

const handleOpportunityUpdate = () => {
  showOpportunityDetails.value = false
  selectedOpportunity.value = null
  loadOpportunities()
}

onMounted(() => {
  loadOpportunities()
})
</script>