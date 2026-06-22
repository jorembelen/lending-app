<?php

namespace App\Livewire\Admin;

use App\Livewire\BaseComponent;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;

class LogsComponent extends BaseComponent
{
    public $search = '';
    public $user_filter = '';
    public $subject_type_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $sortColumnName = 'created_at';
    public $sortDirection = 'desc';
    public $showFilters = false;
    public $selectedLogId;
    public $exportFormat = 'csv';
    
    // Bulk actions
    public $selectedLogs = [];
    public $selectAll = false;
    public $showBulkActions = false;
    
    // Cached data properties
    public $users = [];
    public $subjectTypes = [];
    
    // Performance optimizations
    public $debounceMs = 300;
    public $perPage = 25;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'user_filter' => ['except' => ''],
        'subject_type_filter' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => '']
    ];

    public function mount()
    {
        $this->perPage = 25;
        $this->loadFilterData();
    }

    /**
     * Load filter data once during component initialization
     */
    protected function loadFilterData()
    {
        // Cache filter data for better performance
        $this->users = Cache::remember('logs_filter_users', 300, function () {
            return User::whereIn('id', Activity::whereNotNull('causer_id')->distinct()->pluck('causer_id'))
                      ->orderBy('name')
                      ->get(['id', 'name'])
                      ->toArray();
        });

        $this->subjectTypes = Cache::remember('logs_filter_subject_types', 300, function () {
            return Activity::whereNotNull('subject_type')
                          ->distinct()
                          ->orderBy('subject_type')
                          ->pluck('subject_type')
                          ->toArray();
        });
    }

    public function render()
    {
        $logs = $this->getLogsQuery();
        
        return view('livewire.admin.logs-component', [
            'logs' => $logs,
            'users' => $this->users,
            'subjectTypes' => $this->subjectTypes
        ]);
    }

    /**
     * Optimized logs query with better performance
     */
    protected function getLogsQuery()
    {
        $query = Activity::query()
            ->with(['causer:id,name']) // Only load needed fields
            ->select(['id', 'description', 'properties', 'subject_type', 'causer_type', 'causer_id', 'created_at']);

        // Apply search filter
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('description', 'like', $searchTerm)
                  ->orWhere('subject_type', 'like', $searchTerm)
                  ->orWhereRaw('JSON_EXTRACT(properties, "$") LIKE ?', [$searchTerm]);
                  
                // Only join causer table if really needed for search
                if (strlen($this->search) > 2) {
                    $q->orWhereHas('causer', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', $searchTerm);
                    });
                }
            });
        }

        // Apply filters efficiently
        if (!empty($this->user_filter)) {
            $query->where('causer_id', $this->user_filter);
        }

        if (!empty($this->subject_type_filter)) {
            $query->where('subject_type', $this->subject_type_filter);
        }

        if (!empty($this->date_from)) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }

        if (!empty($this->date_to)) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        // Apply sorting
        $query->orderBy($this->sortColumnName, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    /**
     * Handle property updates with debouncing for search
     */
    public function updated($property)
    {
        if (in_array($property, ['search', 'user_filter', 'subject_type_filter', 'date_from', 'date_to'])) {
            $this->resetPage();
        }
        
        // Clear selection when filters change
        if ($property !== 'selectedLogs' && $property !== 'selectAll') {
            $this->clearSelection();
        }
    }

    /**
     * Clear all filters and reset component state
     */
    public function clearFilters()
    {
        $this->reset(['search', 'user_filter', 'subject_type_filter', 'date_from', 'date_to']);
        $this->resetPage();
        $this->clearSelection();
        $this->showFilters = false;
        return session('All filters have been cleared.');
    }

    /**
     * Toggle filters panel
     */
    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    /**
     * View log details in modal (compatible with x-modal component)
     */
    public function viewLogDetails($id)
    {
        $this->selectedLogId = $id;
        $this->dispatch('logDetailsModal');
    }

    /**
     * Close modal (required by x-modal component)
     */
    public function close()
    {
        $this->selectedLogId = null;
        $this->dispatch('hide-modal');
    }

    /**
     * Submit method (required by x-modal component but not used for view-only modal)
     */
    public function submit()
    {
        // This modal is view-only, so just close it
        $this->close();
    }

    /**
     * Get selected log for modal display
     */
    public function getSelectedLogProperty()
    {
        if ($this->selectedLogId) {
            return Activity::with('causer:id,name')->find($this->selectedLogId);
        }
        return null;
    }

    /**
     * Format log properties for display
     */
    public function formatLogProperties($log)
    {
        $properties = $log->properties ?? [];
        
        if (empty($properties)) {
            return '<span class="text-muted small">No additional data</span>';
        }

        $formatted = [];
        
        // Handle different log types
        if ($log->event === 'updated' && isset($properties['old']) && isset($properties['attributes'])) {
            $changes = array_diff_assoc($properties['attributes'], $properties['old']);
            foreach ($changes as $key => $newValue) {
                $oldValue = $properties['old'][$key] ?? 'N/A';
                $formatted[] = "<strong>{$key}:</strong> <span class='text-muted'>{$oldValue}</span> → <span class='text-success'>{$newValue}</span>";
            }
        } elseif (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $formatted[] = "<strong>{$key}:</strong> {$value}";
            }
        } else {
            // Handle custom log format from our ActivityLogService
            foreach ($properties as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $formatted[] = "<strong>{$key}:</strong> {$value}";
            }
        }

        return !empty($formatted) ? implode('<br>', array_slice($formatted, 0, 3)) : '<span class="text-muted small">No data</span>';
    }

    /**
     * Handle column sorting
     */
    public function sortBy($columnName)
    {
        if ($this->sortColumnName === $columnName) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
        $this->sortColumnName = $columnName;
    }

    // Bulk Actions
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedLogs = $this->getLogsQuery()->pluck('id')->toArray();
        } else {
            $this->selectedLogs = [];
        }
        $this->showBulkActions = !empty($this->selectedLogs);
    }

    public function updatedSelectedLogs()
    {
        $this->showBulkActions = !empty($this->selectedLogs);
        
        // Update selectAll state
        $currentPageLogs = $this->getLogsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($currentPageLogs) && 
                          count(array_intersect($this->selectedLogs, $currentPageLogs)) === count($currentPageLogs);
    }

    #[On('confirmBulkDelete')]
    public function confirmBulkDelete()
    {
        if (empty($this->selectedLogs)) {
            $this->showErrorAlert('No logs selected for deletion.');
            return;
        }

        $count = count($this->selectedLogs);
        $this->dispatch('bulk:confirm', [
            'type' => 'warning',
            'message' => 'Delete Multiple Logs',
            'text' => "Are you sure you want to delete {$count} log entries? This action cannot be undone.",
            'count' => $count
        ]);
    }

    public function confirmSingleDelete($id)
    {
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'message' => 'Are you sure?',
            'text' => 'Are you sure? Please confirm to proceed.',
            'id' => $id
        ]);
    }

    #[On('bulkDeleteLogs')]
    public function bulkDeleteLogs()
    {
        if (empty($this->selectedLogs)) {
            $this->showErrorAlert('No logs selected for deletion.');
            return;
        }

        try {
            $deletedCount = Activity::whereIn('id', $this->selectedLogs)->delete();
            $this->clearSelection();
            $this->resetPage();
            return session()->flash('success', "Successfully deleted {$deletedCount} log entries.");

        } catch (\Exception $e) {
            $this->showErrorAlert('An error occurred while deleting logs: ' . $e->getMessage());
        }
    }

    #[On('deleteSingleLog')]
    public function deleteSingleLog($id)
    {
        try {
            $log = Activity::findOrFail($id);
            $log->delete();
            
            // Remove from selection if selected
            $this->selectedLogs = array_diff($this->selectedLogs, [$id]);
            $this->showBulkActions = !empty($this->selectedLogs);

            return session()->flash('success', 'Log entry deleted successfully.');

        } catch (\Exception $e) {
            $this->showErrorAlert('An error occurred while deleting the log: ' . $e->getMessage());
        }
    }

    #[On('clearSelection')]
    public function clearSelection()
    {
        $this->selectedLogs = [];
        $this->selectAll = false;
        $this->showBulkActions = false;
    }

    /**
     * Handle deletion cancellation callback
     */
    #[On('deleteCancelled')]
    public function deleteCancelled()
    {
        // Optional: Add any logic needed when deletion is cancelled
        // For now, just log that deletion was cancelled
    }

    // Helper methods for alerts
    protected function showSuccessAlert($message)
    {
        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => $message
        ]);
    }

    protected function showErrorAlert($message)
    {
        $this->dispatch('show-alert', [
            'type' => 'error',
            'message' => $message
        ]);
    }
}
