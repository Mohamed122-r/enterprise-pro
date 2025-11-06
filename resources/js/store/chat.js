import { defineStore } from 'pinia'
import { chatApi } from '@/utils/api'
import { useWebSocket } from '@/composables/useWebSocket'

export const useChatStore = defineStore('chat', {
  state: () => ({
    conversations: [],
    currentConversation: null,
    messages: [],
    loading: false,
    error: null,
    connected: false
  }),

  getters: {
    getConversationById: (state) => (id) => {
      return state.conversations.find(conv => conv.id === id)
    },
    
    unreadCount: (state) => {
      return state.conversations.reduce((count, conv) => {
        return count + (conv.unread_count || 0)
      }, 0)
    },
    
    directConversations: (state) => {
      return state.conversations.filter(conv => conv.type === 'direct')
    },
    
    groupConversations: (state) => {
      return state.conversations.filter(conv => conv.type === 'group')
    }
  },

  actions: {
    // Conversations
    async fetchConversations() {
      this.loading = true
      this.error = null
      
      try {
        const response = await chatApi.conversations()
        this.conversations = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch conversations'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createConversation(participantIds, name = null) {
      this.loading = true
      this.error = null
      
      try {
        const response = await chatApi.createConversation({
          user_ids: participantIds,
          name
        })
        
        this.conversations.unshift(response.data.data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create conversation'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Messages
    async fetchMessages(conversationId) {
      this.loading = true
      this.error = null
      
      try {
        const response = await chatApi.messages(conversationId)
        this.messages = response.data.data
        this.currentConversation = this.getConversationById(conversationId)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch messages'
        throw error
      } finally {
        this.loading = false
      }
    },

    async sendMessage(conversationId, content, type = 'text') {
      this.loading = true
      this.error = null
      
      try {
        const response = await chatApi.sendMessage(conversationId, {
          content,
          type
        })
        
        this.messages.push(response.data.data)
        
        // Update conversation last message
        const conversation = this.getConversationById(conversationId)
        if (conversation) {
          conversation.last_message = response.data.data
          conversation.updated_at = new Date().toISOString()
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to send message'
        throw error
      } finally {
        this.loading = false
      }
    },

    // WebSocket integration
    connectWebSocket() {
      const { connect, subscribe, isConnected } = useWebSocket()
      
      if (!isConnected.value) {
        connect()
      }
      
      // Subscribe to conversation updates
      subscribe(`conversation.${this.currentConversation?.id}`, (data) => {
        this.handleIncomingMessage(data)
      })
      
      this.connected = true
    },

    handleIncomingMessage(messageData) {
      // Check if message belongs to current conversation
      if (messageData.conversation_id === this.currentConversation?.id) {
        this.messages.push(messageData)
      }
      
      // Update conversation in list
      const conversation = this.getConversationById(messageData.conversation_id)
      if (conversation) {
        conversation.last_message = messageData
        conversation.updated_at = messageData.created_at
        
        // Increment unread count if not current conversation
        if (messageData.conversation_id !== this.currentConversation?.id) {
          conversation.unread_count = (conversation.unread_count || 0) + 1
        }
      }
    },

    markAsRead(conversationId) {
      const conversation = this.getConversationById(conversationId)
      if (conversation) {
        conversation.unread_count = 0
      }
    },

    // Utility methods
    setCurrentConversation(conversation) {
      this.currentConversation = conversation
      this.markAsRead(conversation.id)
    },

    clearCurrentConversation() {
      this.currentConversation = null
      this.messages = []
    },

    clearError() {
      this.error = null
    },

    // Search and filtering
    searchConversations(searchTerm) {
      if (!searchTerm) return this.conversations
      
      return this.conversations.filter(conv =>
        conv.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        conv.participants?.some(participant =>
          participant.name.toLowerCase().includes(searchTerm.toLowerCase())
        )
      )
    },

    getConversationWithUser(userId) {
      return this.conversations.find(conv =>
        conv.type === 'direct' &&
        conv.participants?.some(participant => participant.id === userId)
      )
    }
  }
})