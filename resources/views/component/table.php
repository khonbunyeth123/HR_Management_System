<?php
/**
 * Reusable Table Component
 * 
 * Usage:
 * <?php include 'table.php'; ?>
 */
?>
<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
    <div class="sticky-table-wrapper overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-800 text-white shadow-sm">
                <?php echo $tableHead; ?>
            </thead>
            <tbody id="<?php echo $tbodyId; ?>" class="divide-y divide-gray-100">
                <?php echo $tableBody; ?>
            </tbody>
        </table>
    </div>
    <div id="<?php echo $paginationId; ?>" class="p-2 border-t border-gray-100"></div>
</div>
