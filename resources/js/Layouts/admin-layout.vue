<script setup>
import {Link, usePage} from '@inertiajs/vue3';
import { computed } from 'vue';


const imageName = 'dswd-logo-transparent.png';
const dashboardIcon = 'dashboard.png';
const reportsIcon = 'reports.png';
const accountsIcon = 'accounts.png';
const programsIcon = 'programs.png';
const logsIcon = 'logs.png';
const logoutIcon = 'logout.png';

const page = usePage();

const currentRoute = computed(() => route().current());
</script>

<template>
  <div class="fixed top-0 left-0 h-screen w-60 bg-gradient-to-b from-blue-300 to-blue-900 text-white flex flex-col">
    <div class="pb-4">
      <img :src="`/ppd-images/${imageName}`" alt="DSWD Logo" class="h-24 mr-2">
    </div>
    
    <nav class="flex flex-col flex-grow">
      <div>
        <Link :href="route('admin-dashboard')" 
        :class="['flex items-center py-4 pr-4',
          { 'bg-blue-600': currentRoute === 'admin-dashboard' },
          { 'hover:bg-blue-500': currentRoute !== 'admin-dashboard' }
        ]">
          <i class="fas fa-tachometer-alt mr-2"></i> <img :src="`/ppd-images/${dashboardIcon}`" alt="Dashboard Icon" class="h-6 w-6 mr-2"> DASHBOARD
        </Link>

        <Link :href="route('admin-reports')" 
        :class="['flex items-center py-4 pr-4',
          { 'bg-blue-600': currentRoute === 'admin-reports' },
          { 'hover:bg-blue-500': currentRoute !== 'admin-reports' }
        ]">
          <i class="fas fa-file-alt mr-2"></i> <img :src="`/ppd-images/${reportsIcon}`" alt="Reports Icon" class="h-6 w-6 mr-2">  REPORTS
        </Link>

        <Link :href="route('admin-accounts')" 
        :class="['flex items-center py-4 pr-4',
          { 'bg-blue-600': currentRoute === 'admin-accounts' },
          { 'hover:bg-blue-500': currentRoute !== 'admin-accounts' }
        ]">
          <i class="fas fa-users mr-2"></i> <img :src="`/ppd-images/${accountsIcon}`" alt="Accounts Icon" class="h-6 w-6 mr-2">  ACCOUNTS
        </Link>

        <Link :href="route('admin-programs')" 
        :class="['flex items-center py-4 pr-4',
          { 'bg-blue-600': currentRoute === 'admin-programs' },
          { 'hover:bg-blue-500': currentRoute !== 'admin-programs' }
        ]">
          <i class="fas fa-project-diagram mr-2"></i> <img :src="`/ppd-images/${programsIcon}`" alt="Programs Icon" class="h-6 w-6 mr-2">  PROGRAMS
        </Link>

        <Link :href="route('admin-logs')" 
        :class="['flex items-center py-4 pr-4',
          { 'bg-blue-600': currentRoute === 'admin-logs' },
          { 'hover:bg-blue-500': currentRoute !== 'admin-logs' }
        ]">
          <i class="fas fa-list-alt mr-2"></i> <img :src="`/ppd-images/${logsIcon}`" alt="Logs Icon" class="h-6 w-6 mr-2">  LOGS
        </Link>

      
      </div>

      
      
      <div class="mt-auto">
        <Link 
        :href="route('logout')" 
          method="post"
          as="button"
          class="flex items-center py-4 w-full text-left hover:bg-red-600"
        >
        <i class="fas fa-list-alt mr-2"></i> <img :src="`/ppd-images/${logoutIcon}`" alt="Logout Icon" class="h-6 w-6 mr-2"> LOGOUT
        </Link>
      </div>
    </nav>
  </div>
</template>