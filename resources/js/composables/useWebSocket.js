import { ref, onUnmounted } from 'vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

export function useWebSocket() {
  const echo = ref(null)
  const connected = ref(false)
  const subscriptions = ref(new Map())

  const connect = (config = {}) => {
    if (echo.value) {
      disconnect()
    }

    const token = localStorage.getItem('auth_token')
    
    echo.value = new Echo({
      broadcaster: 'pusher',
      key: import.meta.env.VITE_PUSHER_APP_KEY || 'local',
      wsHost: import.meta.env.VITE_PUSHER_HOST || '127.0.0.1',
      wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
      wssPort: import.meta.env.VITE_PUSHER_PORT || 6001,
      forceTLS: false,
      encrypted: true,
      disableStats: true,
      enabledTransports: ['ws', 'wss'],
      auth: {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
      ...config
    })

    echo.value.connector.pusher.connection.bind('connected', () => {
      connected.value = true
      console.log('WebSocket connected')
    })

    echo.value.connector.pusher.connection.bind('disconnected', () => {
      connected.value = false
      console.log('WebSocket disconnected')
    })

    echo.value.connector.pusher.connection.bind('error', (error) => {
      console.error('WebSocket error:', error)
    })
  }

  const disconnect = () => {
    if (echo.value) {
      // Unsubscribe from all channels
      subscriptions.value.forEach((channel, channelName) => {
        channel.unsubscribe()
      })
      subscriptions.value.clear()
      
      echo.value.disconnect()
      echo.value = null
      connected.value = false
    }
  }

  const subscribe = (channelName, events) => {
    if (!echo.value) {
      console.warn('Echo not initialized. Call connect() first.')
      return null
    }

    // Unsubscribe if already subscribed
    if (subscriptions.value.has(channelName)) {
      subscriptions.value.get(channelName).unsubscribe()
    }

    const channel = echo.value.channel(channelName)
    
    // Subscribe to events
    Object.entries(events).forEach(([event, callback]) => {
      channel.listen(event, callback)
    })

    subscriptions.value.set(channelName, channel)
    return channel
  }

  const subscribePrivate = (channelName, events) => {
    if (!echo.value) {
      console.warn('Echo not initialized. Call connect() first.')
      return null
    }

    const privateChannel = echo.value.private(channelName)
    
    Object.entries(events).forEach(([event, callback]) => {
      privateChannel.listen(event, callback)
    })

    subscriptions.value.set(channelName, privateChannel)
    return privateChannel
  }

  const unsubscribe = (channelName) => {
    if (subscriptions.value.has(channelName)) {
      subscriptions.value.get(channelName).unsubscribe()
      subscriptions.value.delete(channelName)
    }
  }

  const broadcast = (channelName, event, data) => {
    if (!echo.value) {
      console.warn('Echo not initialized. Call connect() first.')
      return
    }

    // This would typically be done from the backend
    console.log('Broadcasting:', { channelName, event, data })
  }

  const isSubscribed = (channelName) => {
    return subscriptions.value.has(channelName)
  }

  // Auto-disconnect when component unmounts
  onUnmounted(() => {
    disconnect()
  })

  return {
    echo,
    connected,
    connect,
    disconnect,
    subscribe,
    subscribePrivate,
    unsubscribe,
    broadcast,
    isSubscribed
  }
}