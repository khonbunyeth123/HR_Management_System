/**
 * Reusable Pagination Component
 * Save as: components/pagination.js
 * 
 * Usage:
 * <script src="components/pagination.js"></script>
 * 
 * renderPagination({
 *   currentPage: 1,
 *   totalPages: 10,
 *   showingFrom: 1,
 *   showingTo: 18,
 *   totalRecords: 180,
 *   showPageNumbers: true,
 *   onPrevious: () => {},
 *   onNext: () => {},
 *   onPageClick: (page) => {}
 * });
 */

function renderPagination(options) {
    const {
        currentPage = 1,
        totalPages = 1,
        showingFrom = 0,
        showingTo = 0,
        totalRecords = 0,
        containerSelector = '#paginationContainer',
        onPrevious = () => { },
        onNext = () => { },
        onPageClick = null,
        showPageNumbers = true,
        maxPageButtons = 5,
        prevText = 'Previous',
        nextText = 'Next',
        showIcons = true
    } = options;

    const container = typeof containerSelector === 'string'
        ? document.querySelector(containerSelector)
        : containerSelector;

    if (!container) {
        console.error('Pagination container not found');
        return;
    }

    // Calculate page numbers to display
    let pageNumbers = [];
    if (showPageNumbers && totalPages > 1) {
        const halfMaxButtons = Math.floor(maxPageButtons / 2);
        let startPage = Math.max(1, currentPage - halfMaxButtons);
        let endPage = Math.min(totalPages, currentPage + halfMaxButtons);

        // Adjust if we're at the beginning or end
        if (currentPage <= halfMaxButtons) {
            endPage = Math.min(totalPages, maxPageButtons);
        } else if (currentPage + halfMaxButtons >= totalPages) {
            startPage = Math.max(1, totalPages - maxPageButtons + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            pageNumbers.push(i);
        }
    }

    const showFirstEllipsis = pageNumbers.length > 0 && pageNumbers[0] > 2;
    const showLastEllipsis = pageNumbers.length > 0 && pageNumbers[pageNumbers.length - 1] < totalPages - 1;

    // Build page number buttons HTML
    let pageNumbersHTML = '';
    if (showPageNumbers && totalPages > 1) {
        // First page button
        if (pageNumbers.length > 0 && pageNumbers[0] > 1) {
            pageNumbersHTML += `
                <button 
                    class="pagination-page-btn w-10 h-10 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all" 
                    data-page="1"
                >
                    1
                </button>
            `;
            if (showFirstEllipsis) {
                pageNumbersHTML += '<span class="px-2 text-gray-500">...</span>';
            }
        }

        // Page number buttons
        pageNumbers.forEach(page => {
            const isActive = page === currentPage;
            pageNumbersHTML += `
                <button 
                    class="pagination-page-btn w-10 h-10 border rounded-lg text-sm font-medium transition-all ${isActive
                    ? 'bg-indigo-600 text-white border-indigo-600 cursor-default'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-indigo-600 hover:text-white hover:border-indigo-600'
                }"
                    data-page="${page}"
                    ${isActive ? 'disabled' : ''}
                >
                    ${page}
                </button>
            `;
        });

        // Last page button
        if (pageNumbers.length > 0 && pageNumbers[pageNumbers.length - 1] < totalPages) {
            if (showLastEllipsis) {
                pageNumbersHTML += '<span class="px-2 text-gray-500">...</span>';
            }
            pageNumbersHTML += `
                <button 
                    class="pagination-page-btn w-10 h-10 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all" 
                    data-page="${totalPages}"
                >
                    ${totalPages}
                </button>
            `;
        }
    } else {
        // Simple page display
        pageNumbersHTML = `
            <div class="text-sm font-medium text-gray-700 px-4">
                Page ${currentPage} of ${totalPages}
            </div>
        `;
    }

    // Build full pagination HTML
    const paginationHTML = `
        <div class="bg-gray-50 px-2 py-1 border-t border-gray-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <!-- Pagination Info -->
                <div class="text-sm text-gray-600">
                    Showing <span class="font-medium">${showingFrom}</span> to 
                    <span class="font-medium">${showingTo}</span> of 
                    <span class="font-medium">${totalRecords}</span> results
                </div>

                <!-- Pagination Controls -->
                <div class="flex items-center gap-2">
                    <!-- Previous Button -->
                    <button 
                        id="paginationPrevBtn" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-gray-700 disabled:hover:border-gray-300"
                        ${currentPage === 1 ? 'disabled' : ''}
                    >
                        ${showIcons ? '<span class="iconify mr-1" data-icon="mdi:chevron-left"></span>' : ''}
                        ${prevText}
                    </button>

                    ${pageNumbersHTML}

                    <!-- Next Button -->
                    <button 
                        id="paginationNextBtn" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-gray-700 disabled:hover:border-gray-300"
                        ${currentPage === totalPages ? 'disabled' : ''}
                    >
                        ${nextText}
                        ${showIcons ? '<span class="iconify ml-1" data-icon="mdi:chevron-right"></span>' : ''}
                    </button>
                </div>
            </div>
        </div>
    `;

    container.innerHTML = paginationHTML;

    // Attach event listeners
    const prevBtn = container.querySelector('#paginationPrevBtn');
    const nextBtn = container.querySelector('#paginationNextBtn');
    const pageButtons = container.querySelectorAll('.pagination-page-btn');

    if (prevBtn && !prevBtn.disabled) {
        prevBtn.addEventListener('click', onPrevious);
    }

    if (nextBtn && !nextBtn.disabled) {
        nextBtn.addEventListener('click', onNext);
    }

    if (onPageClick) {
        pageButtons.forEach(btn => {
            if (!btn.disabled) {
                btn.addEventListener('click', () => {
                    const page = parseInt(btn.dataset.page);
                    if (!isNaN(page)) {
                        onPageClick(page);
                    }
                });
            }
        });
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { renderPagination };
}