<?php
declare(strict_types=1);

function format_submission_value(?string $value, string $fieldType): string
{
    if ($value === null || $value === '') {
        return '—';
    }

    if ($fieldType === 'checkbox') {
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $items = array_values(array_filter(array_map('strval', $decoded), 'strlen'));
            return $items ? implode(', ', $items) : '—';
        }
    }

    return $value;
}

if (!function_exists('status_label')) {
    function status_label(string $status): string
    {
        return match ($status) {
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            default => 'Chờ duyệt',
        };
    }
}

if (!function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        return match ($status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning text-dark',
        };
    }
}

function valid_date(string $date): bool
{
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

function submission_filters(array $source): array
{
    $status = isset($source['status']) && is_string($source['status']) ? $source['status'] : '';
    $dateFrom = isset($source['date_from']) && is_string($source['date_from']) ? $source['date_from'] : '';
    $dateTo = isset($source['date_to']) && is_string($source['date_to']) ? $source['date_to'] : '';
    $search = isset($source['q']) && is_string($source['q']) ? trim($source['q']) : '';

    return [
        'status' => in_array($status, ALLOWED_SUBMISSION_STATUSES, true) ? $status : '',
        'date_from' => valid_date($dateFrom) ? $dateFrom : '',
        'date_to' => valid_date($dateTo) ? $dateTo : '',
        'q' => mb_substr($search, 0, 100),
    ];
}

function submission_filter_sql(array $filters, array &$params): string
{
    $conditions = [];
    $params = [];

    if ($filters['status'] !== '') {
        $conditions[] = 'fs.status = :status';
        $params['status'] = $filters['status'];
    }
    if ($filters['date_from'] !== '') {
        $conditions[] = 'fs.submitted_at >= :date_from';
        $params['date_from'] = $filters['date_from'] . ' 00:00:00';
    }
    if ($filters['date_to'] !== '') {
        $conditions[] = 'fs.submitted_at < DATE_ADD(:date_to, INTERVAL 1 DAY)';
        $params['date_to'] = $filters['date_to'] . ' 00:00:00';
    }
    if ($filters['q'] !== '') {
        $conditions[] = 'EXISTS (
            SELECT 1 FROM form_submission_values fsv_search
            WHERE fsv_search.submission_id = fs.id
              AND fsv_search.value LIKE :search
        )';
        $params['search'] = '%' . $filters['q'] . '%';
    }

    return $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
}

function query_string(array $parameters): string
{
    $filtered = array_filter($parameters, static fn ($value): bool => $value !== '' && $value !== null);
    return http_build_query($filtered);
}
