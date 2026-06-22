<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class BaseComponent extends Component
{
	use WithPagination;

    public $search;
    public $hasSearch = false;
    public $perPage = 10;
	public $sortColumnName = 'created_at';
    public $sortDirection = 'desc';
    protected $queryString = ['search'];
	protected $paginationTheme = 'bootstrap';

    public function search() 
    {
        $this->hasSearch = true;
        $this->validate([
            'search' => 'required|min:4'
        ],['search.min' => 'Minimum character is 4.']);
    }
    
    public function clearSearch() 
    {
        $this->hasSearch = false;
        $this->search = null;
        $this->resetErrorBag();
    }

    public function updated($property)
    {
        if ($property === 'search') {
            $this->resetPage();
        }
    }

	public function sortBy($columnName)
    {
        if ($this->sortColumnName === $columnName) {
            $this->sortDirection = $this->swapSortDirection();
        } else {
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
        $this->sortColumnName = $columnName;
    }

    public function swapSortDirection()
    {
        return $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

}
