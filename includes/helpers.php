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
            return normalize_field_options($decoded);
        }
    }

    $lines = preg_split('/\r\n|\r|\n/', $options);
    return normalize_field_options(is_array($lines) ? $lines : []);
}

function normalize_field_options(array $options): array
{
    $values = [];
    $seen = [];

    foreach ($options as $option) {
        $value = trim((string) $option);
        if ($value === '') {
            continue;
        }

        $key = mb_strtolower($value);
        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $values[] = $value;
    }

    return $values;
}

function encode_field_options(string $options): ?string
{
    $values = field_options($options);
    return $values === [] ? null : json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function unique_field_name(object $pdo, string $label): string
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

function recruitment_form_closed(): bool
{
    require_once __DIR__ . '/editable.php';
    $contents = editable_contents();
    return ($contents['recruitment_form_closed'] ?? '0') === '1';
}

function recruitment_closed_message(): string
{
    require_once __DIR__ . '/editable.php';
    $contents = editable_contents();
    $message = trim($contents['recruitment_closed_message'] ?? '');

    return $message !== ''
        ? $message
        : 'CIT hiện đã đóng form tuyển thành viên. Hẹn gặp bạn ở đợt tuyển tiếp theo.';
}

if (!function_exists('status_label')) {
    function status_label(string $status): string
    {
        return match ($status) {
            'approved' => 'Đã duyệt',
            'rejected' => 'Bị từ chối',
            default => 'Đang xem xét',
        };
    }
}

if (!function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        return match ($status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }
}

function get_site_logo(): string
{
    require_once __DIR__ . '/editable.php';
    $contents = editable_contents();
    return $contents['site_logo'] ?? 'assets/images/cit/logoclb.png';
}

function get_logo_url(string $baseDir = ''): string
{
    $logo = get_site_logo();
    if ($baseDir !== '' && !preg_match('/^(https?:|\/)/', $logo)) {
        return $baseDir . $logo;
    }
    return $logo;
}

function get_hero_bg(): string
{
    require_once __DIR__ . '/editable.php';
    $contents = editable_contents();
    return $contents['hero_bg'] ?? 'assets/images/cit/cit-cover.webp';
}

function hex_to_rgb(string $hex): string
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return "{$r}, {$g}, {$b}";
}
