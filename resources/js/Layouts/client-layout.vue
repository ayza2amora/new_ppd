<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// Icon and logo images
const iconImageName = 'dswd-icon.png';  // For collapsed state
const logoImageName = 'dswd-logo-transparent.png';  // For expanded state
const reportsIcon = 'reports.png';
const logsIcon = 'logs.png';
const logoutIcon = 'logout.png';

const page = usePage();
const currentRoute = computed(() => route().current());

const isExpanded = ref(false);

// Emit the state to the parent layout for content width adjustment
const emit = defineEmits(['sidebar-expanded']);
const toggleSidebar = (state) => {
  isExpanded.value = state;
  emit('sidebar-expanded', state);
};
</script>

<template>
  <div
    class="fixed top-0 left-0 h-screen bg-white text-black flex flex-col transition-all duration-300 ease-in-out"
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


    <!-- Navigation Links -->
    <nav class="flex flex-col flex-grow">
      <div>
        <Link
          :href="route('client-reports')"
          :class="[
            'flex items-center py-4 px-2',
            { 'bg-blue-600': currentRoute === 'client-reports' },
            { 'hover:bg-blue-200': currentRoute !== 'client-reports' }
          ]"
        >
          <i class="fas fa-file-alt mr-2 ml-2"></i>
          <img :src="`/ppd-images/${reportsIcon}`" alt="Reports Icon" class="h-6 w-6 mr-2" />
          <span :class="{ 'hidden': !isExpanded }">REPORTS</span>
        </Link>

        <Link
          :href="route('client-logs')"
          :class="[
            'flex items-center py-4 px-2',
            { 'bg-blue-600': currentRoute === 'client-logs' },
            { 'hover:bg-blue-200': currentRoute !== 'client-logs' }
          ]"
        >
          <i class="fas fa-list-alt mr-2 ml-2"></i>
          <img :src="`/ppd-images/${logsIcon}`" alt="Logs Icon" class="h-6 w-6 mr-2" />
          <span :class="{ 'hidden': !isExpanded }">LOGS</span>
        </Link>
      </div>

      <!-- Logout Link at the Bottom -->
      <div class="mt-auto">
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="flex items-center py-4 px-4 w-full text-left hover:bg-red-600"
        >
          <i class="fas fa-sign-out-alt mr-2 ml-2"></i>
          <img :src="`/ppd-images/${logoutIcon}`" alt="Logout Icon" class="h-6 w-6 mr-2" />
          <span :class="{ 'hidden': !isExpanded }">LOGOUT</span>
        </Link>
      </div>
    </nav>
  </div>
</template>
