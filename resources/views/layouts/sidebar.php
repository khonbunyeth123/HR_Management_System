<?php include __DIR__ . '/header.php'; ?>
<?php require_once __DIR__ . '/../../../app/Helpers/PermissionHelper.php'; ?>

<div class="navigation bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white flex flex-col shadow-2xl border-r border-slate-700/50" style="max-height: 100vh;">
  <div class="menu flex-1" style="overflow: hidden; max-height: calc(100vh - 56px);">
    <ul class="p-3" style="overflow: hidden;">

      <?php
      $current_page = isset($_GET['page']) ? strtolower($_GET['page']) : 'dashboard';
      $current_action = isset($_GET['action']) ? strtolower((string) $_GET['action']) : '';

      $pagePermissions = [
        'dashboard' => ['dashboard.view'],
        'attendance' => ['attendance.view'],
        'employee' => ['employee.view', 'employees.view'],
        'leave' => ['leave.view'],
        'report' => ['report.view'],
        'report/report_daily' => ['report.view_daily', 'report.view'],
        'report/report_summary' => ['report.view_summary', 'report.view'],
        'report/report_detail' => ['report.view_detail', 'report.view'],
        'report/report_top_employee' => ['report.view_top', 'report.view'],
        'user' => ['user.view', 'users.view'],
        'roles' => ['role.view', 'roles.view'],
        'permissions' => ['permission.view', 'permissions.view'],
        'audits' => ['audits.view', 'audit.view'],
      ];

      $canAccessPage = static function (string $pageName) use ($pagePermissions): bool {
        $pageName = strtolower($pageName);

        if (!isset($pagePermissions[$pageName])) {
          return true;
        }

        $slugs = $pagePermissions[$pageName];
        $slugs = is_array($slugs) ? $slugs : [$slugs];

        if (function_exists('hasAnyPermissionSlugs')) {
          return hasAnyPermissionSlugs($slugs);
        }

        foreach ($slugs as $slug) {
          if (is_string($slug) && hasPermissionSlug($slug)) {
            return true;
          }
        }

        return false;
      };

      $isSubmenuActive = static function (array $sub, string $activePage, string $activeAction): bool {
        $subPage = strtolower((string) ($sub['page'] ?? ''));

        if ($subPage === '' || !str_starts_with($activePage, $subPage)) {
          return false;
        }

        if (isset($sub['active_action'])) {
          return $activeAction === strtolower((string) $sub['active_action']);
        }

        if (isset($sub['exclude_action'])) {
          return $activeAction !== strtolower((string) $sub['exclude_action']);
        }

        return true;
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
        ['page'=>'user','label'=>'User Management','icon'=>'mdi:account-cog'],
        [
          'page'=>'setting',
          'label'=>'Settings',
          'icon'=>'mdi:cog',
          'submenu'=>[
            ['page'=>'roles','label'=>'Roles','icon'=>'mdi:shield-account'],
            ['page'=>'permissions','label'=>'Permissions','icon'=>'mdi:key-outline']
          ]
        ],
      ];

      foreach ($menu_items as $item):
        $visibleSubmenu = [];

        if (isset($item['submenu'])) {
          foreach ($item['submenu'] as $sub) {
            if ($canAccessPage($sub['page'])) {
              $visibleSubmenu[] = $sub;
            }
          }

          if (empty($visibleSubmenu)) {
            continue;
          }
        } else {
          if (!$canAccessPage($item['page'])) {
            continue;
          }
        }

        $has_active_submenu = false;
        if (!empty($visibleSubmenu)) {
          foreach ($visibleSubmenu as $sub) {
            if ($isSubmenuActive($sub, $current_page, $current_action)) {
              $has_active_submenu = true;
              break;
            }
          }
        }

        $is_active = $current_page === strtolower($item['page']);
        $is_parent_active = $is_active || $has_active_submenu;
      ?>
      <li class="mb-1.5 rounded-xl overflow-hidden <?= $is_parent_active ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white' : 'hover:bg-slate-700/50 text-slate-300' ?>">

        <?php if (!empty($visibleSubmenu)): ?>
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
          <?php foreach ($visibleSubmenu as $sub):
            $is_sub_active = $isSubmenuActive($sub, $current_page, $current_action);
            $sub_href = $sub['href'] ?? ('?page=' . strtolower($sub['page']));
          ?>
          <li>
            <a href="<?= htmlspecialchars($sub_href, ENT_QUOTES, 'UTF-8') ?>"
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
