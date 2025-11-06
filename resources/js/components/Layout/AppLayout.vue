<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <Sidebar :is-open="sidebarOpen" @toggle="sidebarOpen = !sidebarOpen" />
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col lg:pl-64">
      <!-- Header -->
      <Header @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      
      <!-- Main -->
      <main class="flex-1 p-6">
        <slot />
      </main>
      
      <!-- Footer -->
      <Footer />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from './Sidebar.vue'
import Header from './Header.vue'
import Footer from './Footer.vue'

const sidebarOpen = ref(false)
</script>