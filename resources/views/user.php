<div class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
  <div class="p-2">
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
      <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <iconify-icon icon="mdi:users-group" style="font-size: 24px; color: #4f46e5;"></iconify-icon>
            <h1 class="text-lg font-bold text-gray-900">User Directory</h1>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full" id="totalCount">0 Users</span>
            <button onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors">
              <iconify-icon icon="mdi:plus-circle"></iconify-icon>
              Add User
            </button>
          </div>
        </div>

        <!-- Search & Filters -->
        <div class="flex flex-col sm:flex-row gap-2">
          <div class="flex-1 relative">
            <iconify-icon icon="mdi:magnify" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px;"></iconify-icon>
            <input type="text" id="searchInput" placeholder="Search by name or username..." class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">Name</th>
              <th class="px-4 py-3 text-left font-semibold">Username</th>
              <th class="px-4 py-3 text-left font-semibold">Email</th>
              <th class="px-4 py-3 text-left font-semibold">Role</th>
              <th class="px-4 py-3 text-left font-semibold">Created At</th>
              <th class="px-4 py-3 text-center font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody id="userTableBody" class="divide-y divide-gray-100">
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                <div class="flex items-center justify-center gap-2">
                  <iconify-icon icon="mdi:loading" style="font-size: 20px;" class="animate-spin"></iconify-icon>
                  Loading...
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div id="paginationContainer"></div>
    </div>
  </div>
</div>

<!-- Add User Modal -->
<div id="createUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
    <div class="sticky top-0 bg-gradient-to-r from-slate-900 to-slate-800 text-white px-6 py-4 flex items-center justify-between border-b">
      <div class="flex items-center gap-3">
        <iconify-icon icon="mdi:account-plus" style="font-size: 24px;"></iconify-icon>
        <h2 class="text-lg font-bold">Add New User</h2>
      </div>
      <button onclick="closeCreateModal()" class="text-gray-300 hover:text-white transition-colors p-1">
        <iconify-icon icon="mdi:close" style="font-size: 20px;"></iconify-icon>
      </button>
    </div>
    <div class="p-6">
      <form id="createUserForm" onsubmit="submitCreateUser(event)">
        <!-- Full Name & Username -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label for="fullName" class="block text-sm font-semibold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>       
            <input type="text" id="fullName" name="full_name" required placeholder="John Doe" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>
          <div>
            <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>        
            <div class="relative">
              <span class="absolute left-3 top-2.5 text-gray-400 text-sm">@</span>
              <input type="text" id="username" name="username" required placeholder="johndoe" class="w-full pl-7 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
          </div>
        </div>

        <!-- Email & Password -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>      
            <input type="email" id="email" name="email" required placeholder="john@example.com" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>
          <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>        
            <input type="password" id="password" name="password" required placeholder="Enter a strong password" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          </div>
        </div>

        <!-- Role & Status -->
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div>
            <label for="role_id" class="block text-sm font-semibold text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
            <select id="role_id" name="role_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
              <option value="">Loading roles...</option>
            </select>
          </div>
          <div>
            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
            <select id="status" name="status_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
              <option value="1" selected>Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
          <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-2 border border-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50">Cancel</button>
          <button type="submit" id="submitBtn" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold flex items-center justify-center gap-2">
            <iconify-icon icon="mdi:check-circle" id="submitIcon"></iconify-icon>
            <span id="submitText">Add User</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
    <div class="sticky top-0 bg-gradient-to-r from-blue-900 to-blue-800 text-white px-6 py-4 flex items-center justify-between border-b">
      <div class="flex items-center gap-3">
        <iconify-icon icon="mdi:account-edit" style="font-size: 24px;"></iconify-icon>
        <h2 class="text-lg font-bold">Edit User</h2>
      </div>
      <button onclick="closeEditModal()" class="text-gray-300 hover:text-white transition-colors p-1">
        <iconify-icon icon="mdi:close" style="font-size: 20px;"></iconify-icon>
      </button>
    </div>
    <div class="p-6">
      <form id="editUserForm" onsubmit="submitEditUser(event)">
        <input type="hidden" id="editUserId" name="user_id">
        
        <!-- Full Name & Username (read-only) -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label for="editFullName" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>       
            <input type="text" id="editFullName" name="full_name" placeholder="John Doe" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label for="editUsername" class="block text-sm font-semibold text-gray-700 mb-2">Username</label>        
            <div class="relative">
              <span class="absolute left-3 top-2.5 text-gray-400 text-sm">@</span>
              <input type="text" id="editUsername" name="username" placeholder="johndoe" disabled class="w-full pl-7 pr-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-100 cursor-not-allowed">
            </div>
          </div>
        </div>

        <!-- Email & Password -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label for="editEmail" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>      
            <input type="email" id="editEmail" name="email" placeholder="john@example.com" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label for="editPassword" class="block text-sm font-semibold text-gray-700 mb-2">Password <span class="text-xs text-gray-500">(Optional)</span></label>        
            <input type="password" id="editPassword" name="password" placeholder="Leave blank to keep current" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- Role & Status -->
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div>
            <label for="editRole" class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
            <select id="editRole" name="role_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">
              <option value="">Loading roles...</option>
            </select>
          </div>
          <div>
            <label for="editStatus" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
            <select id="editStatus" name="status_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white cursor-pointer">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
          <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 border border-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50">Cancel</button>
          <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold flex items-center justify-center gap-2">
            <iconify-icon icon="mdi:check-circle"></iconify-icon>
            <span>Update User</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-sm mx-4">
    <div class="bg-gradient-to-r from-red-900 to-red-800 text-white px-6 py-4 flex items-center justify-between border-b">
      <div class="flex items-center gap-3">
        <iconify-icon icon="mdi:alert-circle" style="font-size: 24px;"></iconify-icon>
        <h2 class="text-lg font-bold">Delete User</h2>
      </div>
    </div>
    <div class="p-6">
      <p class="text-gray-700 mb-2">Are you sure you want to delete this user?</p>
      <p class="text-red-600 font-semibold mb-6"><span id="deleteUserName"></span></p>
      <p class="text-sm text-gray-600 mb-6">This action cannot be undone.</p>
      
      <input type="hidden" id="deleteUserId">
      
      <div class="flex gap-3">
        <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2 border border-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50">Cancel</button>
        <button type="button" onclick="submitDeleteUser()" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold flex items-center justify-center gap-2">
          <iconify-icon icon="mdi:delete"></iconify-icon>
          <span>Delete</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let currentPage = 1;
let totalPages = 1;
let allUsers = [];
const perPage = 18;

// ✅ NEW: Improved date formatting function
function formatDate(dateString) {
    // Check if dateString exists and is not null/undefined/empty
    if (!dateString || dateString === null || dateString === undefined || dateString.trim() === '') {
        return '<span class="text-gray-400 italic">N/A</span>';
    }
    
    try {
        const date = new Date(dateString);
        
        // Check if date is valid
        if (isNaN(date.getTime())) {
            console.warn('Invalid date:', dateString);
            return '<span class="text-red-400">Invalid Date</span>';
        }
        
        return date.toLocaleDateString(undefined, { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    } catch (error) {
        console.error('Date parsing error:', error, dateString);
        return '<span class="text-red-400">Error</span>';
    }
}

// Load roles from API
function loadRoles() {
    fetch("/api/roles")
        .then(res => res.json())
        .then(result => {
            console.log("ROLE API:", result);

            const select = document.getElementById("role_id");

            if(result.success){
                const roles = result.data.data; // ✅ FIX HERE

                select.innerHTML = '<option value="">Select Role</option>';

                roles.forEach(role => {
                    select.innerHTML += `<option value="${role.id}">${role.name}</option>`;
                });

            } else {
                select.innerHTML = '<option value="">No roles found</option>';
            }
        })
        .catch(err => console.error(err));
}

// ✅ NEW: Load roles for edit modal
function loadEditRoles(currentRoleId) {
    fetch("/api/roles")
        .then(res => res.json())
        .then(result => {
            console.log("EDIT ROLE API:", result);

            const select = document.getElementById("editRole");

            if(result.success){
                const roles = result.data.data; // ✅ FIX HERE

                select.innerHTML = '<option value="">Select Role</option>';

                roles.forEach(role => {
                    const selected = role.id == currentRoleId ? 'selected' : '';
                    select.innerHTML += `<option value="${role.id}" ${selected}>${role.name}</option>`;
                });

            } else {
                select.innerHTML = '<option value="">No roles found</option>';
            }
        })
        .catch(err => console.error(err));
}

// Modal
function openCreateModal(){ document.getElementById("createUserModal").classList.remove("hidden"); document.getElementById("createUserForm").reset(); }
function closeCreateModal(){ document.getElementById("createUserModal").classList.add("hidden"); document.getElementById("createUserForm").reset(); }

// Submit
function submitCreateUser(event){
    event.preventDefault();
    const fullName = document.getElementById("fullName").value.trim();
    const username = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const role_id = document.getElementById("role_id").value;
    const status = document.getElementById("status").value;

    if(!fullName || !username || !email || !password || !role_id){
        alert("Please fill all required fields");
        return;
    }

    const urlParams = new URLSearchParams();
    urlParams.append("full_name", fullName);
    urlParams.append("username", username);
    urlParams.append("email", email);
    urlParams.append("password", password);
    urlParams.append("role_id", role_id);
    urlParams.append("status_id", status);

    fetch("/api/users/create",{
        method:"POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: urlParams.toString()
    })
        .then(res => res.json())
        .then(result => {
            console.log("Response:", result);
            if(result.success){
                alert("User created successfully!");
                closeCreateModal();
                loadUsers(1);
            }
            else {
                alert(result.message || JSON.stringify(result));
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("Error: " + err);
        });
}

// Load users
function loadUsers(page){
    fetch(`/api/users/show?page=${page}&per_page=${perPage}`)
        .then(res => res.json())
        .then(result => {
            if(result.success && result.data?.users){
                allUsers = result.data.users;
                console.log("Loaded users:", allUsers); // ✅ DEBUG
                
                // ✅ DEBUG: Check first user's created_at field
                if(allUsers.length > 0) {
                    console.log("First user created_at:", allUsers[0].created_at);
                    console.log("All fields in first user:", Object.keys(allUsers[0]));
                }
                
                applyFilters();
                document.getElementById("totalCount").textContent = `${result.pagination.total} Users`;
            } else {
                showError("No users found");
            }
        }).catch(err => {
            console.error("Fetch error:", err);
            showError("Error loading users");
        });
}

// Filter & Table
function applyFilters(){
    const search = document.getElementById("searchInput").value.toLowerCase();
    const filtered = allUsers.filter(u => u.full_name.toLowerCase().includes(search) || u.username.toLowerCase().includes(search));
    renderTable(filtered);
}

// ✅ UPDATED: Using new formatDate function
function renderTable(users){
    const tbody = document.getElementById("userTableBody");
    if(!users.length){ 
        tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No users found</td></tr>'; 
        return; 
    }
    
    tbody.innerHTML = users.map(user => {
        const dateHTML = formatDate(user.created_at);
        return `
            <tr class="hover:bg-indigo-50">
                <td class="px-4 py-3 font-medium">${user.full_name}</td>
                <td class="px-4 py-3 text-xs font-mono">@${user.username}</td>
                <td class="px-4 py-3">${user.email}</td>
                <td class="px-4 py-3"><span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">${user.role_name}</span></td>      
                <td class="px-4 py-3 text-xs text-gray-600">
                    ${dateHTML}
                </td>
                <td class="px-4 py-3 flex gap-2 justify-center">
                    <button onclick="openEditModal(${user.id})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-semibold flex items-center gap-1 transition-colors">
                        <iconify-icon icon="mdi:pencil" style="font-size: 14px;"></iconify-icon>
                        Edit
                    </button>
                    <button onclick="openDeleteModal(${user.id}, '${user.full_name}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold flex items-center gap-1 transition-colors">
                        <iconify-icon icon="mdi:delete" style="font-size: 14px;"></iconify-icon>
                        Delete
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function showError(message){ 
    document.getElementById("userTableBody").innerHTML = `<tr><td colspan="5" class="px-4 py-6 text-center text-red-500">${message}</td></tr>`; 
}

// Event listeners
document.getElementById("searchInput").addEventListener("input", applyFilters);
document.addEventListener("keydown", e => { if(e.key === "Escape") closeCreateModal(); });
document.getElementById("createUserModal").addEventListener("click", e => { if(e.target === this) closeCreateModal(); });

// ✅ NEW: Edit Modal Functions
function openEditModal(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) {
        alert("User not found");
        return;
    }
    
    document.getElementById("editUserId").value = user.id;
    document.getElementById("editFullName").value = user.full_name;
    document.getElementById("editUsername").value = user.username;
    document.getElementById("editEmail").value = user.email;
    document.getElementById("editRole").value = user.role_id;
    document.getElementById("editStatus").value = user.status_id;
    
    // ✅ NEW: Load roles into edit dropdown
    loadEditRoles(user.role_id);
    
    document.getElementById("editUserModal").classList.remove("hidden");
}

function closeEditModal() {
    document.getElementById("editUserModal").classList.add("hidden");
    document.getElementById("editUserForm").reset();
}

function submitEditUser(event) {
    event.preventDefault();
    
    const userId = document.getElementById("editUserId").value;
    const fullName = document.getElementById("editFullName").value.trim();
    const email = document.getElementById("editEmail").value.trim();
    const password = document.getElementById("editPassword").value.trim();
    const roleId = document.getElementById("editRole").value;
    const status = document.getElementById("editStatus").value;
    
    if(!fullName || !email || !roleId) {
        alert("Please fill all required fields");
        return;
    }
    
    const urlParams = new URLSearchParams();
    urlParams.append("id", userId);
    urlParams.append("full_name", fullName);
    urlParams.append("email", email);
    urlParams.append("role_id", roleId);
    urlParams.append("status_id", status);
    
    // ✅ NEW: Only send password if it's not empty
    if(password) {
        urlParams.append("password", password);
    }
    
    fetch("/api/users/update", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: urlParams.toString()
    })
    .then(res => res.json())
    .then(result => {
        console.log("Update Response:", result);
        if(result.success) {
            alert("User updated successfully!");
            closeEditModal();
            loadUsers(1);
        } else {
            alert(result.message || "Failed to update user");
        }
    })
    .catch(err => {
        console.error("Error:", err);
        alert("Error: " + err);
    });
}

// ✅ NEW: Delete Modal Functions
function openDeleteModal(userId, userName) {
    document.getElementById("deleteUserId").value = userId;
    document.getElementById("deleteUserName").textContent = userName;
    document.getElementById("deleteUserModal").classList.remove("hidden");
}

function closeDeleteModal() {
    document.getElementById("deleteUserModal").classList.add("hidden");
}

function submitDeleteUser() {
    const userId = document.getElementById("deleteUserId").value;

    if(!userId) {
        alert("User ID not found");
        return;
    }

    const urlParams = new URLSearchParams();
    urlParams.append("id", userId);

    fetch("/api/users/delete", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: urlParams.toString()
    })
    .then(res => res.json())
    .then(result => {
        console.log("Delete Response:", result);
        if(result.success) {
            alert("User deleted successfully!");
            closeDeleteModal();
            loadUsers(1);
        } else {
            alert(result.message || "Failed to delete user");
        }
    })
    .catch(err => {
        console.error("Error:", err);
        alert("Error: " + err);
    });
}

// Init
loadRoles();
loadUsers(1);
</script>