<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current_page = isset($_GET['page']) ? strtolower($_GET['page']) : 'dashboard';
?>

<div
  class="navigation max-h-[calc(100vh-56px)] bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white h-screen flex flex-col shadow-2xl border-r border-slate-700/50">

  <!-- Menu Section -->
  <div class="menu flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
    <ul class="p-3 space-y-1.5">

<?php
$menu_items = [
  ['page'=>'dashboard','label'=>'Dashboard','icon'=>'mdi:view-dashboard','mid'=>1],
  ['page'=>'attendance','label'=>'Attendance','icon'=>'mdi:clock-check-outline','mid'=>2],
  ['page'=>'employee','label'=>'Employees','icon'=>'mdi:account-group','mid'=>3],
  ['page'=>'leave','label'=>'Leave Requests','icon'=>'mdi:calendar-month','mid'=>4],
  [
    'page'=>'report','label'=>'Reports','icon'=>'mdi:chart-box','mid'=>5,
    'submenu'=>[
      ['page'=>'report_daily','label'=>'Daily Report','icon'=>'mdi:calendar-today','mid'=>51],
      ['page'=>'report_summary','label'=>'Summary Report','icon'=>'mdi:chart-line','mid'=>52],
      ['page'=>'report_detail','label'=>'Detailed Report','icon'=>'mdi:file-document-outline','mid'=>53],
      ['page'=>'report_top_employee','label'=>'Top Employees','icon'=>'mdi:star-circle','mid'=>54],
    ]
  ],
  ['page'=>'user','label'=>'User Management','icon'=>'mdi:shield-account','mid'=>6],
  ['page'=>'audits','label'=>'Audits','icon'=>'mdi:cellphone-link','mid'=>7],
];

// Admin sees all
foreach ($menu_items as $index => $item):
  $is_active = $current_page === $item['page'];
  $has_active_sub = false;

  if (isset($item['submenu'])) {
    foreach ($item['submenu'] as $s) {
      if ($current_page === $s['page']) $has_active_sub = true;
    }
  }

  $is_parent_active = $is_active || $has_active_sub;
?>

<li data-opt="<?= $index ?>" class="rounded-xl transition-all duration-300 <?= $is_parent_active ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/50' : 'hover:bg-slate-700/50 text-slate-300 hover:text-white' ?>">

<?php if (isset($item['submenu'])): ?>
  <button type="button" class="nav-toggle w-full flex items-center px-4 py-3 gap-3 group" data-menu="<?= $item['page'] ?>">
    <span class="iconify text-xl <?= $is_parent_active?'text-white':'text-slate-400 group-hover:text-white' ?>" data-icon="<?= $item['icon'] ?>"></span>
    <span class="text-sm font-semibold tracking-wide"><?= $item['label'] ?></span>
    <span class="iconify ml-auto text-lg dropdown-arrow <?= $has_active_sub?'rotate-180':'' ?>" data-icon="mdi:chevron-down"></span>
  </button>

  <ul class="submenu <?= $has_active_sub?'':'hidden' ?> pl-8 pr-4 pb-2 pt-1 space-y-1" data-submenu="<?= $item['page'] ?>" data-parent-li="<?= $index ?>">
    <?php foreach ($item['submenu'] as $sub):
      $sub_active = $current_page === $sub['page'];
    ?>
    <li>
      <a href="?page=<?= $sub['page'] ?>" data-page="<?= $sub['page'] ?>" class="nav-link flex items-center gap-2 py-2.5 px-3 rounded-lg text-sm <?= $sub_active?'bg-slate-700/70 text-white font-semibold border-l-2 border-indigo-400':'text-slate-400 hover:text-white hover:bg-slate-700/30' ?>">
        <span class="iconify text-base <?= $sub_active?'text-indigo-400':'text-slate-500' ?>" data-icon="<?= $sub['icon'] ?>"></span>
        <span><?= $sub['label'] ?></span>
        <?php if ($sub_active): ?>
          <span class="ml-auto w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse"></span>
        <?php endif; ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>

<?php else: ?>
  <a href="?page=<?= $item['page'] ?>" data-page="<?= $item['page'] ?>" class="nav-link flex items-center px-4 py-3 gap-3 group">
    <span class="iconify text-xl <?= $is_active?'text-white':'text-slate-400 group-hover:text-white' ?>" data-icon="<?= $item['icon'] ?>"></span>
    <span class="text-sm font-semibold tracking-wide"><?= $item['label'] ?></span>
    <?php if ($is_active): ?>
      <span class="ml-auto w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
    <?php endif; ?>
  </a>
<?php endif; ?>
</li>

<?php endforeach; ?>

    </ul>
  </div>

  <!-- Footer Section -->
  <div class="p-4 border-t border-slate-700/50 backdrop-blur-sm bg-slate-800/50">
    <div class="flex items-center gap-3 text-slate-400 text-xs">
      <span class="iconify text-lg" data-icon="mdi:copyright"></span>
      <div class="flex flex-col">
        <span class="font-semibold text-slate-300"><?= date('Y') ?> Doorstep Technology</span>
        <span class="text-[10px] text-slate-500">All rights reserved</span>
      </div>
    </div>
  </div>
</div>

<script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    let currentPage = new URLSearchParams(location.search).get('page') || 'dashboard';

    /* Dropdown toggle for top-level buttons */
    document.querySelectorAll('.nav-toggle').forEach(btn => {
      btn.addEventListener('click', () => {
        const menu = btn.dataset.menu;
        const submenu = document.querySelector(`[data-submenu="${menu}"]`);
        const arrow = btn.querySelector('.dropdown-arrow');
        submenu.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
      });
    });

    /* Nav link click SPA */
    document.addEventListener('click', e => {
      const link = e.target.closest('.nav-link');
      if (!link) return;

      e.preventDefault();
      currentPage = link.dataset.page;
      updateActiveState(link);
      history.pushState({}, '', '?page=' + currentPage);
    });

    /* Back/Forward button handling */
    window.addEventListener('popstate', () => {
      const page = new URLSearchParams(location.search).get('page') || 'dashboard';
      currentPage = page;
      const link = document.querySelector(`.nav-link[data-page="${page}"]`);
      if (link) updateActiveState(link);
    });

    /* Set initial active states */
    const initialLink = document.querySelector(`.nav-link[data-page="${currentPage}"]`);
    if (initialLink) updateActiveState(initialLink);

    /* ================= FUNCTION ================= */
    function updateActiveState(clickedLink) {
      // Reset top-level
      document.querySelectorAll('li[data-opt]').forEach(li => {
        li.classList.remove('bg-gradient-to-r','from-indigo-500','to-purple-600','text-white','shadow-lg','shadow-indigo-500/50');
        li.classList.add('hover:bg-slate-700/50','text-slate-300','hover:text-white');
      });
      // Reset submenus
      document.querySelectorAll('.submenu .nav-link').forEach(l => {
        l.classList.remove('bg-slate-700/70','text-white','font-semibold','border-l-2','border-indigo-400');
        l.querySelector('.animate-pulse')?.remove();
      });

      const submenu = clickedLink.closest('.submenu');
      if (submenu) {
        const parentIndex = submenu.dataset.parentLi;
        const parentLi = document.querySelector(`li[data-opt="${parentIndex}"]`);

        parentLi.classList.add('bg-gradient-to-r','from-indigo-500','to-purple-600','text-white','shadow-lg','shadow-indigo-500/50');
        submenu.classList.remove('hidden');

        const arrow = parentLi.querySelector('.dropdown-arrow');
        if (arrow) arrow.classList.add('rotate-180');

        clickedLink.classList.add('bg-slate-700/70','text-white','font-semibold','border-l-2','border-indigo-400');
        clickedLink.insertAdjacentHTML('beforeend','<span class="ml-auto w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse"></span>');
        return;
      }

      // Top-level item
      const parentLi = clickedLink.closest('li[data-opt]');
      parentLi.classList.add('bg-gradient-to-r','from-indigo-500','to-purple-600','text-white','shadow-lg','shadow-indigo-500/50');
      clickedLink.insertAdjacentHTML('beforeend','<span class="ml-auto w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>');

      // Toggle submenu if exists
      const sub = parentLi.querySelector('.submenu');
      if (sub) {
        sub.classList.toggle('hidden');
        const arrow = parentLi.querySelector('.dropdown-arrow');
        if (arrow) arrow.classList.toggle('rotate-180');
      }
    }
  });
</script>

<style>
.scrollbar-thin::-webkit-scrollbar { width: 6px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #475569; border-radius: 3px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #64748b; }

.dropdown-arrow { transition: transform 0.3s ease; }
.dropdown-arrow.rotate-180 { transform: rotate(180deg); }

.submenu { overflow: hidden; transition: all 0.3s ease; }
</style>
