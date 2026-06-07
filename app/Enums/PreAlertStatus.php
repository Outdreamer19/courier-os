<?php

namespace App\Enums;

enum PreAlertStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case MatchedToPackage = 'matched_to_package';
    case IssueFound = 'issue_found';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::MatchedToPackage => 'Matched to Package',
            self::IssueFound => 'Issue Found',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * @return list<self>
     */
    public static function editable(): array
    {
        return [self::Submitted, self::UnderReview];
    }

    /**
     * @return list<self>
     */
    public static function cancellable(): array
    {
        return [self::Submitted, self::UnderReview];
    }

    public function isEditable(): bool
    {
        return in_array($this, self::editable(), true);
    }

    public function isCancellable(): bool
    {
        return in_array($this, self::cancellable(), true);
    }
}
