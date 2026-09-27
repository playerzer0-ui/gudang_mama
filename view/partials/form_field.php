<?php
// Renders one labelled input inside a .gm-form-field wrapper.
// $options: type, value, placeholder, oninput, required, readonly, name (defaults to $id).
// A null value leaves the value attribute out; a trailing " *" on required labels is added automatically.
function form_field(string $id, string $label, array $options = []): void
{
    $escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    $required = !empty($options['required']);

    $attributes = 'name="' . $escape($options['name'] ?? $id) . '"'
        . ' type="' . $escape($options['type'] ?? 'text') . '"'
        . ' id="' . $escape($id) . '"';
    if (isset($options['placeholder'])) {
        $attributes .= ' placeholder="' . $escape($options['placeholder']) . '"';
    }
    if (isset($options['value'])) {
        $attributes .= ' value="' . $escape($options['value']) . '"';
    }
    if (isset($options['oninput'])) {
        $attributes .= ' oninput="' . $escape($options['oninput']) . '"';
    }
    if ($required) {
        $attributes .= ' required';
    }
    if (!empty($options['readonly'])) {
        $attributes .= ' readonly';
    }

    echo '<div class="gm-form-field"><label for="' . $escape($id) . '">' . $escape($label) . ($required ? ' *' : '')
        . '</label><input ' . $attributes . '></div>' . "\n";
}
