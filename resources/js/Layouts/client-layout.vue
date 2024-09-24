

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const logo = 'dswd-logo-transparent.png';
const reportsIcon = 'reports.png';
const logsIcon = 'logs.png';
const profileIcon = 'profile.png';
const logoutIcon = 'logout.png';
const page = usePage();
const currentRoute = computed(() => route().current());
</script>

<template>
  <div class="fixed top-0 left-0 h-screen w-60 bg-gradient-to-b from-blue-300 to-blue-900 text-white flex flex-col">
    <div class="pb-4">
      <img :src="`/ppd-images/${logo}`" alt="DSWD Logo" class="h-24 mr-2">
    </div>
    <nav class="flex flex-col flex-grow">
      <div>
        <Link 
          :href="route('client-reports')" 
          :class="[
            'flex items-center py-4 pr-4',
            { 'bg-blue-600': currentRoute === 'client-reports' },
            { 'hover:bg-blue-500': currentRoute !== 'client-reports' }
          ]"
        >
          <i class="fas fa-file-alt mr-2 ml-2"></i> <img :src="`/ppd-images/${reportsIcon}`" alt="Report Icon" class="h-6 w-6 mr-2"> REPORTS
        </Link>
        <Link :href="route('client-logs')" 
        :class="['flex items-center py-4 pr-4',
          { 'bg-blue-600': currentRoute === 'client-logs' },
          { 'hover:bg-blue-500': currentRoute !== 'client-logs' }
        ]">
          <i class="fas fa-list-alt mr-2 ml-2"></i> <img :src="`/ppd-images/${logsIcon}`" alt="Logs Icon" class="h-6 w-6 mr-2">  LOGS
        </Link>

      </div>

      <div class="mt-auto">
        <Link 
          :href="route('logout')" 
          method="post"
          as="button"
          class="flex items-center py-4  w-full text-left hover:bg-red-600"
        >
        <i class="fas fa-list-alt mr-2 ml-2"></i> <img :src="`/ppd-images/${logoutIcon}`" alt="Logout Icon" class="h-6 w-6 mr-2"> LOGOUT
      </Link>
      </div>
    </nav>
  </div>
</template>