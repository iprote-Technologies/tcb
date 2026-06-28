<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Enums;

enum AccountType: string
{
    case Collection = 'collection';
    case Disbursement = 'disbursement';
    case Settlement = 'settlement';
    case LoanCollection = 'loan_collection';
    case Savings = 'savings';
    case GeneralCollection = 'general_collection';

    public function label(): string
    {
        return match ($this) {
            self::Collection => 'Collection',
            self::Disbursement => 'Disbursement',
            self::Settlement => 'Settlement',
            self::LoanCollection => 'Loan Collection',
            self::Savings => 'Savings',
            self::GeneralCollection => 'General Collection',
        };
    }
}
