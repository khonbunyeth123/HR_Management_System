<?php
/**
 * Reusable Button Component
 * 
 * @param string $label The text to display
 * @param string $type primary|secondary|danger|ghost|success|link (default: primary)
 * @param string $size xs|sm|md (default: sm)
 * @param string $icon Iconify icon name (optional)
 * @param string $href If provided, renders an <a> tag instead of <button>
 * @param string $id Element ID
 * @param string $class Additional CSS classes
 * @param string $attr Additional HTML attributes
 */

$label = $label ?? '';
$type = $type ?? 'primary';
$size = $size ?? 'sm';
$icon = $icon ?? null;
$href = $href ?? null;
$id = $id ?? null;
$class = $class ?? '';
$attr = $attr ?? '';

$baseClasses = "inline-flex items-center justify-center gap-1.5 font-black rounded-lg transition-all duration-200 shadow-sm disabled:opacity-50 disabled:pointer-events-none whitespace-nowrap";

$typeClasses = [
    'primary'   => "bg-indigo-600 text-white hover:bg-indigo-700 hover:shadow-indigo-100",
    'secondary' => "bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 hover:border-slate-300",
    'danger'    => "bg-rose-500 text-white hover:bg-rose-600 hover:shadow-rose-100",
    'success'   => "bg-emerald-500 text-white hover:bg-emerald-600 hover:shadow-emerald-100",
    'ghost'     => "bg-transparent text-slate-600 hover:bg-slate-100 shadow-none hover:shadow-none border border-transparent",
    'link'      => "bg-transparent text-indigo-600 hover:underline shadow-none hover:shadow-none p-0",
];

$sizeClasses = [
    'xs' => "px-2 py-1 text-[11px]",
    'sm' => "px-3 py-1.5 text-xs",
    'md' => "px-4 py-2 text-sm",
];

// Explicit icon sizes to keep things "pro"
$iconSizeClasses = [
    'xs' => "text-[12px]",
    'sm' => "text-[14px]",
    'md' => "text-[16px]",
];

$selectedType = $typeClasses[$type] ?? $typeClasses['primary'];
$selectedSize = $sizeClasses[$size] ?? $sizeClasses['sm'];
$selectedIconSize = $iconSizeClasses[$size] ?? $iconSizeClasses['sm'];

$finalClasses = "{$baseClasses} {$selectedType} {$selectedSize} {$class}";

$idAttr = $id ? "id=\"{$id}\"" : "";
$tag = $href ? "a" : "button";
$hrefAttr = $href ? "href=\"{$href}\"" : "";
$typeAttr = (!$href && $tag === 'button' && strpos($attr, 'type=') === false) ? "type=\"button\"" : "";

?>

<<?= $tag ?> <?= $idAttr ?> <?= $hrefAttr ?> <?= $typeAttr ?> class="<?= $finalClasses ?>" <?= $attr ?>>
    <?php if ($icon): ?>
        <span class="iconify <?= $selectedIconSize ?> shrink-0" data-icon="<?= $icon ?>"></span>
    <?php endif; ?>
    <?= $label ?>
</<?= $tag ?>>
