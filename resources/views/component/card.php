<?php
/**
 * Reusable Card Component
 * 
 * @param string $title Card title
 * @param string $icon Iconify icon name
 * @param string $headerRight Content to put on the right side of the header
 * @param string $content Main content of the card
 * @param string $footer Footer content
 * @param string $class Additional CSS classes for the container
 * @param string $bodyClass Additional CSS classes for the card body
 * @param bool $padding Whether to include default padding in the body (default: true)
 */

$title = $title ?? null;
$icon = $icon ?? null;
$headerRight = $headerRight ?? null;
$content = $content ?? '';
$footer = $footer ?? null;
$id = $id ?? null;
$class = $class ?? '';
$bodyClass = $bodyClass ?? '';
$padding = $padding ?? true;

$containerClasses = "bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden flex flex-col {$class}";
$bodyPadding = $padding ? "p-3" : "";
$idAttr = $id ? "id=\"{$id}\"" : "";
?>

<div <?= $idAttr ?> class="<?= $containerClasses ?>">
    <?php if ($title || $icon || $headerRight): ?>
        <div class="px-3 py-2 border-b border-slate-100 bg-white/50">
            <div class="flex items-center justify-between gap-2">
                <?php if ($title || $icon): ?>
                    <h2 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <?php if ($icon): ?>
                            <span class="iconify" data-icon="<?= $icon ?>"></span>
                        <?php endif; ?>
                        <?= $title ?>
                    </h2>
                <?php endif; ?>
                
                <?php if ($headerRight): ?>
                    <div class="flex items-center gap-1">
                        <?= $headerRight ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="<?= $bodyPadding ?> <?= $bodyClass ?> flex-grow min-h-0">
        <?= $content ?>
    </div>

    <?php if ($footer): ?>
        <div class="px-3 py-2 border-t border-slate-100 bg-slate-50/50">
            <?= $footer ?>
        </div>
    <?php endif; ?>
</div>
