<?php

namespace App\Support;

class C4ChangeRequestStatuses
{
    public const DRAFT = 'draft';

    public const PENDING_REVIEW = 'pending_review';

    public const CHANGES_REQUESTED = 'changes_requested';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    /** @var list<string> */
    public const ALL = [
        self::DRAFT,
        self::PENDING_REVIEW,
        self::CHANGES_REQUESTED,
        self::APPROVED,
        self::REJECTED,
    ];

    /** @var array<string, string> */
    public const LABELS = [
        self::DRAFT => 'Draft',
        self::PENDING_REVIEW => 'Pending Review',
        self::CHANGES_REQUESTED => 'Changes Requested',
        self::APPROVED => 'Approved',
        self::REJECTED => 'Rejected',
    ];

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function validationRule(): string
    {
        return 'required|in:'.implode(',', self::ALL);
    }

    public static function badgeClass(string $status): string
    {
        return match ($status) {
            self::APPROVED => 'success',
            self::PENDING_REVIEW => 'warning',
            self::CHANGES_REQUESTED => 'info',
            self::REJECTED => 'danger',
            default => 'secondary',
        };
    }
}
