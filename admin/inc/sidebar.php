<div
  class="navigation max-h-[calc(100vh-56px)] bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white h-screen flex flex-col shadow-2xl border-r border-slate-700/50">

  <!-- Menu Section -->
  <div class="menu flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
    <ul class="p-3 space-y-1.5">
      <?php
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


      if ($_SESSION['utype'] === 'admin') {
        foreach ($menu_items as $index => $item) {
          // Check if current page matches this item or any submenu item
          $is_active = (strtolower($current_page) === strtolower($item['page']));
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

          echo '
            <li data-opt="' . $index . '" class="rounded-xl ' . $active_class . ' transition-all duration-300 transform hover:scale-[1.02]">';
          
          // If item has submenu, make it a dropdown
          if (isset($item['submenu'])) {
            echo '
              <button type="button" class="nav-toggle w-full flex items-center px-4 py-3 gap-3 group" data-menu="' . $item['page'] . '">
                <span class="iconify text-xl ' . ($is_parent_active ? 'text-white' : 'text-slate-400 group-hover:text-white') . ' transition-colors" data-icon="' . $item['icon'] . '"></span>
                <span class="text-sm font-semibold tracking-wide">' . $item['label'] . '</span>
                <span class="iconify ml-auto text-lg transition-transform duration-300 dropdown-arrow ' . ($has_active_submenu ? 'rotate-180' : '') . '" data-icon="mdi:chevron-down"></span>
              </button>
              <ul class="submenu ' . ($has_active_submenu ? '' : 'hidden') . ' pl-8 pr-4 pb-2 pt-1 space-y-1" data-submenu="' . $item['page'] . '">';
            
            foreach ($item['submenu'] as $subitem) {
              $is_sub_active = (strtolower($current_page) === strtolower($subitem['page']));
              $sub_active_class = $is_sub_active 
                ? 'bg-slate-700/70 text-white font-semibold border-l-2 border-indigo-400' 
                : 'text-slate-400 hover:text-white hover:bg-slate-700/30';
              
              $sub_icon = isset($subitem['icon']) ? $subitem['icon'] : 'mdi:circle-small';
              
              echo '
                <li>
                  <a href="?page=' . strtolower($subitem['page']) . '" data-page="' . strtolower($subitem['page']) . '" class="nav-link flex items-center gap-2 py-2.5 px-3 rounded-lg text-sm ' . $sub_active_class . ' transition-all duration-200 group">
                    <span class="iconify text-base ' . ($is_sub_active ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300') . '" data-icon="' . $sub_icon . '"></span>
                    <span>' . $subitem['label'] . '</span>
                    ' . ($is_sub_active ? '<span class="ml-auto w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse"></span>' : '') . '
                  </a>
                </li>';
            }
            
            echo '
              </ul>';
          } else {
            // Regular menu item without submenu
            echo '
              <a href="?page=' . strtolower($item['page']) . '" data-page="' . strtolower($item['page']) . '" class="nav-link flex items-center px-4 py-3 gap-3 group">
                <span class="iconify text-xl ' . ($is_active ? 'text-white' : 'text-slate-400 group-hover:text-white') . ' transition-colors" data-icon="' . $item['icon'] . '"></span>
                <span class="text-sm font-semibold tracking-wide">' . $item['label'] . '</span>
                ' . ($is_active ? '<span class="ml-auto w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>' : '') . '
              </a>';
          }
          
          echo '
            </li>';
        }
      } else {
        // Regular users (permission-based)
        foreach ($menu_items as $index => $item) {
          // Check permission for main menu item
          $stmt = $conn->prepare("SELECT aid FROM user_permissions WHERE uid = ? AND mid = ?");
          $stmt->bind_param("ii", $_SESSION['uid'], $item['mid']);
          $stmt->execute();
          $res = $stmt->get_result();
          
          if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $permission = $row['aid'];

            if ($permission > 0) {
              $is_active = (strtolower($current_page) === strtolower($item['page']));
              $has_active_submenu = false;
              
              // Check if submenu exists and has accessible items
              $accessible_submenu_items = [];
              if (isset($item['submenu'])) {
                foreach ($item['submenu'] as $subitem) {
                  // Check permission for submenu item
                  $stmt_sub = $conn->prepare("SELECT aid FROM user_permissions WHERE uid = ? AND mid = ?");
                  $stmt_sub->bind_param("ii", $_SESSION['uid'], $subitem['mid']);
                  $stmt_sub->execute();
                  $res_sub = $stmt_sub->get_result();
                  
                  if ($res_sub && $res_sub->num_rows > 0) {
                    $row_sub = $res_sub->fetch_assoc();
                    if ($row_sub['aid'] > 0) {
                      $accessible_submenu_items[] = $subitem;
                      if (strtolower($current_page) === strtolower($subitem['page'])) {
                        $has_active_submenu = true;
                      }
                    }
                  }
                }
              }

              // Only show menu item if it has accessible submenu items or is not a submenu parent
              if (!isset($item['submenu']) || count($accessible_submenu_items) > 0) {
                $is_parent_active = $is_active || $has_active_submenu;
                $active_class = $is_parent_active
                  ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/50'
                  : 'hover:bg-slate-700/50 text-slate-300 hover:text-white';

                echo '<li data-opt="' . $index . '" class="rounded-xl ' . $active_class . ' transition-all duration-300 transform hover:scale-[1.02]">';
                
                if (isset($item['submenu']) && count($accessible_submenu_items) > 0) {
                  echo '
                    <button type="button" class="nav-toggle w-full flex items-center px-4 py-3 gap-3 group" data-menu="' . $item['page'] . '">
                      <span class="iconify text-xl ' . ($is_parent_active ? 'text-white' : 'text-slate-400 group-hover:text-white') . ' transition-colors" data-icon="' . $item['icon'] . '"></span>
                      <span class="text-sm font-semibold tracking-wide">' . $item['label'] . '</span>
                      <span class="iconify ml-auto text-lg transition-transform duration-300 dropdown-arrow ' . ($has_active_submenu ? 'rotate-180' : '') . '" data-icon="mdi:chevron-down"></span>
                    </button>
                    <ul class="submenu ' . ($has_active_submenu ? '' : 'hidden') . ' pl-8 pr-4 pb-2 pt-1 space-y-1" data-submenu="' . $item['page'] . '">';
                  
                  foreach ($accessible_submenu_items as $subitem) {
                    $is_sub_active = (strtolower($current_page) === strtolower($subitem['page']));
                    $sub_active_class = $is_sub_active 
                      ? 'bg-slate-700/70 text-white font-semibold border-l-2 border-indigo-400' 
                      : 'text-slate-400 hover:text-white hover:bg-slate-700/30';
                    
                    $sub_icon = isset($subitem['icon']) ? $subitem['icon'] : 'mdi:circle-small';
                    
                    echo '
                      <li>
                        <a href="?page=' . strtolower($subitem['page']) . '" data-page="' . strtolower($subitem['page']) . '" class="nav-link flex items-center gap-2 py-2.5 px-3 rounded-lg text-sm ' . $sub_active_class . ' transition-all duration-200 group">
                          <span class="iconify text-base ' . ($is_sub_active ? 'text-indigo-400' : 'text-slate-500 group-hover:text-slate-300') . '" data-icon="' . $sub_icon . '"></span>
                          <span>' . $subitem['label'] . '</span>
                          ' . ($is_sub_active ? '<span class="ml-auto w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse"></span>' : '') . '
                        </a>
                      </li>';
                  }
                  
                  echo '</ul>';
                } else {
                  echo '
                    <a href="?page=' . strtolower($item['page']) . '" data-page="' . strtolower($item['page']) . '" class="nav-link flex items-center px-4 py-3 gap-3 group">
                      <span class="iconify text-xl ' . ($is_active ? 'text-white' : 'text-slate-400 group-hover:text-white') . ' transition-colors" data-icon="' . $item['icon'] . '"></span>
                      <span class="text-sm font-semibold tracking-wide">' . $item['label'] . '</span>
                      ' . ($is_active ? '<span class="ml-auto w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>' : '') . '
                    </a>';
                }
                
                echo '</li>';
              }
            }
          }
        }
      }
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

    const contentArea = document.getElementById('content-area');
    if (!contentArea) return console.error('Content area not found!');

    // Store mounted pages
    const pageCache = {};
    let currentPage = null;

    // Dropdown toggle functionality - FIXED
    document.querySelectorAll('.nav-toggle').forEach(toggle => {
      toggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation(); // Extra safety to prevent any propagation
        
        const menuName = this.dataset.menu;
        const submenu = document.querySelector(`[data-submenu="${menuName}"]`);
        const arrow = this.querySelector('.dropdown-arrow');
        
        if (submenu) {
          const isHidden = submenu.classList.contains('hidden');
          
          // Close all other submenus
          document.querySelectorAll('.submenu').forEach(sub => {
            if (sub !== submenu) {
              sub.classList.add('hidden');
              const otherArrow = sub.previousElementSibling?.querySelector('.dropdown-arrow');
              if (otherArrow) otherArrow.classList.remove('rotate-180');
            }
          });
          
          // Toggle current submenu
          submenu.classList.toggle('hidden');
          if (arrow) {
            arrow.classList.toggle('rotate-180');
          }
        }
        
        return false; // Extra safety
      });
    });

    // Auto-expand submenu if a submenu item is active on page load
    const urlParams = new URLSearchParams(window.location.search);
    const currentPageParam = urlParams.get('page') || 'dashboard';
    
    document.querySelectorAll('.submenu').forEach(submenu => {
      const links = submenu.querySelectorAll('.nav-link');
      links.forEach(link => {
        if (link.dataset.page === currentPageParam) {
          submenu.classList.remove('hidden');
          const arrow = submenu.previousElementSibling?.querySelector('.dropdown-arrow');
          if (arrow) arrow.classList.add('rotate-180');
        }
      });
    });

    // Event delegation for nav links - FIXED to ignore button clicks
    document.addEventListener('click', function (e) {
      // Ignore clicks on buttons (dropdown toggles)
      if (e.target.closest('.nav-toggle')) {
        return;
      }
      
      const link = e.target.closest('.nav-link');
      if (!link) return;

      e.preventDefault();
      const page = link.dataset.page;
      if (!page) return;

      if (page === currentPage) return;

      currentPage = page;
      updateActiveState(link);
      showPage(page);

      const newUrl = window.location.pathname + '?page=' + page;
      window.history.pushState({ page }, '', newUrl);
      updatePageTitle(page);
    });

    // Handle back/forward buttons
    window.addEventListener('popstate', function (e) {
      const urlParams = new URLSearchParams(window.location.search);
      const page = urlParams.get('page') || 'dashboard';

      if (page === currentPage) return;

      currentPage = page;
      const activeLink = document.querySelector(`.nav-link[data-page="${page}"]`);
      if (activeLink) updateActiveState(activeLink);

      showPage(page);
      updatePageTitle(page);
    });

    // Initial load
    const initialPage = urlParams.get('page') || 'dashboard';
    currentPage = initialPage;
    const initialLink = document.querySelector(`.nav-link[data-page="${initialPage}"]`);
    if (initialLink) updateActiveState(initialLink);
    showPage(initialPage);

    // --- Functions ---
    function showPage(page) {
      if (pageCache[page]) {
        contentArea.innerHTML = pageCache[page];
        if (window.initializePageScripts) window.initializePageScripts();
        return;
      }

      contentArea.innerHTML = `
      <div class="flex items-center justify-center h-64">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500 mx-auto mb-4"></div>
          <p class="text-slate-600">Loading ${page}...</p>
        </div>
      </div>
    `;

      fetch('pages/' + page + '.php')
        .then(res => {
          if (!res.ok) throw new Error('Page not found');
          return res.text();
        })
        .then(html => {
          pageCache[page] = html;
          contentArea.innerHTML = html;
          if (window.initializePageScripts) window.initializePageScripts();
        })
        .catch(err => {
          console.error(err);
          contentArea.innerHTML = `
          <div class="p-8 text-center">
            <div class="text-red-500 text-5xl mb-4">⚠️</div>
            <h3 class="text-xl font-semibold text-slate-700 mb-2">Error Loading Page</h3>
            <p class="text-slate-500">Could not load ${page}.php</p>
            <p class="text-slate-400 text-sm mt-2">Make sure the file exists in the pages/ directory</p>
          </div>
        `;
        });
    }

    function updateActiveState(clickedLink) {
      // Remove active state from all top-level items
      document.querySelectorAll('li[data-opt]').forEach(li => {
        li.classList.remove('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white', 'shadow-lg', 'shadow-indigo-500/50');
        li.classList.add('hover:bg-slate-700/50', 'text-slate-300', 'hover:text-white');
      });

      // Remove active state from all submenu items
      document.querySelectorAll('.submenu .nav-link').forEach(link => {
        link.classList.remove('bg-slate-700/70', 'text-white', 'font-semibold', 'border-l-2', 'border-indigo-400');
        link.classList.add('text-slate-400', 'hover:text-white', 'hover:bg-slate-700/30');
        
        // Remove pulse indicator from all submenu items
        const pulse = link.querySelector('.animate-pulse');
        if (pulse) pulse.remove();
        
        // Reset icon colors
        const icon = link.querySelector('.iconify');
        if (icon) {
          icon.classList.remove('text-indigo-400');
          icon.classList.add('text-slate-500', 'group-hover:text-slate-300');
        }
      });

      // Remove pulse indicators from top-level items
      document.querySelectorAll('li[data-opt] > a .animate-pulse, li[data-opt] > .nav-link .animate-pulse').forEach(pulse => pulse.remove());

      // Find the parent li and activate it
      const parentLi = clickedLink.closest('li[data-opt]');
      if (parentLi) {
        parentLi.classList.add('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white', 'shadow-lg', 'shadow-indigo-500/50');
        parentLi.classList.remove('hover:bg-slate-700/50', 'text-slate-300', 'hover:text-white');
        
        // If it's a submenu item, also activate the submenu link
        if (clickedLink.closest('.submenu')) {
          clickedLink.classList.add('bg-slate-700/70', 'text-white', 'font-semibold', 'border-l-2', 'border-indigo-400');
          clickedLink.classList.remove('text-slate-400', 'hover:text-white', 'hover:bg-slate-700/30');
          
          // Add pulse indicator to active submenu item
          const pulseSpan = document.createElement('span');
          pulseSpan.className = 'ml-auto w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse';
          clickedLink.appendChild(pulseSpan);
          
          // Update icon color for active submenu item
          const icon = clickedLink.querySelector('.iconify');
          if (icon) {
            icon.classList.remove('text-slate-500', 'group-hover:text-slate-300');
            icon.classList.add('text-indigo-400');
          }
        } else {
          // Add pulse indicator to active top-level item
          const topLevelLink = parentLi.querySelector('a.nav-link');
          if (topLevelLink && !topLevelLink.querySelector('.animate-pulse')) {
            const pulseSpan = document.createElement('span');
            pulseSpan.className = 'ml-auto w-1.5 h-1.5 bg-white rounded-full animate-pulse';
            topLevelLink.appendChild(pulseSpan);
          }
        }
        
        // Keep submenu open if clicking a submenu item
        const submenu = clickedLink.closest('.submenu');
        if (submenu) {
          submenu.classList.remove('hidden');
          const arrow = submenu.previousElementSibling?.querySelector('.dropdown-arrow');
          if (arrow) arrow.classList.add('rotate-180');
        }
      }
    }

    function updatePageTitle(page) {
      const navTitle = document.querySelector('.nav-title') || document.querySelector('h2.text-white');
      if (navTitle) {
        const parts = page.split('_');
        const displayName = parts.map(part => part.charAt(0).toUpperCase() + part.slice(1)).join(' ');
        navTitle.textContent = displayName;
      }
    }

  });

</script>

<style>
  /* Custom Scrollbar */
  .scrollbar-thin::-webkit-scrollbar {
    width: 6px;
  }

  .scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
  }

  .scrollbar-thin::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 3px;
  }

  .scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #64748b;
  }

  /* Dropdown arrow animation */
  .dropdown-arrow {
    transition: transform 0.3s ease;
  }

  .dropdown-arrow.rotate-180 {
    transform: rotate(180deg);
  }

  /* Submenu transition */
  .submenu {
    overflow: hidden;
    transition: all 0.3s ease;
  }
  
  /* Prevent text selection on buttons */
  .nav-toggle {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
  }
</style>