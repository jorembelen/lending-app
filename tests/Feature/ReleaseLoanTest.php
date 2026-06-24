<?php

namespace Tests\Feature;

use App\Livewire\Admin\ReleaseNewLoanComponent;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\LoyaltyTier;
use App\Models\RatePreset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReleaseLoanTest extends TestCase
{
    use RefreshDatabase;

    private User       $admin;
    private User       $collector;
    private Borrower   $borrower;
    private RatePreset $preset;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'collector', 'borrower'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->admin = User::factory()->create(['name' => 'Admin User', 'status' => 1]);
        $this->admin->assignRole('admin');

        $this->collector = User::factory()->create(['name' => 'Collector User', 'status' => 1]);
        $this->collector->assignRole('collector');

        $tier = LoyaltyTier::create([
            'name'                       => 'Standard',
            'rank'                       => 1,
            'max_missed_days_to_qualify' => 999,
            'loan_ceiling_multiplier'    => null,
            'rate_discount_per_1000'     => 0,
            'priority_reloan'            => false,
        ]);

        $this->borrower = Borrower::create([
            'full_name'       => 'Maria Santos',
            'phone_number'    => '09181234567',
            'address'         => '123 Test St',
            'qr_reference'    => Str::uuid(),
            'current_tier_id' => $tier->id,
        ]);

        $this->preset = RatePreset::create([
            'name'          => 'Standard 20/1000/60d',
            'rate_per_1000' => 20.00,
            'term_days'     => 60,
            'is_default'    => true,
            'is_active'     => true,
        ]);
    }

    // ── Auth / access ─────────────────────────────────────────────────────────

    #[Test]
    public function unauthenticated_user_is_redirected(): void
    {
        $this->get(route('admin.loans.create'))->assertRedirect();
    }

    #[Test]
    public function non_admin_cannot_access_release_loan_page(): void
    {
        $borrowerUser = User::factory()->create(['status' => 1]);
        $borrowerUser->assignRole('borrower');

        $this->actingAs($borrowerUser)
            ->get(route('admin.loans.create'))
            ->assertForbidden();
    }

    // ── Rendering ─────────────────────────────────────────────────────────────

    #[Test]
    public function release_loan_page_renders_for_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.loans.create'))
            ->assertOk();
    }

    #[Test]
    public function preset_is_auto_selected_on_mount(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->assertSet('ratePresetId', (string) $this->preset->id)
            ->assertSet('interestRate', (string) $this->preset->rate_per_1000)
            ->assertSet('termDays', (string) $this->preset->term_days);
    }

    #[Test]
    public function release_date_defaults_to_today(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->assertSet('releaseDate', today()->toDateString());
    }

    // ── Borrower search ───────────────────────────────────────────────────────

    #[Test]
    public function borrower_search_results_show_full_name_and_code(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->set('borrowerSearch', 'Maria')
            ->assertSee('Maria Santos')
            ->assertSee($this->borrower->borrower_code);
    }

    #[Test]
    public function short_search_term_returns_no_results(): void
    {
        // Under 2 chars should not trigger a search
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->set('borrowerSearch', 'M')
            ->assertDontSee('Maria Santos');
    }

    // ── selectBorrower ────────────────────────────────────────────────────────

    #[Test]
    public function select_borrower_populates_fields_without_type_error(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->call('selectBorrower', $this->borrower)
            ->assertSet('borrowerId', $this->borrower->id)
            ->assertSet('borrowerSearch', 'Maria Santos')
            ->assertHasNoErrors();
    }

    #[Test]
    public function select_borrower_with_null_does_nothing(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->call('selectBorrower', null)
            ->assertSet('borrowerId', null)
            ->assertHasNoErrors();
    }

    #[Test]
    public function mount_with_borrower_id_preselects_borrower(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class, ['borrower' => $this->borrower->id])
            ->assertSet('borrowerId', $this->borrower->id)
            ->assertSet('borrowerSearch', 'Maria Santos');
    }

    // ── selectPreset ──────────────────────────────────────────────────────────

    #[Test]
    public function select_preset_populates_rate_and_term(): void
    {
        $another = RatePreset::create([
            'name'          => 'Fast 25/1000/30d',
            'rate_per_1000' => 25.00,
            'term_days'     => 30,
            'is_default'    => false,
            'is_active'     => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->call('selectPreset', (string) $another->id)
            ->assertSet('ratePresetId', (string) $another->id)
            ->assertSet('interestRate', '25.00')
            ->assertSet('termDays', '30');
    }

    // ── Computed properties ───────────────────────────────────────────────────

    #[Test]
    public function daily_payment_computed_correctly(): void
    {
        // ₱5000 at ₱20/₱1000 → 5000/1000 × 20 = ₱100/day
        $component = Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->set('principal', '5000')
            ->set('interestRate', '20');

        $this->assertEquals(100.0, $component->instance()->dailyPayment);
    }

    #[Test]
    public function total_payable_computed_correctly(): void
    {
        // ₱100/day × 60 days = ₱6000
        $component = Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->set('principal', '5000')
            ->set('interestRate', '20')
            ->set('termDays', '60');

        $this->assertEquals(6000.0, $component->instance()->totalPayable);
    }

    #[Test]
    public function daily_payment_returns_zero_when_inputs_empty(): void
    {
        $component = Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class);

        $this->assertEquals(0.0, $component->instance()->dailyPayment);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    #[Test]
    public function save_fails_validation_without_borrower(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->set('principal', '5000')
            ->set('interestRate', '20')
            ->set('termDays', '60')
            ->set('releaseDate', today()->toDateString())
            ->call('save')
            ->assertHasErrors(['borrowerId']);
    }

    #[Test]
    public function save_fails_validation_without_preset(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->set('borrowerId', $this->borrower->id)
            ->set('ratePresetId', '')
            ->set('principal', '5000')
            ->set('interestRate', '20')
            ->set('termDays', '60')
            ->set('releaseDate', today()->toDateString())
            ->call('save')
            ->assertHasErrors(['ratePresetId']);
    }

    #[Test]
    public function save_fails_validation_without_principal(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->set('borrowerId', $this->borrower->id)
            ->set('ratePresetId', (string) $this->preset->id)
            ->set('principal', '')
            ->call('save')
            ->assertHasErrors(['principal']);
    }

    #[Test]
    public function save_fails_validation_with_nonexistent_borrower_id(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->set('borrowerId', 999999)
            ->set('ratePresetId', (string) $this->preset->id)
            ->set('principal', '5000')
            ->set('interestRate', '20')
            ->set('termDays', '60')
            ->set('releaseDate', today()->toDateString())
            ->call('save')
            ->assertHasErrors(['borrowerId']);
    }

    // ── Successful save ───────────────────────────────────────────────────────

    #[Test]
    public function valid_form_creates_loan_with_correct_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->set('borrowerId', $this->borrower->id)
            ->set('ratePresetId', (string) $this->preset->id)
            ->set('collectorId', (string) $this->collector->id)
            ->set('principal', '5000')
            ->set('interestRate', '20')
            ->set('termDays', '60')
            ->set('releaseDate', today()->toDateString())
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.borrowers'));

        $loan = Loan::first();
        $this->assertNotNull($loan);
        $this->assertEquals($this->borrower->id,   $loan->borrower_id);
        $this->assertEquals($this->preset->id,      $loan->rate_preset_id);
        $this->assertEquals(5000,                   (float) $loan->principal);
        $this->assertEquals(20.0,                   (float) $loan->rate_per_1000_locked);
        $this->assertEquals(60,                     $loan->term_days_locked);
        $this->assertEquals(100.0,                  (float) $loan->daily_installment);
        $this->assertEquals(6000.0,                 (float) $loan->total_payable);
        $this->assertEquals(today()->toDateString(), $loan->disbursed_at->toDateString());
        $this->assertEquals($this->admin->id,        $loan->disbursed_by_user_id);
        $this->assertEquals('active',               $loan->status);
    }

    #[Test]
    public function valid_form_flashes_success_message(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReleaseNewLoanComponent::class)
            ->set('borrowerId', $this->borrower->id)
            ->set('ratePresetId', (string) $this->preset->id)
            ->set('collectorId', (string) $this->collector->id)
            ->set('principal', '5000')
            ->set('interestRate', '20')
            ->set('termDays', '60')
            ->set('releaseDate', today()->toDateString())
            ->call('save');

        $this->assertEquals('Loan released successfully.', session('success'));
    }
}
