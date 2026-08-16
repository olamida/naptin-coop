<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Flexible Loan Policy Configuration
    |--------------------------------------------------------------------------
    |
    | NAPTIN Cooperative Regulatory Framework:
    | - This is NOT a CBN-licensed Microfinance Bank (MFB) or Commercial Bank.
    | - It is a Staff Cooperative/Thrift Society registered under Cooperative
    |   Societies Act Cap C21 LFN 2004 and respective State Cooperative Law.
    | - Funds managed are closed-loop internal member savings, not public deposits.
    | - Therefore CBN Prudential Guidelines for MFBs including Single Obligor
    |   Limit (Section 3.2 of CBN MFB Framework) does NOT apply to internal
    |   loan approvals.
    | - Lending limits are governed by:
    |   1. Cooperative Bye-Laws approved by Director of Cooperatives
    |   2. Loan Policy approved by Annual General Meeting (AGM)
    |   3. EXCO discretion for special cases with audit trail
    |   4. Employer deduction agreement (typically 1/3 of salary) with IPPIS/Employer
    |   5. Member affordability and savings multiplier principle (internal risk rule)
    |
    | - We retain CBN-inspired GOOD PRACTICES that are universally good accounting:
    |   - Double-entry ledger, Trial Balance, P&L, Balance Sheet (IFRS for SMEs)
    |   - Loan Loss Provisioning (IFRS 9) for true financial health
    |   - Maker-Checker and Segregation of Duties (COSO)
    |   - Audit trail (ISA 230)
    |
    | - We REMOVE CBN MFB-specific prudential limits that don't apply to cooperative
    |   internal operations:
    |   - Single Obligor Limit (5% of shareholders fund) - REMOVED
    |   - Capital Adequacy Ratio - Not applicable (we track General Reserve per
    |     Cooperative Act 25% of profit)
    |   - Liquidity Ratio - Not applicable in same way (we track cash flow)
    |
    */

    'default_loan_multiplier' => [
        'regular' => 2.0,
        'emergency' => 1.0,
        'educational' => 2.0,
        'special' => 3.0,
    ],

    'max_loan_multiplier' => [
        'regular' => 4.0,
        'emergency' => 2.0,
        'educational' => 3.0,
        'special' => 5.0,
    ],

    'guarantor_cap' => 500000,

    'payroll_deduction' => [
        'default_max_percent' => 33.33, // 1/3 rule - based on employer IPPIS agreement, not CBN
        'hard_max_percent' => 66.67, // Absolute max even with override - cannot take entire salary
        'retirement_override_max_percent' => 60.00, // For retiring members
        'defaulter_override_max_percent' => 50.00, // For defaulters catch-up
    ],

    'allowances' => [
        'allow_multiplier_override' => true,
        'allow_deduction_override' => true,
        'require_second_approval_above_percent' => 50.00,
    ],

    'override_reason_categories' => [
        'retirement_recovery' => 'Retirement Recovery (deduct more to recover before retirement)',
        'defaulter_catchup' => 'Defaulter Catch-up (higher deduction to clear arrears)',
        'long_service_goodwill' => 'Long Service Goodwill (EXCO discretion for loyal members)',
        'emergency_medical' => 'Emergency Medical (urgent health needs)',
        'exco_discretion' => 'EXCO Discretion (case-by-case approval)',
        'agm_approval' => 'AGM Approved (ratified by Annual General Meeting)',
        'other' => 'Other (specify in details)',
    ],
];
