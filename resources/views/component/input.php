<?php
/**
 * Reusable Input Component
 * 
 * @param string $label Input label
 * @param string $name Input name
 * @param string $type Input type (text, password, email, number, date, etc.)
 * @param string $value Input value
 * @param string $placeholder Placeholder text
 * @param string $id Input ID
 * @param string $class Additional CSS classes for the input
 * @param string $containerClass Additional CSS classes for the container
 * @param string $attr Additional HTML attributes
 * @param string $icon Iconify icon name to show inside the input
 * @param bool $required Whether the field is required
 */

$label = $label ?? null;
$name = $name ?? '';
$type = $type ?? 'text';
$value = $value ?? '';
$placeholder = $placeholder ?? '';
$id = $id ?? $name;
$class = $class ?? '';
$containerClass = $containerClass ?? '';
$attr = $attr ?? '';
$icon = $icon ?? null;
$required = $required ?? false;

$inputClasses = "w-full border border-slate-200 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-slate-400 bg-white";
if ($icon) $inputClasses .= " pl-8";

?>

<div class="flex flex-col gap-1 <?= $containerClass ?>">
    <?php if ($label): ?>
        <label for="<?= $id ?>" class="text-[10px] font-black text-slate-500 normal-case tracking-wider">
            <?= $label ?>
            <?php if ($required): ?><span class="text-rose-500">*</span>    <?php endif; ?>
        </label>
    <?php endif; ?>

    <div class="relative flex items-center">
        <?php if ($icon): ?>
            <div class="absolute left-2.5 text-slate-400 pointer-events-none flex items-center justify-center">
                <span class="iconify text-[14px]" data-icon="<?= $icon ?>"></span>
            </div>
        <?php endif; ?>
        
        <input 
            type="<?= $type ?>" 
            name="<?= $name ?>" 
            id="<?= $id ?>" 
            value="<?= htmlspecialchars((string)$value) ?>" 
            placeholder="<?= htmlspecialchars($placeholder) ?>"
            class="<?= $inputClasses ?> <?= $class ?>"
            <?= $required ? 'required' : '' ?>
            <?= $attr ?>
        >
    </div>
</div>
