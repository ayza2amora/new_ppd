<script setup>
import { ref, onMounted, computed } from 'vue';
import Layout from '../../Layouts/admin-layout.vue';
import { usePage } from '@inertiajs/vue3';

const users = ref([]);
const filter = ref('all');
const searchQuery = ref('');
const searchInput = ref('');

const approveIcon = 'approve.png';
const activateIcon = 'activate.png';
const deactivateIcon = 'deactivate.png';
const newProgramName = ref('');
const showModal = ref(false);
const newUserType = ref('');
const newProgram = ref('');
const selectedUserId = ref(null);
const modalType = ref(''); // 'role' or 'program'

const { props } = usePage();
const programs = ref(props.programs || []); // Ensure programs is always an array

// Fetch users and programs
const fetchUsers = async () => {
  try {
    const response = await fetch('/users');
    const data = await response.json();
    
    // Ensure each user has a program field, even if it's null
    users.value = data.map(user => ({
      ...user,
      program: user.program || { id: '', name: '' }  // Default empty program if not set
    }));

    // Fetch programs if they're not already available
    if (programs.value.length === 0) {
      const programsResponse = await fetch('/programs');
      const programsData = await programsResponse.json();
      programs.value = programsData;
    }
  } catch (error) {
    console.error('Error fetching data:', error);
  }
};

// Computed property to filter users based on their approval status
const filteredUsers = computed(() => {
  let filtered = users.value;

  if (filter.value === 'approved') {
    filtered = filtered.filter(user => user.approved === 1);
  } else if (filter.value === 'pending') {
    filtered = filtered.filter(user => user.approved === null || user.approved === 0);
  }

  if (searchQuery.value) {
    filtered = filtered.filter(user =>
      user.employeeid.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  return filtered;
});

// Perform search based on user input
const performSearch = () => {
  searchQuery.value = searchInput.value;
};

// Approve user
const approveUser = async (userId) => {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }

    const response = await fetch(`/users/${userId}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    // Update the user's status in the local state
    const userIndex = users.value.findIndex(user => user.id === userId);
    if (userIndex !== -1) {
      users.value[userIndex].approved = 1;
      users.value[userIndex].active = 0;
    }
  } catch (error) {
    console.error('Error approving user:', error);
  }
};

// Confirm user role change
const confirmUserRoleChange = (userId, updatedRole) => {
  selectedUserId.value = userId;
  newUserType.value = updatedRole;
  modalType.value = 'role';
  showModal.value = true;
};

// Confirm program change
const confirmUserProgramChange = (userId, updatedProgramId) => {
  selectedUserId.value = userId;
  
  // Find the program by ID
  const selectedProgram = programs.value.find(program => program.id === parseInt(updatedProgramId));
  
  if (selectedProgram) {
    newProgram.value = selectedProgram.id;  // Store the program ID for submission
    newProgramName.value = selectedProgram.name;  // Store the program name for the modal display
  }

  modalType.value = 'program';
  showModal.value = true;
};

// Cancel changes
const cancelChange = () => {
  showModal.value = false;
};

// Update user role
const updateUserRole = async () => {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }

    const response = await fetch(`/users/${selectedUserId.value}/role`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({ role: newUserType.value }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    // Update the user's role in the local state
    const userIndex = users.value.findIndex(user => user.id === selectedUserId.value);
    if (userIndex !== -1) {
      users.value[userIndex].role = newUserType.value;
    }

    showModal.value = false;
  } catch (error) {
    console.error('Error updating user role:', error);
  }
};

// Update user program
const updateUserProgram = async () => {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }

    const response = await fetch(`/users/${selectedUserId.value}/program`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({ program: newProgram.value }),  // Send only the program ID
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('Error response text:', errorText);
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const updatedUser = await response.json();  // Get the updated user

    // Update the user's program in the local state
    const userIndex = users.value.findIndex(user => user.id === selectedUserId.value);
    if (userIndex !== -1) {
      users.value[userIndex] = updatedUser;  // Replace the user object with the updated one
    }

    showModal.value = false;
  } catch (error) {
    console.error('Error updating user program:', error);
  }
};




// Activate user
const activateUser = async (userId) => {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }

    const response = await fetch(`/users/${userId}/activate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    // Update the user's status in the local state
    const userIndex = users.value.findIndex(user => user.id === userId);
    if (userIndex !== -1) {
      users.value[userIndex] = {
        ...users.value[userIndex],
        approved: 1,
        active: 1,
      };
    }
  } catch (error) {
    console.error('Error activating user:', error);
  }
};

// Deactivate user
const deactivateUser = async (userId) => {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }

    const response = await fetch(`/users/${userId}/deactivate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const userIndex = users.value.findIndex(user => user.id === userId);
    if (userIndex !== -1) {
      users.value[userIndex] = {
        ...users.value[userIndex],
        approved: 1,
        active: 0,
      };
    }
  } catch (error) {
    console.error('Error deactivating user:', error);
  }
};

onMounted(() => {
  fetchUsers();
});
const currentPage = ref(1); // Current page
const itemsPerPage = ref(5); // Items per page

// Paginated users computed property
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredUsers.value.slice(start, end);
});

// Total pages computed property
const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / itemsPerPage.value);
});
const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};
const isSidebarExpanded = ref(false);

// This function updates the content layout when the sidebar is expanded or collapsed
const handleSidebarExpanded = (expanded) => {
  isSidebarExpanded.value = expanded;
};
</script>

<template>
  <Layout @sidebar-expanded="handleSidebarExpanded"/>
    <div
      :class="{
        'ml-60': isSidebarExpanded,
        'ml-16': !isSidebarExpanded
      }"
      class="flex-1 min-h-screen p-4 transition-all duration-300 bg-gray-100" >

      <div class="bg-white p-6 rounded shadow-md max-w-full mx-auto">
    <!-- Header can be repositioned or removed as needed -->
        <div class="flex flex-wrap justify-between mb-4">
          <div class="flex flex-wrap items-center mb-2 sm:mb-0">
            <input v-model="searchInput" class="mr-2 p-2 border rounded w-full sm:w-auto" placeholder="Search by ID"/>
            <button @click="performSearch" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
              Search
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-100">
              <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Employee ID</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Name</th>
              <th class="px-10 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Program</th>
              <th class="px-10 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">UserType</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in paginatedUsers" :key="user.id">
                <td class="px-6 py-2 whitespace-nowrap">{{ user.employeeid }}</td>
                <td class="px-6 py-2 whitespace-nowrap">
                  <span>{{ user.first_name }} {{ user.middle_name }} {{ user.last_name }} {{ user.suffix }}</span>
                </td>
                <td class="px-6 py-2">
                  <select 
                    v-model="user.program.id" 
                    @change="confirmUserProgramChange(user.id, $event.target.value)"
                    :disabled="user.role === 'admin'"
                    class="block w-full py-1 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                  >
                    <option value="">Select a program</option>
                    <option v-for="program in programs" :key="program.id" :value="program.id">
                      {{ program.name }}  <!-- Display the program name -->
                    </option>
                  </select>
                </td>
                <td class="px-6 py-2">
                  <select 
                    v-model="user.role"
                    @change="confirmUserRoleChange(user.id, $event.target.value)" 
                    class="block w-full py-1 px-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                  >
                    <option value="0">User</option>
                    <option value="1">Admin</option>
                  </select>
                </td>
                <td class="px-6 py-2 whitespace-nowrap">
                  <span v-if="user.approved" class="text-green-600">Approved</span>
                  <span v-else class="text-yellow-600">Pending</span>
                </td>
                <td class="px-2 py-2 whitespace-nowrap items-center">
                  <img v-if="!user.approved" @click="approveUser(user.id)" :src="`/ppd-images/${approveIcon}`" alt="Approve" title="Approve User" class="cursor-pointer w-7 h-7 items-center align-center" />
                  <div v-if="user.approved && !user.active" class="flex justify-center">
                    <img @click="activateUser(user.id)" :src="`/ppd-images/${activateIcon}`" alt="Activate" title="Activate User" class="cursor-pointer w-10 h-10 inline-block" />
                  </div>
                  <div v-if="user.approved && user.active" class="flex justify-center">
                    <img @click="deactivateUser(user.id)" :src="`/ppd-images/${deactivateIcon}`" alt="Deactivate" title="Deactivate User" class="cursor-pointer w-9 h-9 inline-block" />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
     

       <!-- Pagination Controls -->
<div class="mt-4 flex justify-center space-x-4 items-center">
  <button
    @click="prevPage"
    :disabled="currentPage === 1"
    class="arrow-box"
    aria-label="Previous"
  >
    <span>&lt;</span> <!-- Replaced with '<' -->
  </button>

  <span class="text-gray-600 font-medium text-center">
    {{ currentPage }} of {{ totalPages }}
  </span>

  <button
    @click="nextPage"
    :disabled="currentPage === totalPages"
    class="arrow-box"
    aria-label="Next"
  >
    <span>&gt;</span> <!-- Replaced with '>' -->
  </button>
</div>
      </div>
  </div>

  <!-- Modal -->
  <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-md w-96">
      <h2 class="text-xl font-semibold mb-4">
        {{ modalType === 'program' ? 'Confirm Program Change' : 'Confirm User Type Change' }}
      </h2>
      <p v-if="modalType === 'program'">
        Are you sure you want to change the program to <strong>{{ newProgramName }}</strong>? <!-- Display the program name -->
      </p>
      <p v-else>
        Are you sure you want to change the user type to <strong>{{ newUserType }}</strong>?
      </p>
      <div class="mt-6 flex justify-end">
        <button @click="cancelChange" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
          Cancel
        </button>
        <button 
          @click="modalType === 'program' ? updateUserProgram() : updateUserRole()"
          class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded"
        >
          Confirm
        </button>
      </div>
    </div>
  </div>
</template>

<style>
.arrow-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;  /* Adjust width */
  height: 30px; /* Adjust height */
  border: 1px solid #ccc; /* Light gray border */
  border-radius: 0px;  /* Rounded corners */
  background-color: #fff; /* White background */
  font-size: 1rem; /* Smaller font size */
  padding: 0.25rem;  /* Reduced padding */
  cursor: pointer;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.arrow-box:hover {
  background-color: #f1f1f1; /* Slight background color change on hover */
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
}

.arrow-box:disabled {
  background-color: #f9f9f9; /* Lighter background when disabled */
  color: #ccc;  /* Gray out the arrow */
  cursor: not-allowed;
}

.arrow-box span {
  font-size: 1rem;  /* Adjusted font size to match button size */
  color: #333; /* Arrow color */
}

</style>