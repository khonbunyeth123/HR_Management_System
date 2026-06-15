<?php
/**
 * Reusable Select Component
 * 
 * @param string $label Select label
 * @param string $name Select name
 * @param array $options Array of ['value' => 'label']
 * @param string $value Selected value
 * @param string $id Select ID
 * @param string $class Additional CSS classes for the select
 * @param string $containerClass Additional CSS classes for the container
 * @param string $attr Additional HTML attributes
 * @param string $icon Iconify icon name to show inside the select
 * @param bool $required Whether the field is required
 * @param string $placeholder Optional placeholder option
 */

$label = $label ?? null;
$name = $name ?? '';
$options = $options ?? [];
$value = $value ?? '';
$id = $id ?? $name;
$class = $class ?? '';
$containerClass = $containerClass ?? '';
$attr = $attr ?? '';
$icon = $icon ?? null;
$required = $required ?? false;
$placeholder = $placeholder ?? null;

$selectClasses = "w-full border border-slate-200 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all bg-white appearance-none cursor-pointer";
if ($icon) $selectClasses .= " pl-8";

?>

<div class="flex flex-col gap-1 <?= $containerClass ?>">
    <?php if ($label): ?>
        <label for="<?= $id ?>" class="text-[10px] font-black text-slate-500 uppercase tracking-wider">
            <?= $label ?>
            <?php if ($required): ?><span class="text-rose-500">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <div class="relative flex items-center">
        <?php if ($icon): ?>
            <div class="absolute left-2.5 text-slate-400 pointer-events-none flex items-center justify-center">
                <span class="iconify text-[14px]" data-icon="<?= $icon ?>"></span>
            </div>
        <?php endif; ?>
        
        <select 
            name="<?= $name ?>" 
            id="<?= $id ?>" 
            class="<?= $selectClasses ?> <?= $class ?>"
            <?= $required ? 'required' : '' ?>
            <?= $attr ?>
        >
            <?php if ($placeholder): ?>
                <option value=""><?= htmlspecialchars($placeholder) ?></option>
            <?php endif; ?>
            
            <?php foreach ($options as $optValue => $optLabel): ?>
                <option value="<?= htmlspecialchars((string)$optValue) ?>" <?= (string)$value === (string)$optValue ? 'selected' : '' ?>>
                    <?= htmlspecialchars($optLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="absolute right-2.5 text-slate-400 pointer-events-none flex items-center justify-center">
            <span class="iconify text-[14px]" data-icon="mdi:chevron-down"></span>
        </div>
    </div>
</div>
