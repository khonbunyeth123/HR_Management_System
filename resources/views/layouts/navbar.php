<?php
// --- Start session only if not already started ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default username if session not set
$uname = $_SESSION['uname'] ?? 'User';
?>
<!-- Top Navigation -->
<nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 w-full border-b border-slate-100">
  <div class="w-full px-4">
    <div class="flex items-center justify-between w-full h-[50px]">

      <!-- Logo & Brand -->
      <div class="flex items-center gap-2 min-w-0">
        <button id="sidebarToggle" type="button"
          class="md:hidden inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-600 border border-slate-100 transition-all hover:bg-slate-100"
          aria-label="Open menu" aria-controls="appSidebar" aria-expanded="false">
          <span class="iconify text-lg" data-icon="mdi:menu"></span>
        </button>
        <div class="w-8 h-8 bg-indigo-600 rounded-lg p-0.5 shadow-md shadow-indigo-100">
          <img src="/assets/img/logo.png" alt="Logo" class="w-full h-full rounded-[6px] object-cover border border-white/20">
        </div>
        <div class="hidden sm:flex flex-col">
          <h1 class="text-slate-900 text-xs font-black leading-tight tracking-tight">Doorstep</h1>
          <span class="text-slate-400 text-[8px] font-bold uppercase tracking-[0.2em]">Admin</span>
        </div>
      </div>

      <!-- Clock -->
      <div class="hidden min-[420px]:block flex-1 text-center">
        <h2 id="clock" class="text-slate-900 text-[10px] font-black tabular-nums tracking-wider opacity-80"></h2>
      </div>

      <!-- User Section -->
      <div class="flex items-center gap-2">
        <!-- User Info -->
        <div class="hidden md:flex items-center gap-2 pl-2 border-l border-slate-100">
          <div
            class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center text-white text-xs font-black shadow-md shadow-indigo-100">
            <?php
            $name = htmlspecialchars($_SESSION['uname'] ?? 'User');
            echo strtoupper(substr($name, 0, 1));
            ?>
          </div>
          <div class="flex flex-col">
            <span class="text-slate-900 text-[10px] font-black leading-none mb-0.5">
                <?= htmlspecialchars($_SESSION['uname'] ?? 'User'); ?>
            </span>
            <span class="text-slate-400 text-[9px] font-bold">Admin</span>
          </div>
        </div>

        <!-- Logout Button -->
        <button id="logoutBtn"
          class="flex items-center justify-center h-8 w-8 md:w-auto md:px-3 bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white rounded-lg text-[10px] font-black transition-all duration-300 border border-rose-100 group"
          title="Logout">
          <span class="iconify text-lg md:mr-1 group-hover:rotate-12 transition-transform" data-icon="mdi:logout-variant"></span>
          <span class="hidden md:inline">Exit</span>
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
