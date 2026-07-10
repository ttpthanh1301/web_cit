<?php
declare(strict_types=1);

function asset_attrs(array $attrs): string
{
    $pairs = [];
    foreach ($attrs as $name => $value) {
        if ($value === null || $value === false) {
            continue;
        }
        $pairs[] = $name . '="' . htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
    }

    return $pairs ? ' ' . implode(' ', $pairs) : '';
}

function render_component(string $component, array $props = []): void
{
    $component = preg_replace('/[^a-zA-Z0-9_\/\\-]+/', '', $component);
    $componentPath = __DIR__ . '/components/' . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $component) . '.php';

    if (!is_file($componentPath)) {
        return;
    }

    extract($props, EXTR_SKIP);
    require $componentPath;
}

function field_options(?string $options): array
{
    if ($options === null || trim($options) === '') {
        return [];
    }

    $options = trim($options);
    if (str_starts_with($options, '[') || str_starts_with($options, '{')) {
        $decoded = json_decode($options, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map(static fn ($value): string => trim((string) $value), $decoded), static fn ($value): bool => $value !== ''));
        }
    }

    $lines = preg_split('/\r\n|\r|\n/', $options);
    return array_values(array_filter(array_map(static fn ($line): string => trim((string) $line), $lines), static fn ($line): bool => $line !== ''));
}

function encode_field_options(string $options): ?string
{
    $values = field_options($options);
    return $values === [] ? null : json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function unique_field_name(PDO $pdo, string $label): string
{
    $base = preg_replace('/[^a-z0-9]+/', '_', strtolower($label));
    $base = trim($base, '_');
    if ($base === '') {
        $base = 'field';
    }

    $name = $base;
    $suffix = 1;
    $statement = $pdo->prepare('SELECT 1 FROM form_fields WHERE field_name = :name LIMIT 1');

    do {
        $statement->execute(['name' => $name]);
        $exists = (bool) $statement->fetchColumn();
        if ($exists) {
            $suffix++;
            $name = $base . '_' . $suffix;
        }
    } while ($exists);

    return $name;
}

function status_label(string $status): string
{
    return match ($status) {
        'approved' => 'Đã duyệt',
        'rejected' => 'Bị từ chối',
        default => 'Đang xem xét',
    };
}

function status_badge(string $status): string
{
    return match ($status) {
        'approved' => 'success',
        'rejected' => 'danger',
        default => 'warning',
    };
}
