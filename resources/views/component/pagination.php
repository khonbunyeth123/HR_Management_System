<?php
/**
 * Reusable Pagination Component
 * 
 * Usage in your PHP file:
 * 
 * include 'components/pagination.php';
 * 
 * renderPagination([
 *     'current_page' => 1,
 *     'total_pages' => 10,
 *     'showing_from' => 1,
 *     'showing_to' => 18,
 *     'total_records' => 180,
 *     'show_page_numbers' => true,
 *     'max_page_buttons' => 5
 * ]);
 */

function renderPagination($options = [])
{
    // Default options
    $defaults = [
        'current_page' => 1,
        'total_pages' => 1,
        'showing_from' => 0,
        'showing_to' => 0,
        'total_records' => 0,
        'show_page_numbers' => true,
        'max_page_buttons' => 5,
        'prev_text' => 'Previous',
        'next_text' => 'Next',
        'show_icons' => true
    ];

    $config = array_merge($defaults, $options);
    extract($config);

    // Calculate page numbers to display
    $page_numbers = [];
    if ($show_page_numbers && $total_pages > 1) {
        $half_max_buttons = floor($max_page_buttons / 2);
        $start_page = max(1, $current_page - $half_max_buttons);
        $end_page = min($total_pages, $current_page + $half_max_buttons);

        // Adjust if we're at the beginning or end
        if ($current_page <= $half_max_buttons) {
            $end_page = min($total_pages, $max_page_buttons);
        } elseif ($current_page + $half_max_buttons >= $total_pages) {
            $start_page = max(1, $total_pages - $max_page_buttons + 1);
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            $page_numbers[] = $i;
        }
    }

    $show_first_ellipsis = !empty($page_numbers) && $page_numbers[0] > 2;
    $show_last_ellipsis = !empty($page_numbers) && end($page_numbers) < $total_pages - 1;
    ?>

    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <!-- Pagination Info -->
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium"><?= $showing_from ?></span> to
                <span class="font-medium"><?= $showing_to ?></span> of
                <span class="font-medium"><?= $total_records ?></span> results
            </div>

            <!-- Pagination Controls -->
            <div class="flex items-center gap-2">
                <!-- Previous Button -->
                <button id="paginationPrevBtn"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-gray-700 disabled:hover:border-gray-300"
                    <?= $current_page === 1 ? 'disabled' : '' ?> data-page="<?= $current_page - 1 ?>">
                    <?php if ($show_icons): ?>
                        <span class="iconify mr-1" data-icon="mdi:chevron-left"></span>
                    <?php endif; ?>
                    <?= $prev_text ?>
                </button>

                <?php if ($show_page_numbers && $total_pages > 1): ?>
                    <!-- First Page -->
                    <?php if (!empty($page_numbers) && $page_numbers[0] > 1): ?>
                        <button
                            class="pagination-page-btn w-10 h-10 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all"
                            data-page="1">
                            1
                        </button>
                        <?php if ($show_first_ellipsis): ?>
                            <span class="px-2 text-gray-500">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php foreach ($page_numbers as $page): ?>
                        <button
                            class="pagination-page-btn w-10 h-10 border rounded-lg text-sm font-medium transition-all <?= $page === $current_page ? 'bg-indigo-600 text-white border-indigo-600 cursor-default' : 'bg-white text-gray-700 border-gray-300 hover:bg-indigo-600 hover:text-white hover:border-indigo-600' ?>"
                            data-page="<?= $page ?>" <?= $page === $current_page ? 'disabled' : '' ?>>
                            <?= $page ?>
                        </button>
                    <?php endforeach; ?>

                    <!-- Last Page -->
                    <?php if (!empty($page_numbers) && end($page_numbers) < $total_pages): ?>
                        <?php if ($show_last_ellipsis): ?>
                            <span class="px-2 text-gray-500">...</span>
                        <?php endif; ?>
                        <button
                            class="pagination-page-btn w-10 h-10 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all"
                            data-page="<?= $total_pages ?>">
                            <?= $total_pages ?>
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Simple Page Display -->
                    <div class="text-sm font-medium text-gray-700 px-4">
                        Page <?= $current_page ?> of <?= $total_pages ?>
                    </div>
                <?php endif; ?>

                <!-- Next Button -->
                <button id="paginationNextBtn"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-gray-700 disabled:hover:border-gray-300"
                    <?= $current_page === $total_pages ? 'disabled' : '' ?> data-page="<?= $current_page + 1 ?>">
                    <?= $next_text ?>
                    <?php if ($show_icons): ?>
                        <span class="iconify ml-1" data-icon="mdi:chevron-right"></span>
                    <?php endif; ?>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Attach pagination event listeners
        (function () {
            const prevBtn = document.getElementById('paginationPrevBtn');
            const nextBtn = document.getElementById('paginationNextBtn');
            const pageButtons = document.querySelectorAll('.pagination-page-btn');

            if (prevBtn && !prevBtn.disabled) {
                prevBtn.addEventListener('click', function () {
                    const page = parseInt(this.dataset.page);
                    if (page > 0 && typeof loadEmployees === 'function') {
                        loadEmployees(page);
                    }
                });
            }

            if (nextBtn && !nextBtn.disabled) {
                nextBtn.addEventListener('click', function () {
                    const page = parseInt(this.dataset.page);
                    if (page > 0 && typeof loadEmployees === 'function') {
                        loadEmployees(page);
                    }
                });
            }

            pageButtons.forEach(function (btn) {
                if (!btn.disabled) {
                    btn.addEventListener('click', function () {
                        const page = parseInt(this.dataset.page);
                        if (page > 0 && typeof loadEmployees === 'function') {
                            loadEmployees(page);
                        }
                    });
                }
            });
        })();
    </script>

    <?php
}
?>