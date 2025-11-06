<template>
  <footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row justify-between items-center">
        <div class="flex items-center space-x-4">
          <img class="h-8 w-auto" src="/logo.png" alt="Enterprise Pro">
          <p class="text-sm text-gray-500">
            &copy; {{ currentYear }} Enterprise Pro. All rights reserved.
          </p>
        </div>
        
        <div class="mt-4 md:mt-0 flex items-center space-x-6">
          <a href="#" class="text-sm text-gray-500 hover:text-gray-700">
            Privacy Policy
          </a>
          <a href="#" class="text-sm text-gray-500 hover:text-gray-700">
            Terms of Service
          </a>
          <a href="#" class="text-sm text-gray-500 hover:text-gray-700">
            Support
          </a>
        </div>
        
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
          <span class="text-sm text-gray-500">Version {{ appVersion }}</span>
          <div class="h-2 w-2 rounded-full bg-green-400" title="System Online"></div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue'

const currentYear = ref(new Date().getFullYear())
const appVersion = ref('1.0.0')
</script>