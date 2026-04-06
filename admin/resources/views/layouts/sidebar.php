<div class="navigation bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white flex flex-col shadow-2xl border-r border-slate-700/50">
  <div class="menu flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
    <ul class="p-3">

      <?php
        $current_page = isset($_GET['page']) ? strtolower($_GET['page']) : 'dashboard';
        require_once __DIR__ . '/../../../app/Helpers/PermissionHelper.php';
        use App\Helpers\PermissionHelper;

        $pagePermissionMap = [
          'dashboard' => ['dashboard', 'view'],
          'attendance' => ['attendance', 'view'],
          'employee' => ['employee', 'view'],
          'leave' => ['leave', 'view'],
          'audits' => ['audits', 'view'],
          'report' => ['report', 'view'],
          'report/report_daily' => ['report', 'view_daily'],
          'report/report_summary' => ['report', 'view_summary'],
          'report/report_detail' => ['report', 'view_detail'],
          'report/report_top_employee' => ['report', 'view_top'],
          'user' => ['user', 'view'],
          'roles' => ['roles', 'view'],
          'permissions' => ['permissions', 'view'],
        ];

        $canAccess = function (string $page) use ($pagePermissionMap): bool {
          if (!isset($pagePermissionMap[$page])) {
            return true;
          }
          [$mod, $act] = $pagePermissionMap[$page];
          return PermissionHelper::can($mod, $act);
        };

$menu_items = [

  ['page'=>'dashboard','label'=>'Dashboard','icon'=>'mdi:view-dashboard'],

  ['page'=>'attendance','label'=>'Attendance','icon'=>'mdi:clock-check-outline'],

  ['page'=>'employee','label'=>'Employees','icon'=>'mdi:account-group'],

  ['page'=>'leave','label'=>'Leave Requests','icon'=>'mdi:calendar-month'],

  [
    'page'=>'report',
    'label'=>'Reports',
    'icon'=>'mdi:chart-box',
    'submenu'=>[
      ['page'=>'report/report_daily','label'=>'Daily Report','icon'=>'mdi:calendar-today'],
      ['page'=>'report/report_summary','label'=>'Summary Report','icon'=>'mdi:chart-line'],
      ['page'=>'report/report_detail','label'=>'Detailed Report','icon'=>'mdi:file-document-outline'],
      ['page'=>'report/report_top_employee','label'=>'Top Employees','icon'=>'mdi:star-circle'],
    ]
  ],

  // NEW SECURITY MENU
  [
    'page'=>'security',
    'label'=>'Security',
    'icon'=>'mdi:shield-lock',
    'submenu'=>[
      ['page'=>'user','label'=>'User Management','icon'=>'mdi:account-cog'],
      ['page'=>'roles','label'=>'Roles','icon'=>'mdi:shield-account'],
      ['page'=>'permissions','label'=>'Permissions','icon'=>'mdi:key-outline'],
      ['page'=>'audits','label'=>'Audit Logs','icon'=>'mdi:file-document-multiple']
    ]
  ],

];

        foreach ($menu_items as $item):
        if (isset($item['submenu'])) {
          $item['submenu'] = array_values(array_filter($item['submenu'], function ($sub) use ($canAccess) {
            return $canAccess($sub['page']);
          }));
          if (count($item['submenu']) === 0) {
            continue;
          }
        } else {
          if (!$canAccess($item['page'])) {
            continue;
          }
        }
        // Detect active submenu
        $has_active_submenu = false;
        if (isset($item['submenu'])) {
          foreach ($item['submenu'] as $sub) {
            if (str_starts_with($current_page, strtolower($sub['page']))) {
              $has_active_submenu = true;
              break;
            }
          }
        }
        // Detect active parent
        $is_active = $current_page === strtolower($item['page']);
        $is_parent_active = $is_active || $has_active_submenu;
      ?>
      <li class="mb-1.5 rounded-xl overflow-hidden <?= $is_parent_active ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white' : 'hover:bg-slate-700/50 text-slate-300' ?>">

        <?php if (isset($item['submenu'])): ?>

        <button type="button"
          class="nav-toggle w-full flex items-center px-4 py-3 gap-3"
          data-menu="<?= $item['page'] ?>">

          <span class="iconify text-xl" data-icon="<?= $item['icon'] ?>"></span>
          <span class="text-sm font-semibold"><?= $item['label'] ?></span>
          <span class="iconify ml-auto dropdown-arrow <?= $has_active_submenu ? 'rotate-180' : '' ?>"
                data-icon="mdi:chevron-down"></span>
        </button>

        <ul class="submenu <?= $has_active_submenu ? 'open' : '' ?> pl-8 pr-4 space-y-1"
          data-submenu="<?= $item['page'] ?>">
          <?php foreach ($item['submenu'] as $sub):
            $is_sub_active = str_starts_with($current_page, strtolower($sub['page']));
          ?>
          <li>
            <a href="?page=<?= strtolower($sub['page']) ?>"
              data-page="<?= strtolower($sub['page']) ?>"
              class="no-underline flex items-center gap-2 py-2.5 px-3 rounded-lg text-sm
                      <?= $is_sub_active
                          ? 'bg-slate-700 text-white font-semibold'
                          : 'text-slate-400 hover:text-white hover:bg-slate-700/30' ?>">
              <span class="iconify" data-icon="<?= $sub['icon'] ?>"></span>
              <?= $sub['label'] ?>
            </a>
          </li>
          <?php endforeach; ?>

        </ul>
        <?php else: ?>
        <a href="?page=<?= strtolower($item['page']) ?>"
          data-page="<?= strtolower($item['page']) ?>"
          class="no-underline flex items-center gap-3 px-4 py-3 rounded-xl
                text-sm font-semibold text-slate-200
                hover:bg-slate-700/40 transition">
          <span class="iconify text-[1em]" data-icon="<?= $item['icon'] ?>"></span>
          <span><?= $item['label'] ?></span>
        </a>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

  <script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Toggle submenu
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
        });
      });

    });
  </script>

  <style>
 .submenu {
  display: none;
}

.submenu.open {
  display: block;
}

.navigation a {
  text-decoration: none;
}

.dropdown-arrow {
  transition: transform 0.3s ease;
}

.rotate-180 {
  transform: rotate(180deg);
}

  </style>
