<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="text-center max-w-md">
      <div class="text-9xl font-bold text-gray-200 mb-4">404</div>
      <h1 class="text-3xl font-bold text-gray-800 mb-4">Page Not Found</h1>
      <p class="text-gray-600 mb-8 text-lg">Sorry, the page you are looking for doesn't exist.</p>
      <div class="space-y-4">
        <button @click="$router.push('/')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
          Go to Dashboard
        </button>
        <button @click="$router.back()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition duration-200">
          Go Back
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default { name: 'NotFound' }
</script>
