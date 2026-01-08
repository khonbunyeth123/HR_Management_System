<?php
// --- Start session only if not already started ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default username if session not set
$uname = $_SESSION['uname'] ?? 'User';
?>
<!-- Top Navigation -->
<nav class="bg-gradient-to-b from-slate-900 to-slate-800 sticky top-0 z-50 w-full">
  <div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between w-full h-[56px]">

      <!-- Logo & Brand -->
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-white/10 backdrop-blur-md rounded-lg p-0.5 shadow-md">
          <img src="/assets/img/logo.png" alt="Logo" class="w-full h-full rounded-md object-cover">
        </div>
        <div class="hidden sm:flex flex-col">
          <h1 class="text-white text-sm font-bold leading-tight">Doorstep Technology</h1>
          <span class="text-white/70 text-[10px] font-medium uppercase tracking-wider">Admin Portal</span>
        </div>
      </div>

      <!-- Clock -->
      <div class="flex-1 text-center">
        <h2 id="clock" class="text-white text-xs sm:text-sm font-semibold"></h2>
      </div>

      <!-- User Section -->
      <div class="flex items-center gap-3">
        <!-- User Info -->
        <div class="hidden md:flex items-center gap-2.5">
          <div
            class="w-8 h-8 bg-gradient-to-br from-pink-400 to-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold shadow-md">
            <?php
            $name = htmlspecialchars($_SESSION['uname'] ?? 'User');
            echo strtoupper(substr($name, 0, 1));
            ?>
          </div>
          <span class="text-white text-sm font-semibold">
            <?= htmlspecialchars($_SESSION['uname'] ?? 'User'); ?>
          </span>
        </div>

        <!-- Logout Button -->
        <button id="logoutBtn"
          class="flex items-center gap-2 px-3.5 py-2 bg-white/10 hover:bg-red-500 border border-white/20 hover:border-transparent rounded-lg text-white text-sm font-semibold transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg backdrop-blur-md"
          title="Logout">
          <span class="iconify" data-icon="mdi:logout" data-width="18"></span>
          <span class="hidden sm:inline">Logout</span>
        </button>
      </div>

    </div>
  </div>
</nav>

<!-- Logout Confirmation Modal -->
<div id="logoutModal"
  class="fixed inset-0 flex items-center justify-center bg-black/50 opacity-0 pointer-events-none transition-opacity duration-300 z-50">
  <div id="logoutModalContent"
    class="bg-white rounded-lg shadow-lg p-6 w-80 text-center transform scale-90 transition-all duration-300">
    <h2 class="text-lg font-semibold mb-4">Confirm Logout</h2>
    <p class="mb-6">Are you sure you want to logout?</p>
    <div class="flex justify-center gap-4">
      <button id="cancelLogout" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Cancel</button>
      <button id="confirmLogout" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Logout</button>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    // --- Real-time Clock ---
    function updateClock() {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const year = now.getFullYear();
      document.getElementById('clock').textContent = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // --- Logout Modal ---
    const logoutModal = document.getElementById('logoutModal');
    const logoutModalContent = document.getElementById('logoutModalContent');
    const logoutBtn = document.getElementById('logoutBtn');

    function openLogoutModal() {
      logoutModal.classList.remove('opacity-0', 'pointer-events-none');
      logoutModal.classList.add('opacity-100');
      logoutModalContent.classList.remove('scale-90');
      logoutModalContent.classList.add('scale-100');
    }

    function closeLogoutModal() {
      logoutModal.classList.remove('opacity-100');
      logoutModal.classList.add('opacity-0');
      logoutModalContent.classList.remove('scale-100');
      logoutModalContent.classList.add('scale-90');

      setTimeout(() => {
        logoutModal.classList.add('pointer-events-none');
      }, 300);
    }

    // Open modal when logout button is clicked
    logoutBtn.addEventListener('click', openLogoutModal);

    // Close modal when cancel is clicked
    document.getElementById('cancelLogout').addEventListener('click', closeLogoutModal);

    // Close modal when clicking outside
    logoutModal.addEventListener('click', (e) => {
      if (e.target === logoutModal) {
        closeLogoutModal();
      }
    });

    // Confirm logout
    document.getElementById('confirmLogout').addEventListener('click', () => {
      fetch('/api/auth/logout.php', { method: 'POST', credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
          if (data.success) window.location.href = '/login.php';
        })
        .catch(() => {
          console.log('Logging out...');
          window.location.href = '/login.php';
        });
    });
  });
</script>