<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// Importing the logo and icon
const iconImageName = 'dswd-icon.png';  // For collapsed state
const logoImageName = 'dswd-logo-transparent.png';  // For expanded state
const dashboardIcon = 'dashboard.png';
const reportsIcon = 'reports.png';
const accountsIcon = 'accounts.png';
const programsIcon = 'programs.png';
const logsIcon = 'logs.png';
const logoutIcon = 'logout.png';

const page = usePage();
const currentRoute = computed(() => route().current());

const isExpanded = ref(false);

// Emit the state to the parent so it can adjust the layout
const emit = defineEmits(['sidebar-expanded']);
const toggleSidebar = (state) => {
  isExpanded.value = state;
  emit('sidebar-expanded', state);
};
</script>

<template>
  <div
    class="fixed top-0 left-0 h-screen bg-white text-black font-semibold flex flex-col transition-all duration-300 ease-in-out"
    :class="[isExpanded ? 'w-60' : 'w-16']"
    @mouseenter="toggleSidebar(true)"
    @mouseleave="toggleSidebar(false)"
  >
    <div class="pb-2 flex justify-center items-center h-24">
      <!-- Switch between the icon and full logo depending on the sidebar's state -->
      <img
        :src="isExpanded ? `/ppd-images/${logoImageName}` : `/ppd-images/${iconImageName}`"
        alt="DSWD Logo"
        :class="isExpanded ? 'h-20 w-auto object-contain' : 'h-16 w-16 object-contain'"
      />
    </div>

    <nav class="flex flex-col flex-grow">
      <Link
        v-for="(item, index) in [
          { route: 'admin-dashboard', icon: dashboardIcon, text: 'DASHBOARD' },
          { route: 'admin-reports', icon: reportsIcon, text: 'REPORTS' },
          { route: 'admin-accounts', icon: accountsIcon, text: 'ACCOUNTS' },
          { route: 'admin-programs', icon: programsIcon, text: 'PROGRAMS' },
          { route: 'admin-logs', icon: logsIcon, text: 'LOGS' },
        ]"
        :key="index"
        :href="route(item.route)"
        :class="[
          'flex items-center py-4 px-4',
          { 'bg-blue-600': currentRoute === item.route },
          { 'hover:bg-blue-400': currentRoute !== item.route }
        ]"
      >
        <img :src="`/ppd-images/${item.icon}`" :alt="`${item.text} Icon`" class="h-6 w-6 mr-2" />
        <span :class="{ 'hidden': !isExpanded }">{{ item.text }}</span>
      </Link>
    </nav>

    <div class="mt-auto">
      <Link
        :href="route('logout')"
        method="post"
        as="button"
        class="flex items-center py-4 w-full text-left hover:bg-red-600 px-4"
      >
        <img :src="`/ppd-images/${logoutIcon}`" alt="Logout Icon" class="h-6 w-6 mr-2" />
        <span :class="{ 'hidden': !isExpanded }">LOGOUT</span>
      </Link>
    </div>
  </div>
</template>
