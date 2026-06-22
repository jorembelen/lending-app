<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Borrower;
use App\Models\BorrowerAccount;
use App\Models\BorrowerTierHistory;
use App\Models\HolidayCalendar;
use App\Models\Loan;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTier;
use App\Models\OtpRequest;
use App\Models\Payment;
use App\Models\RatePreset;
use App\Models\RebateGrant;
use App\Models\RebateRule;
use App\Models\ScheduleItem;
use App\Models\User;
use App\Services\LoanScheduleService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BorrowerLifecycleSeeder extends Seeder
{
    private LoanScheduleService $scheduleService;
    private array $collectors;
    private array $staff;
    private User $admin;
    private LoyaltyTier $standardTier;
    private LoyaltyTier $trustedTier;
    private LoyaltyTier $preferredTier;
    private RatePreset $standardPreset;
    private RatePreset $expressPreset;
    private RatePreset $extendedPreset;
    private RebateRule $universalRule;
    private RebateRule $trustedRule;
    private RebateRule $preferredRule;

    // Base GPS coordinates for a Philippine barangay (QC area)
    private const BASE_LAT = 14.6760;
    private const BASE_LNG = 121.0437;

    private array $bucketCounts = [
        'on_time'             => 0,
        'with_arrears'        => 0,
        'completed_threshold' => 0,
        'completed_late'      => 0,
        'repeat'              => 0,
        'new_no_loan'         => 0,
        'voided_payment'      => 0,
    ];

    public function run(): void
    {
        $this->scheduleService = new LoanScheduleService();

        $this->collectors    = User::role('collector')->get()->all();
        $this->staff         = User::role('staff')->get()->all();
        $this->admin         = User::role('admin')->first();
        $this->standardTier  = LoyaltyTier::where('name', 'Standard')->firstOrFail();
        $this->trustedTier   = LoyaltyTier::where('name', 'Trusted')->firstOrFail();
        $this->preferredTier = LoyaltyTier::where('name', 'Preferred')->firstOrFail();
        $this->standardPreset = RatePreset::where('name', 'Standard 20/1000/60')->firstOrFail();
        $this->expressPreset  = RatePreset::where('name', 'Express 25/1000/30')->firstOrFail();
        $this->extendedPreset = RatePreset::where('name', 'Extended 18/1000/90')->firstOrFail();
        $this->universalRule = RebateRule::whereNull('loyalty_tier_id')->where('percent_of_interest', 5)->firstOrFail();
        $this->trustedRule   = RebateRule::where('loyalty_tier_id', $this->trustedTier->id)->firstOrFail();
        $this->preferredRule = RebateRule::where('loyalty_tier_id', $this->preferredTier->id)->firstOrFail();

        // Bucket 1: Active, on-time (12)
        for ($i = 0; $i < 12; $i++) {
            $disbursedDaysAgo = rand(15, 55);
            $borrower = $this->makeBorrower();
            $loan = $this->createLoan($borrower, $this->standardPreset, now()->subDays($disbursedDaysAgo));
            $this->payAllDueItems($loan, missedIndices: []);
            $this->bucketCounts['on_time']++;
        }

        // Bucket 2: Active, with arrears (8)
        for ($i = 0; $i < 8; $i++) {
            $disbursedDaysAgo = rand(20, 55);
            $borrower = $this->makeBorrower();
            $loan = $this->createLoan($borrower, $this->standardPreset, now()->subDays($disbursedDaysAgo));
            $dueCount = $loan->scheduleItems()->where('due_date', '<=', today())->count();
            // Pick 3–10 non-consecutive missed indices from the due items
            $missedCount = min(rand(3, 10), max(0, $dueCount - 1));
            $missedIndices = $dueCount > 0
                ? collect(range(0, $dueCount - 1))->shuffle()->take($missedCount)->sort()->values()->all()
                : [];
            $this->payAllDueItems($loan, $missedIndices);
            $this->bucketCounts['with_arrears']++;
        }

        // Bucket 3: Completed within threshold (10), 6+ with rebate grants
        for ($i = 0; $i < 10; $i++) {
            $missedDays  = $i < 5 ? rand(0, 2) : rand(3, 5); // mix of preferred & trusted qualifiers
            $tier        = $missedDays <= 2 ? $this->preferredTier : $this->trustedTier;
            $rule        = $missedDays <= 2 ? $this->preferredRule : $this->trustedRule;
            $disbursedAt = now()->subDays(rand(90, 150));
            $borrower    = $this->makeBorrower();
            $loan        = $this->createLoan($borrower, $this->standardPreset, $disbursedAt);

            $this->payAllScheduleItems($loan, missedCount: $missedDays);
            $this->closeLoan($loan, $missedDays);
            $this->upgradeTier($borrower, $loan, $tier);

            if ($i < 6) {
                $rebateStatus = ['pending_approval', 'approved', 'applied'][$i % 3];
                $this->createRebateGrant($loan, $borrower, $rule, $rebateStatus);
            }

            $this->awardPoints($borrower, $loan, 'loan_completed', 50);
            $this->bucketCounts['completed_threshold']++;
        }

        // Bucket 4: Completed late / defaulted (6)
        for ($i = 0; $i < 6; $i++) {
            $disbursedAt = now()->subDays(rand(100, 180));
            $borrower    = $this->makeBorrower();

            if ($i < 3) {
                // Completed with high missed-day count — no tier upgrade, no rebate
                $missedDays = rand(10, 20);
                $loan = $this->createLoan($borrower, $this->standardPreset, $disbursedAt);
                $this->payAllScheduleItems($loan, missedCount: $missedDays);
                $this->closeLoan($loan, $missedDays);
            } else {
                // Defaulted — some payments made, loan ended with remaining arrears
                $loan = $this->createLoan($borrower, $this->expressPreset, $disbursedAt);
                $items = $loan->scheduleItems()->orderBy('sequence_number')->get();
                // Pay only about 60% of items
                $payCount = (int) floor($items->count() * 0.6);
                foreach ($items->take($payCount) as $item) {
                    $this->recordPayment($loan, $item, (float) $item->amount_due);
                }
                foreach ($items->skip($payCount) as $item) {
                    $item->update(['status' => 'missed']);
                }
                $loan->update([
                    'status'                => 'defaulted',
                    'closed_at'             => now()->subDays(rand(10, 30))->toDateString(),
                    'missed_days_at_closure' => $items->count() - $payCount,
                ]);
            }

            $this->bucketCounts['completed_late']++;
        }

        // Bucket 5: Repeat borrowers (8) — 2–3 historical loans + 1 active, tier progression
        for ($i = 0; $i < 8; $i++) {
            $borrower = $this->makeBorrower();
            $currentTier = $this->standardTier;
            $borrower->update(['current_tier_id' => $currentTier->id]);

            // Past loan 1 — good performance → Standard
            $loan1At = now()->subDays(rand(400, 500));
            $loan1   = $this->createLoan($borrower, $this->standardPreset, $loan1At);
            $missedL1 = rand(0, 3);
            $this->payAllScheduleItems($loan1, missedCount: $missedL1);
            $this->closeLoan($loan1, $missedL1);
            if ($missedL1 <= $this->trustedTier->max_missed_days_to_qualify) {
                $currentTier = $this->trustedTier;
                $this->upgradeTier($borrower, $loan1, $this->trustedTier);
            }

            // Past loan 2 — better performance → Trusted or Preferred
            if ($i < 4 || rand(0, 1)) {
                $loan2At  = now()->subDays(rand(200, 380));
                $loan2    = $this->createLoan($borrower, $this->extendedPreset, $loan2At);
                $missedL2 = rand(0, 2);
                $this->payAllScheduleItems($loan2, missedCount: $missedL2);
                $this->closeLoan($loan2, $missedL2);
                if ($missedL2 <= $this->preferredTier->max_missed_days_to_qualify) {
                    $currentTier = $this->preferredTier;
                    $this->upgradeTier($borrower, $loan2, $this->preferredTier);
                }
                $rule = $missedL2 <= 2 ? $this->preferredRule : $this->trustedRule;
                $this->createRebateGrant($loan2, $borrower, $rule, 'applied');
                $this->awardPoints($borrower, $loan2, 'loan_completed', 100);
            }

            // Active loan 3
            $loan3At = now()->subDays(rand(10, 50));
            $loan3   = $this->createLoan($borrower, $this->standardPreset, $loan3At);
            $this->payAllDueItems($loan3, missedIndices: []);
            $borrower->update(['current_tier_id' => $currentTier->id]);

            $this->bucketCounts['repeat']++;
        }

        // Bucket 6: Brand new, no loan (4)
        for ($i = 0; $i < 4; $i++) {
            $borrower = $this->makeBorrower();
            // Create a borrower account for each
            BorrowerAccount::create([
                'borrower_id'        => $borrower->id,
                'email'              => 'borrower' . $borrower->id . '@mail.local',
                'pin_hash'           => bcrypt('1234'),
                'failed_attempts'    => 0,
                'created_by_user_id' => $this->randomStaff()->id,
            ]);
            $this->bucketCounts['new_no_loan']++;
        }

        // Bucket 7: Voided payments — pick 2 borrowers from active loans and void one payment each
        $activeLoans = Loan::where('status', 'active')
            ->has('payments')
            ->inRandomOrder()
            ->take(2)
            ->get();

        foreach ($activeLoans as $loan) {
            $payment = $loan->payments()->where('is_voided', false)->first();
            if ($payment) {
                $payment->update([
                    'is_voided'         => true,
                    'voided_by_user_id' => $this->admin->id,
                    'voided_reason'     => 'Collector submitted duplicate entry; voided by admin after reconciliation.',
                    'voided_at'         => now()->subHours(rand(1, 48)),
                ]);

                AuditLog::create([
                    'user_id'      => $this->admin->id,
                    'action'       => 'payment.voided',
                    'subject_type' => Payment::class,
                    'subject_id'   => $payment->id,
                    'description'  => 'Admin voided a payment.',
                    'metadata'     => [
                        'loan_id'       => $loan->id,
                        'amount'        => $payment->amount,
                        'voided_reason' => $payment->voided_reason,
                    ],
                ]);

                $this->bucketCounts['voided_payment']++;
            }
        }

        $this->command->info('=== BorrowerLifecycleSeeder Summary ===');
        $this->command->info("Active, on-time:             {$this->bucketCounts['on_time']} borrowers");
        $this->command->info("Active, with arrears:        {$this->bucketCounts['with_arrears']} borrowers");
        $this->command->info("Completed (within threshold):{$this->bucketCounts['completed_threshold']} borrowers");
        $this->command->info("Completed late / defaulted:  {$this->bucketCounts['completed_late']} borrowers");
        $this->command->info("Repeat borrowers:            {$this->bucketCounts['repeat']} borrowers");
        $this->command->info("Brand new (no loan):         {$this->bucketCounts['new_no_loan']} borrowers");
        $this->command->info("Voided payment examples:     {$this->bucketCounts['voided_payment']}");
        $total = array_sum(array_filter($this->bucketCounts, fn ($k) => $k !== 'voided_payment', ARRAY_FILTER_USE_KEY));
        $this->command->info("Total distinct borrowers:    {$total}");
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function makeBorrower(): Borrower
    {
        return Borrower::create([
            'full_name'    => fake()->name(),
            'phone_number' => '09' . fake()->numerify('#########'),
            'address'      => fake()->address(),
            'qr_reference' => Str::uuid()->toString(),
        ]);
    }

    private function createLoan(Borrower $borrower, RatePreset $preset, Carbon $disbursedAt): Loan
    {
        $principals = [2000, 3000, 5000, 8000, 10000, 15000, 20000];
        $principal  = $principals[array_rand($principals)];
        $rate       = (float) $preset->rate_per_1000;
        $termDays   = $preset->term_days;
        $daily      = round(($principal / 1000) * $rate, 2);
        $total      = round($daily * $termDays, 2);

        $loan = Loan::create([
            'borrower_id'           => $borrower->id,
            'rate_preset_id'        => $preset->id,
            'principal'             => $principal,
            'rate_per_1000_locked'  => $rate,
            'term_days_locked'      => $termDays,
            'daily_installment'     => $daily,
            'total_payable'         => $total,
            'disbursed_at'          => $disbursedAt->toDateString(),
            'disbursed_by_user_id'  => $this->randomStaff()->id,
            'status'                => 'active',
        ]);

        // Generate full schedule
        $dates = $this->scheduleService->generateSchedule($disbursedAt, $termDays);
        $items = [];
        foreach ($dates as $seq => $date) {
            $items[] = [
                'loan_id'         => $loan->id,
                'sequence_number' => $seq + 1,
                'due_date'        => $date->toDateString(),
                'amount_due'      => $daily,
                'amount_paid'     => 0,
                'status'          => 'pending',
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }
        ScheduleItem::insert($items);

        return $loan;
    }

    /**
     * Pay all schedule items that are due as of today, skipping specific indices (0-based) as missed.
     */
    private function payAllDueItems(Loan $loan, array $missedIndices): void
    {
        $items = $loan->scheduleItems()
            ->where('due_date', '<=', today())
            ->orderBy('sequence_number')
            ->get();

        foreach ($items as $i => $item) {
            if (in_array($i, $missedIndices)) {
                $item->update(['status' => 'missed']);
            } else {
                $this->recordPayment($loan, $item, (float) $item->amount_due);
            }
        }
    }

    /**
     * Pay all schedule items in a completed loan, with $missedCount items randomly marked as missed.
     */
    private function payAllScheduleItems(Loan $loan, int $missedCount): void
    {
        $items = $loan->scheduleItems()->orderBy('sequence_number')->get();
        $total = $items->count();

        // Spread missed items across the term (not consecutive)
        $missedIndices = collect(range(0, $total - 1))->shuffle()->take($missedCount)->all();

        foreach ($items as $i => $item) {
            if (in_array($i, $missedIndices)) {
                $item->update(['status' => 'missed']);
            } else {
                // Past-dated payment for closed loans
                $collectedAt = Carbon::parse($item->due_date)->addHours(rand(8, 17));
                $this->recordPaymentAt($loan, $item, (float) $item->amount_due, $collectedAt);
            }
        }
    }

    private function recordPayment(Loan $loan, ScheduleItem $item, float $amount): void
    {
        $collectedAt = Carbon::parse($item->due_date)->addHours(rand(8, 17));
        $this->recordPaymentAt($loan, $item, $amount, $collectedAt);
    }

    private function recordPaymentAt(Loan $loan, ScheduleItem $item, float $amount, Carbon $collectedAt): void
    {
        Payment::create([
            'loan_id'            => $loan->id,
            'collector_user_id'  => $this->randomCollector()->id,
            'amount'             => $amount,
            'collected_at'       => $collectedAt,
            'recorded_at'        => $collectedAt->copy()->addSeconds(rand(5, 120)),
            'latitude'           => self::BASE_LAT + (rand(-200, 200) / 10000),
            'longitude'          => self::BASE_LNG + (rand(-200, 200) / 10000),
            'device_identifier'  => 'DEV-' . strtoupper(Str::random(8)),
            'idempotency_key'    => Str::uuid()->toString(),
            'is_voided'          => false,
        ]);

        $newPaid = (float) $item->amount_paid + $amount;
        $newStatus = $newPaid >= (float) $item->amount_due
            ? 'paid'
            : 'partially_paid';

        $item->update(['amount_paid' => $newPaid, 'status' => $newStatus]);
    }

    private function closeLoan(Loan $loan, int $missedDays): void
    {
        $loan->update([
            'status'                 => 'completed',
            'closed_at'             => now()->subDays(rand(1, 10))->toDateString(),
            'missed_days_at_closure' => $missedDays,
        ]);
    }

    private function upgradeTier(Borrower $borrower, Loan $loan, LoyaltyTier $newTier): void
    {
        $borrower->update(['current_tier_id' => $newTier->id]);

        BorrowerTierHistory::create([
            'borrower_id'    => $borrower->id,
            'loyalty_tier_id' => $newTier->id,
            'loan_id'        => $loan->id,
            'changed_at'     => now()->subDays(rand(0, 5)),
            'note'           => "Upgraded to {$newTier->name} at loan #{$loan->id} closure.",
        ]);

        AuditLog::create([
            'user_id'      => $this->admin->id,
            'action'       => 'borrower.tier_changed',
            'subject_type' => Borrower::class,
            'subject_id'   => $borrower->id,
            'description'  => "Borrower promoted to {$newTier->name} tier.",
            'metadata'     => ['loan_id' => $loan->id, 'new_tier' => $newTier->name],
        ]);
    }

    private function createRebateGrant(Loan $loan, Borrower $borrower, RebateRule $rule, string $status): void
    {
        $interest = round((float) $loan->total_payable - (float) $loan->principal, 2);
        $rebate   = round($interest * ((float) $rule->percent_of_interest / 100), 2);

        $approvedBy = $status !== 'pending_approval' ? $this->admin : null;
        $approvedAt = $approvedBy ? now()->subDays(rand(1, 5)) : null;

        $appliedToLoanId = null;
        $appliedAt       = null;
        if ($status === 'applied') {
            // Link to another active loan of the same borrower if exists
            $nextLoan = $borrower->loans()->where('id', '!=', $loan->id)->where('status', 'active')->first();
            $appliedToLoanId = $nextLoan?->id;
            $appliedAt       = $approvedAt?->copy()->addDays(rand(1, 3));
        }

        $grant = RebateGrant::create([
            'loan_id'             => $loan->id,
            'borrower_id'         => $borrower->id,
            'rebate_rule_id'      => $rule->id,
            'interest_amount'     => $interest,
            'rebate_amount'       => $rebate,
            'status'              => $status,
            'approved_by_user_id' => $approvedBy?->id,
            'approved_at'         => $approvedAt,
            'applied_to_loan_id'  => $appliedToLoanId,
            'applied_at'          => $appliedAt,
        ]);

        if ($status === 'approved' || $status === 'applied') {
            AuditLog::create([
                'user_id'      => $this->admin->id,
                'action'       => 'rebate.approved',
                'subject_type' => RebateGrant::class,
                'subject_id'   => $grant->id,
                'description'  => "Rebate of ₱{$rebate} approved for borrower #{$borrower->id}.",
                'metadata'     => ['loan_id' => $loan->id, 'rebate_amount' => $rebate],
            ]);
        }
    }

    private function awardPoints(Borrower $borrower, Loan $loan, string $reason, int $points): void
    {
        LoyaltyPoint::create([
            'borrower_id' => $borrower->id,
            'points'      => $points,
            'reason'      => $reason,
            'loan_id'     => $loan->id,
            'awarded_at'  => now()->subDays(rand(0, 5)),
        ]);
    }

    private function randomCollector(): User
    {
        return $this->collectors[array_rand($this->collectors)];
    }

    private function randomStaff(): User
    {
        $all = array_merge($this->staff, [$this->admin]);
        return $all[array_rand($all)];
    }
}
