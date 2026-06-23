<?php

namespace App\Livewire\Admin;

use App\Models\Borrower;
use Livewire\Component;
use Livewire\WithPagination;

class BorrowersListComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all'; // all | active | completed | overdue

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function getStatsProperty(): array
    {
        return [
            'total'     => Borrower::count(),
            'active'    => Borrower::whereHas('loans', fn ($q) => $q->where('status', 'active'))->count(),
            'overdue'   => Borrower::whereHas('loans', fn ($q) => $q->where('status', 'overdue'))->count(),
            'completed' => Borrower::whereHas('loans', fn ($q) => $q->where('status', 'completed'))->count(),
        ];
    }

    public function render()
    {
        $borrowers = Borrower::with(['loans' => fn ($q) => $q->latest()->limit(1)])
            ->when($this->search, fn ($q) =>
                $q->where('full_name', 'like', "%{$this->search}%")
                  ->orWhere('phone_number', 'like', "%{$this->search}%")
                  ->orWhere('borrower_code', 'like', "%{$this->search}%")
            )
            ->when($this->filter !== 'all', fn ($q) =>
                $q->whereHas('loans', fn ($q) => $q->where('status', $this->filter))
            )
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.admin.borrowers-list-component', compact('borrowers'))
            ->layout('components.layout.admin-shell', [
                'title'     => 'Borrowers',
                'activeNav' => 'borrowers',
            ]);
    }
}
