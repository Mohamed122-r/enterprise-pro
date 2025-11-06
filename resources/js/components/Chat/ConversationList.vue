<template>
  <div class="h-full bg-white border-r border-gray-200 flex flex-col">
    <!-- Header -->
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Messages</h2>
        <button
          @click="showNewConversation = true"
          class="p-2 text-gray-400 hover:text-gray-600"
        >
          <PlusIcon class="h-5 w-5" />
        </button>
      </div>
      
      <!-- Search -->
      <div class="mt-4">
        <SearchBox
          v-model="searchTerm"
          placeholder="Search conversations..."
          class="w-full"
        />
      </div>
    </div>

    <!-- Conversations List -->
    <div class="flex-1 overflow-y-auto">
      <div v-if="loading" class="flex justify-center p-4">
        <LoadingSpinner size="small" />
      </div>

      <div v-else-if="filteredConversations.length === 0" class="p-4 text-center text-gray-500">
        No conversations found
      </div>

      <div v-else class="divide-y divide-gray-200">
        <div
          v-for="conversation in filteredConversations"
          :key="conversation.id"
          :class="[
            'p-4 cursor-pointer hover:bg-gray-50 transition-colors',
            { 'bg-blue-50': conversation.id === currentConversation?.id }
          ]"
          @click="selectConversation(conversation)"
        >
          <div class="flex items-start space-x-3">
            <!-- Avatar -->
            <div class="flex-shrink-0">
              <div
                v-if="conversation.type === 'direct'"
                class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center"
              >
                <span class="text-sm font-medium text-indigo-600">
                  {{ getConversationInitial(conversation) }}
                </span>
              </div>
              <div
                v-else
                class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center"
              >
                <UsersIcon class="h-5 w-5 text-purple-600" />
              </div>
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900">
                  {{ getConversationName(conversation) }}
                </p>
                <span class="text-xs text-gray-500">
                  {{ formatTime(conversation.last_message?.created_at) }}
                </span>
              </div>
              
              <p class="text-sm text-gray-500 truncate mt-1">
                {{ getLastMessagePreview(conversation) }}
              </p>

              <div class="flex items-center justify-between mt-2">
                <div class="flex items-center space-x-2">
                  <span
                    v-if="conversation.unread_count > 0"
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                  >
                    {{ conversation.unread_count }}
                  </span>
                  <span
                    v-if="conversation.type === 'group'"
                    class="inline-flex items-center text-xs text-gray-500"
                  >
                    <UsersIcon class="h-3 w-3 mr-1" />
                    {{ conversation.participants?.length }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- New Conversation Modal -->
    <NewConversationModal
      v-if="showNewConversation"
      @close="showNewConversation = false"
      @created="handleNewConversation"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useChatStore } from '@/store/chat'
import { PlusIcon, UsersIcon } from '@heroicons/vue/24/outline'
import { formatTime } from '@/utils/formatters'
import SearchBox from '@/components/UI/SearchBox.vue'
import LoadingSpinner from '@/components/UI/LoadingSpinner.vue'
import NewConversationModal from './NewConversationModal.vue'

const chatStore = useChatStore()

const searchTerm = ref('')
const showNewConversation = ref(false)
const loading = ref(false)

const currentConversation = computed(() => chatStore.currentConversation)
const conversations = computed(() => chatStore.conversations)

const filteredConversations = computed(() => {
  if (!searchTerm.value) return conversations.value
  
  return conversations.value.filter(conv =>
    conv.name?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
    conv.participants?.some(participant =>
      participant.name.toLowerCase().includes(searchTerm.value.toLowerCase())
    )
  )
})

const loadConversations = async () => {
  loading.value = true
  try {
    await chatStore.fetchConversations()
  } catch (error) {
    console.error('Failed to load conversations:', error)
  } finally {
    loading.value = false
  }
}

const getConversationName = (conversation) => {
  if (conversation.name) return conversation.name
  
  if (conversation.type === 'direct') {
    const otherParticipant = conversation.participants?.find(
      participant => participant.id !== chatStore.user?.id
    )
    return otherParticipant?.name || 'Unknown User'
  }
  
  return 'Group Chat'
}

const getConversationInitial = (conversation) => {
  const name = getConversationName(conversation)
  return name.charAt(0).toUpperCase()
}

const getLastMessagePreview = (conversation) => {
  if (!conversation.last_message) return 'No messages yet'
  
  const message = conversation.last_message
  if (message.type === 'image') return '📷 Image'
  if (message.type === 'file') return '📎 File'
  
  return message.content.length > 50 
    ? message.content.substring(0, 50) + '...'
    : message.content
}

const selectConversation = (conversation) => {
  chatStore.setCurrentConversation(conversation)
  chatStore.markAsRead(conversation.id)
}

const handleNewConversation = (conversation) => {
  showNewConversation.value = false
  selectConversation(conversation)
}

// Auto-refresh conversations
watch(() => chatStore.connected, (connected) => {
  if (connected) {
    loadConversations()
  }
})

onMounted(() => {
  loadConversations()
})
</script>