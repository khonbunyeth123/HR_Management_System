<?php require_once __DIR__ . '/../../../app/Helpers/PermissionHelper.php'; ?>

<aside id="appSidebar"
  class="fixed left-0 top-[64px] z-40 h-[calc(100vh-64px)] w-72 -translate-x-full bg-white text-slate-600 border-r border-slate-100 transition-transform duration-300 ease-in-out md:static md:translate-x-0 flex flex-col"
  aria-label="Primary navigation">
  
  <div class="flex-1 overflow-y-auto overflow-x-hidden p-6 space-y-8">
    
    <!-- Menu Section -->
    <nav>
      <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 px-2">Main Menu</div>
      <ul class="space-y-1">
        <?php
        $current_page = $_GET['page'] ?? 'dashboard';
        
        $menu_items = [
          ['page'=>'dashboard','label'=>'Dashboard','icon'=>'mdi:view-dashboard-outline'],
          ['page'=>'attendance','label'=>'Attendance','icon'=>'mdi:clock-check-outline'],
          ['page'=>'employee','label'=>'Employees','icon'=>'mdi:account-group-outline'],
          ['page'=>'leave','label'=>'Leave Requests','icon'=>'mdi:calendar-month-outline'],
          ['page'=>'calendar','label'=>'Calendar','icon'=>'mdi:calendar-range-outline'],
        ];

        foreach ($menu_items as $item):
          $is_active = $current_page === $item['page'];
        ?>
        <li>
          <a href="?page=<?= $item['page'] ?>"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 group
                  <?= $is_active 
                      ? 'bg-indigo-50 text-indigo-600 shadow-sm shadow-indigo-100' 
                      : 'hover:bg-slate-50 hover:text-slate-900' ?>">
            <span class="iconify text-xl <?= $is_active ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' ?>" data-icon="<?= $item['icon'] ?>"></span>
            <span><?= $item['label'] ?></span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <!-- Analytics Section -->
    <nav>
      <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 px-2">Analytics</div>
      <ul class="space-y-1">
        <?php
        $is_report_active = strpos($current_page, 'report') === 0;
        ?>
        <li>
          <button type="button" 
            class="nav-toggle flex items-center justify-between w-full px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 group
                  <?= $is_report_active ? 'bg-indigo-50 text-indigo-600' : 'hover:bg-slate-50 hover:text-slate-900' ?>"
            data-menu="reports">
            <div class="flex items-center gap-3">
              <span class="iconify text-xl <?= $is_report_active ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' ?>" data-icon="mdi:chart-bar"></span>
              <span>Reports</span>
            </div>
            <span class="iconify dropdown-arrow text-lg transition-transform duration-300 <?= $is_report_active ? 'rotate-180' : '' ?>" data-icon="mdi:chevron-down"></span>
          </button>
          
          <ul class="submenu space-y-1 mt-1 px-2 <?= $is_report_active ? 'open' : '' ?>" data-submenu="reports">
            <?php
            $report_items = [
              ['page' => 'report/report_daily',   'label' => 'Daily Attendance'],
              ['page' => 'report/report_summary', 'label' => 'Summary Report'],
              ['page' => 'report/report_detail',  'label' => 'Detailed Analysis'],
              ['page' => 'report/report_top_employee', 'label' => 'Top Performers'],
            ];
            foreach ($report_items as $sub):
              $is_sub_active = $current_page === $sub['page'];
            ?>
            <li>
              <a href="?page=<?= $sub['page'] ?>"
                class="flex items-center gap-3 px-4 py-2 rounded-lg text-xs font-bold transition-all duration-200
                      <?= $is_sub_active 
                          ? 'text-indigo-600 bg-indigo-50/50' 
                          : 'text-slate-500 hover:text-indigo-600 hover:bg-slate-50' ?>">
                <span class="w-1.5 h-1.5 rounded-full <?= $is_sub_active ? 'bg-indigo-600' : 'bg-slate-300' ?>"></span>
                <span><?= $sub['label'] ?></span>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </li>
      </ul>
    </nav>

    <!-- System Section -->
    <nav>
      <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 px-2">System</div>
      <ul class="space-y-1">
        <?php
        $system_items = [
          ['page'=>'user','label'=>'Users','icon'=>'mdi:account-cog-outline'],
          ['page'=>'roles','label'=>'Roles','icon'=>'mdi:shield-account-outline'],
          ['page'=>'permissions','label'=>'Permissions','icon'=>'mdi:key-outline'],
        ];
        foreach ($system_items as $item):
          $is_active = $current_page === $item['page'];
        ?>
        <li>
          <a href="?page=<?= $item['page'] ?>"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 group
                  <?= $is_active 
                      ? 'bg-indigo-50 text-indigo-600 shadow-sm shadow-indigo-100' 
                      : 'hover:bg-slate-50 hover:text-slate-900' ?>">
            <span class="iconify text-xl <?= $is_active ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' ?>" data-icon="<?= $item['icon'] ?>"></span>
            <span><?= $item['label'] ?></span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </nav>

  </div>

  <!-- Sidebar Footer -->
  <div class="p-6 border-t border-slate-100">
    <div class="bg-indigo-600 rounded-2xl p-4 relative overflow-hidden group">
      <div class="relative z-10">
        <div class="text-[10px] font-black text-white/60 uppercase tracking-widest mb-1">Status</div>
        <div class="text-xs font-black text-white flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            System Online
        </div>
      </div>
      <span class="iconify absolute -right-2 -bottom-2 text-6xl text-white/10 group-hover:scale-110 transition-transform" data-icon="mdi:shield-check"></span>
    </div>
  </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
  if (window.Iconify) {
    Iconify.scan();
  }

  document.querySelectorAll('.nav-toggle').forEach(toggle => {
    toggle.addEventListener('click', function () {
      const menu = this.dataset.menu;
      const submenu = document.querySelector(`[data-submenu="${menu}"]`);
      const arrow = this.querySelector('.dropdown-arrow');
      if (!submenu) return;

      const isOpen = submenu.classList.contains('open');

      document.querySelectorAll('.submenu').forEach(sm => {
        if (sm !== submenu) {
          sm.classList.remove('open');
          sm.previousElementSibling
            ?.querySelector('.dropdown-arrow')
            ?.classList.remove('rotate-180');
        }
      });

      submenu.classList.toggle('open', !isOpen);
      arrow?.classList.toggle('rotate-180', !isOpen);

      if (window.Iconify) {
        Iconify.scan();
      }
    });
  });
});
</script>

<style>
.submenu { display: none; }
.submenu.open { display: block; }
.navigation a { text-decoration: none; }
.dropdown-arrow { transition: transform 0.3s ease; }
.rotate-180 { transform: rotate(180deg); }
</style>
