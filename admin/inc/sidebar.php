<div
  class="navigation max-h-[calc(100vh-56px)] bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white h-screen flex flex-col shadow-2xl border-r border-slate-700/50">

  <!-- Menu Section -->
  <div class="menu flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
    <ul class="p-3 space-y-1.5">
      <?php
      if (session_status() === PHP_SESSION_NONE) session_start();
      $current_page = isset($_GET['page']) ? strtolower($_GET['page']) : 'dashboard';

      $menu_items = [
        ['page' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'mdi:view-dashboard', 'mid' => 1],
        ['page' => 'attendance', 'label' => 'Attendance', 'icon' => 'mdi:clock-check-outline', 'mid' => 2],
        ['page' => 'employee', 'label' => 'Employees', 'icon' => 'mdi:account-group', 'mid' => 3],
        ['page' => 'leave', 'label' => 'Leave Requests', 'icon' => 'mdi:calendar-month', 'mid' => 4],
        [
          'page' => 'report',
          'label' => 'Reports',
          'icon' => 'mdi:chart-box',
          'mid' => 5,
          'submenu' => [
            ['page' => 'report_daily', 'label' => 'Daily Report', 'icon' => 'mdi:calendar-today', 'mid' => 51],
            ['page' => 'report_summary', 'label' => 'Summary Report', 'icon' => 'mdi:chart-line', 'mid' => 52],
            ['page' => 'report_detail', 'label' => 'Detailed Report', 'icon' => 'mdi:file-document-outline', 'mid' => 53],
            ['page' => 'report_top_employee', 'label' => 'Top Employees', 'icon' => 'mdi:star-circle', 'mid' => 54]
          ]
        ],
        ['page' => 'user', 'label' => 'User Management', 'icon' => 'mdi:shield-account', 'mid' => 6],
        ['page' => 'audits', 'label' => 'Audits', 'icon' => 'mdi:cellphone-link', 'mid' => 7]
      ];

      function render_menu($menu_items, $current_page, $conn = null) {
          foreach ($menu_items as $index => $item) {
              $is_active = strtolower($current_page) === strtolower($item['page']);
              $has_active_submenu = false;

              if (isset($item['submenu'])) {
                  foreach ($item['submenu'] as $subitem) {
                      if (strtolower($current_page) === strtolower($subitem['page'])) {
                          $has_active_submenu = true;
                          break;
                      }
                  }
              }

              $is_parent_active = $is_active || $has_active_submenu;
              $active_class = $is_parent_active
                ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/50'
                : 'hover:bg-slate-700/50 text-slate-300 hover:text-white';

              echo '<li data-opt="' . $index . '" class="rounded-xl ' . $active_class . ' transition-all duration-300 transform hover:scale-[1.02]">';

              if (isset($item['submenu'])) {
                  echo '
                    <button type="button" class="nav-toggle w-full flex items-center px-4 py-3 gap-3 group" data-menu="' . $item['page'] . '">
                      <span class="iconify text-xl ' . ($is_parent_active ? 'text-white' : 'text-slate-400 group-hover:text-white') . '" data-icon="' . $item['icon'] . '"></span>
                      <span class="text-sm font-semibold tracking-wide">' . $item['label'] . '</span>
                      <span class="iconify ml-auto text-lg transition-transform duration-300 dropdown-arrow ' . ($has_active_submenu ? 'rotate-180' : '') . '" data-icon="mdi:chevron-down"></span>
                    </button>
                    <ul class="submenu pl-8 pr-4 pb-2 pt-1 space-y-1 ' . ($has_active_submenu ? 'active' : '') . '" data-submenu="' . $item['page'] . '">';

                  foreach ($item['submenu'] as $subitem) {
                      $is_sub_active = strtolower($current_page) === strtolower($subitem['page']);
                      $sub_active_class = $is_sub_active
                        ? 'bg-slate-700/70 text-white font-semibold border-l-2 border-indigo-400'
                        : 'text-slate-400 hover:text-white hover:bg-slate-700/30';
                      $sub_icon = $subitem['icon'] ?? 'mdi:circle-small';

                      echo '<li>
                        <a href="?page=' . strtolower($subitem['page']) . '" data-page="' . strtolower($subitem['page']) . '" class="nav-link flex items-center gap-2 py-2.5 px-3 rounded-lg text-sm ' . $sub_active_class . '">
                          <span class="iconify text-base ' . ($is_sub_active ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300') . '" data-icon="' . $sub_icon . '"></span>
                          <span>' . $subitem['label'] . '</span>
                          ' . ($is_sub_active ? '<span class="ml-auto w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse"></span>' : '') . '
                        </a>
                      </li>';
                  }

                  echo '</ul>';
              } else {
                  echo '<a href="?page=' . strtolower($item['page']) . '" data-page="' . strtolower($item['page']) . '" class="nav-link flex items-center px-4 py-3 gap-3 group">
                          <span class="iconify text-xl ' . ($is_active ? 'text-white' : 'text-slate-400 group-hover:text-white') . '" data-icon="' . $item['icon'] . '"></span>
                          <span class="text-sm font-semibold tracking-wide">' . $item['label'] . '</span>
                          ' . ($is_active ? '<span class="ml-auto w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>' : '') . '
                        </a>';
              }

              echo '</li>';
          }
      }

      render_menu($menu_items, $current_page);
      ?>
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
document.addEventListener('DOMContentLoaded', function () {
    const currentPageParam = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    let currentPage = currentPageParam;

    // Toggle submenus
    document.querySelectorAll('.nav-toggle').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const menuName = btn.dataset.menu;
            const submenu = document.querySelector(`[data-submenu="${menuName}"]`);
            const arrow = btn.querySelector('.dropdown-arrow');
            const isActive = submenu.classList.contains('active');

            document.querySelectorAll('.submenu').forEach(sub => {
                if (sub !== submenu) {
                    sub.classList.remove('active');
                    const otherArrow = sub.previousElementSibling?.querySelector('.dropdown-arrow');
                    if (otherArrow) otherArrow.classList.remove('rotate-180');
                }
            });

            submenu.classList.toggle('active', !isActive);
            if (arrow) arrow.classList.toggle('rotate-180', !isActive);
        });
    });

    // Auto-expand and mark active submenu items on page load
    document.querySelectorAll('.submenu').forEach(sub => {
        let hasActive = false;
        sub.querySelectorAll('.nav-link').forEach(link => {
            if (link.dataset.page === currentPage) {
                hasActive = true;

                link.classList.add('bg-slate-700/70', 'text-white', 'font-semibold', 'border-l-2', 'border-indigo-400');
                const pulseSpan = document.createElement('span');
                pulseSpan.className = 'ml-auto w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse';
                link.appendChild(pulseSpan);

                const icon = link.querySelector('.iconify');
                if (icon) {
                    icon.classList.remove('text-slate-500', 'group-hover:text-slate-300');
                    icon.classList.add('text-indigo-400');
                }
            }
        });

        if (hasActive) {
            sub.classList.add('active');
            const arrow = sub.previousElementSibling?.querySelector('.dropdown-arrow');
            if (arrow) arrow.classList.add('rotate-180');

            const parentLi = sub.closest('li[data-opt]');
            if (parentLi) {
                parentLi.classList.add('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white', 'shadow-lg', 'shadow-indigo-500/50');
                parentLi.classList.remove('hover:bg-slate-700/50', 'text-slate-300', 'hover:text-white');
            }
        }
    });

    // Nav link click
    document.addEventListener('click', e => {
        if (e.target.closest('.nav-toggle')) return;

        const link = e.target.closest('.nav-link');
        if (!link) return;
        e.preventDefault();

        const page = link.dataset.page;
        if (!page || page === currentPage) return;
        currentPage = page;

        // Remove previous active
        document.querySelectorAll('.nav-link').forEach(l => {
            l.classList.remove('bg-slate-700/70', 'text-white', 'font-semibold', 'border-l-2', 'border-indigo-400');
            l.classList.add('text-slate-400', 'hover:text-white', 'hover:bg-slate-700/30');
            const pulse = l.querySelector('.animate-pulse');
            if (pulse) pulse.remove();
        });

        link.classList.add('bg-slate-700/70', 'text-white', 'font-semibold', 'border-l-2', 'border-indigo-400');
        const pulseSpan = document.createElement('span');
        pulseSpan.className = 'ml-auto w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse';
        link.appendChild(pulseSpan);

        window.history.pushState({ page }, '', '?page=' + page);

        const contentArea = document.getElementById('content-area');
        if (contentArea) {
            fetch('pages/' + page + '.php')
            .then(res => res.ok ? res.text() : Promise.reject('Page not found'))
            .then(html => contentArea.innerHTML = html)
            .catch(err => contentArea.innerHTML = '<p class="p-4 text-red-500">Error loading ' + page + '</p>');
        }
    });

    window.addEventListener('popstate', () => location.reload());
});
</script>

<style>
.scrollbar-thin::-webkit-scrollbar { width: 6px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #475569; border-radius:3px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background:#64748b; }

.dropdown-arrow { transition: transform 0.3s ease; }
.dropdown-arrow.rotate-180 { transform: rotate(180deg); }

.submenu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
.submenu.active { max-height: 500px; }

.nav-toggle { user-select: none; -webkit-user-select: none; -moz-user-select: none; }
</style>
