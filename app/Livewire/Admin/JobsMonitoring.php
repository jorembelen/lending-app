<?php

namespace App\Livewire\Admin;

use App\Livewire\BaseComponent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Support\Str;

class JobsMonitoring extends BaseComponent
{
    use WithPagination;

    public $activeTab = 'pending';
    public $isLoading = false;
    public $stats = [];
    public $refreshInterval = 30000; // 30 seconds
    
    // Multiple selection properties
    public $selectedJobs = [];
    public $selectAll = false;
    public $showBulkActions = false;

    protected $casts = [
        'selectedJobs' => 'array',
        'selectAll' => 'boolean',
        'showBulkActions' => 'boolean'
    ];

    // Computed property for bulk actions visibility
    public function getShowBulkActionsProperty()
    {
        return !empty($this->selectedJobs);
    }

    public function mount()
    {
        $this->loadStats();
    }

    public function render()
    {
        $data = [];
        
        if ($this->activeTab === 'pending') {
            $data['jobs'] = $this->getPendingJobs();
        } else {
            $data['jobs'] = $this->getFailedJobs();
        }

        return view('livewire.admin.jobs-monitoring', $data);
    }

    #[On('refreshJobs')]
    public function loadStats()
    {
        $this->stats = [
            'pending' => DB::table('jobs')->where('attempts', 0)->count(),
            'processing' => DB::table('jobs')->where('attempts', '>', 0)->count(),
            'failed' => DB::table('failed_jobs')->count(),
            'total' => DB::table('jobs')->count()
        ];
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->clearSelection(); // Clear selection when switching tabs
        $this->loadStats();
    }

    public function getPendingJobs()
    {
        $query = DB::table('jobs')
            ->where('attempts', 0)
            ->orderBy('created_at', 'desc');

        $jobs = $query->paginate(15);
        
        // Transform the items
        $items = collect($jobs->items())->map(function ($job) {
            $jobData = $this->parseJobPayload($job->payload);
            
            return (object) [
                'id' => $job->id,
                'queue' => $job->queue,
                'job_type' => $jobData['type'] ?? 'Unknown',
                'recipients' => $jobData['recipients'] ?? [],
                'created_at' => Carbon::createFromTimestamp($job->created_at)->format('M j, Y g:i A'),
                'raw_created_at' => Carbon::createFromTimestamp($job->created_at)
            ];
        });

        // Create new paginator with transformed items
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $jobs->total(),
            $jobs->perPage(),
            $jobs->currentPage(),
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    public function getFailedJobs()
    {
        $query = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc');

        $jobs = $query->paginate(15);
        
        // Transform the items
        $items = collect($jobs->items())->map(function ($job) {
            return (object) [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'exception' => Str::limit($job->exception, 100),
                'failed_at' => Carbon::parse($job->failed_at)->format('M j, Y g:i A'),
                'failed_at_human' => Carbon::parse($job->failed_at)->diffForHumans()
            ];
        });

        // Create new paginator with transformed items
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $jobs->total(),
            $jobs->perPage(),
            $jobs->currentPage(),
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    private function parseJobPayload($payload)
    {
        try {
            $decoded = json_decode($payload, true);
            
            if (!isset($decoded['data']['command'])) {
                return ['type' => 'Unknown', 'recipients' => []];
            }

            $command = unserialize($decoded['data']['command']);
            $type = $this->getJobTypeName($command);
            $recipients = $this->extractRecipients($command);

            return [
                'type' => $type,
                'recipients' => $recipients
            ];
        } catch (\Exception $e) {
            return ['type' => 'Unknown', 'recipients' => []];
        }
    }

    private function getJobTypeName($command)
    {
        if (method_exists($command, 'displayName')) {
            return str_replace('App\\Mail\\', '', $command->displayName());
        }
        
        $className = get_class($command);
        return str_replace(['App\\Mail\\', 'App\\Jobs\\'], '', $className);
    }

    private function extractRecipients($command)
    {
        $recipients = [];
        
        if (isset($command->mailable->to) && is_array($command->mailable->to)) {
            foreach ($command->mailable->to as $recipient) {
                if (isset($recipient['address'])) {
                    $recipients[] = $recipient['address'];
                }
            }
        }
        
        return $recipients;
    }

    public function retryAllFailedJobs()
    {
        $this->isLoading = true;
        
        try {
            $result = Artisan::call('queue:retry', ['id' => 'all']);
            
            $this->loadStats();
            $this->isLoading = false;
            
            $this->dispatch('alert', [
                'type' => 'success',
                'title' => 'Success!',
                'message' => 'All failed jobs have been queued for retry.'
            ]);
            
            $this->dispatch('jobRetried');
            
        } catch (\Exception $e) {
            $this->isLoading = false;
            
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Failed to retry jobs: ' . $e->getMessage()
            ]);
        }
    }

    public function retryJob($jobId)
    {
        try {
            Artisan::call('queue:retry', ['id' => $jobId]);
            
            $this->loadStats();
            
            $this->dispatch('alert', [
                'type' => 'success',
                'title' => 'Success!',
                'message' => 'Job has been queued for retry.'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Failed to retry job: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteFailedJob($id)
    {
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'message' => 'Are you sure?',
            'text' => 'This failed job will be permanently deleted!',
            'id' => $id,
            'method' => 'confirmDeleteFailedJob'
        ]);
    }

    public function confirmDeleteFailedJob($id)
    {
        try {
            DB::table('failed_jobs')->where('id', $id)->delete();
            
            $this->loadStats();
            
            $this->dispatch('swal:modal', [
                'type' => 'success',
                'title' => 'Deleted!',
                'message' => 'Failed job has been deleted successfully.'
            ]);
            
            $this->dispatch('jobDeleted');
            
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Failed to delete job: ' . $e->getMessage()
            ]);
        }
    }

    public function deletePendingJob($id)
    {
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'message' => 'Are you sure?',
            'text' => 'This pending job will be removed from the queue!',
            'id' => $id,
            'method' => 'confirmDeletePendingJob'
        ]);
    }

    public function confirmDeletePendingJob($id)
    {
        try {
            DB::table('jobs')->where('id', $id)->delete();
            
            $this->loadStats();
            
            $this->dispatch('alert', [
                'type' => 'success',
                'title' => 'Deleted!',
                'message' => 'Pending job has been removed from the queue.'
            ]);
            
            $this->dispatch('jobDeleted');
            
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Failed to delete job: ' . $e->getMessage()
            ]);
        }
    }

    public function refreshJobs()
    {
        $this->loadStats();
        
        $this->dispatch('alert', [
            'type' => 'info',
            'title' => 'Refreshed!',
            'message' => 'Job data has been updated.'
        ]);
    }

    #[On('jobDeleted')]
    public function handleJobDeleted()
    {
        $this->loadStats();
    }

    #[On('jobRetried')]
    public function handleJobRetried()
    {
        $this->loadStats();
    }

    // Toggle select all functionality
    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectAllJobs();
        } else {
            $this->clearSelection();
        }
    }

    // Select all jobs on current page
    public function selectAllJobs()
    {
        if ($this->activeTab === 'pending') {
            $jobs = $this->getPendingJobs();
        } else {
            $jobs = $this->getFailedJobs();
        }
        
        $this->selectedJobs = $jobs->pluck('id')->toArray();
        $this->selectAll = true;
    }

    // Clear all selections
    public function clearSelection()
    {
        $this->selectedJobs = [];
        $this->selectAll = false;
    }

    // Watch for changes in selectedJobs to update selectAll state
    public function updatedSelectedJobs()
    {
        if ($this->activeTab === 'pending') {
            $jobs = $this->getPendingJobs();
        } else {
            $jobs = $this->getFailedJobs();
        }
        
        $allJobIds = $jobs->pluck('id')->toArray();
        $this->selectAll = !empty($allJobIds) && count(array_intersect($this->selectedJobs, $allJobIds)) === count($allJobIds);
    }

    // Clear selection when switching tabs
    public function updatedActiveTab()
    {
        $this->clearSelection();
    }

    // Bulk delete selected jobs
    public function deleteSelectedJobs()
    {
        if (empty($this->selectedJobs)) {
            $this->dispatch('alert', [
                'type' => 'warning',
                'title' => 'No Selection',
                'message' => 'Please select jobs to delete.'
            ]);
            return;
        }

        $jobType = $this->activeTab === 'pending' ? 'pending' : 'failed';
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'message' => 'Delete Selected Jobs?',
            'text' => 'This will permanently delete ' . count($this->selectedJobs) . ' ' . $jobType . ' job(s). This action cannot be undone!',
            'id' => json_encode($this->selectedJobs),
            'method' => 'confirmDeleteSelectedJobs'
        ]);
    }

    public function confirmDeleteSelectedJobs($jobIds)
    {
        try {
            $jobIds = is_string($jobIds) ? json_decode($jobIds, true) : $jobIds;
            $deletedCount = 0;
            
            if ($this->activeTab === 'pending') {
                $deletedCount = DB::table('jobs')->whereIn('id', $jobIds)->delete();
            } else {
                $deletedCount = DB::table('failed_jobs')->whereIn('id', $jobIds)->delete();
            }
            
            $this->selectedJobs = [];
            $this->selectAll = false;
            $this->loadStats();
            
            $this->dispatch('alert', [
                'type' => 'success',
                'title' => 'Deleted!',
                'message' => $deletedCount . ' job(s) have been deleted successfully.'
            ]);
            
            $this->dispatch('jobDeleted');
            
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Failed to delete jobs: ' . $e->getMessage()
            ]);
        }
    }

    // Bulk retry selected failed jobs
    public function retrySelectedJobs()
    {
        if (empty($this->selectedJobs)) {
            $this->dispatch('alert', [
                'type' => 'warning',
                'title' => 'No Selection',
                'message' => 'Please select jobs to retry.'
            ]);
            return;
        }

        if ($this->activeTab !== 'failed') {
            $this->dispatch('alert', [
                'type' => 'warning',
                'title' => 'Invalid Action',
                'message' => 'Retry is only available for failed jobs.'
            ]);
            return;
        }

        try {
            $retryCount = count($this->selectedJobs);
            
            foreach ($this->selectedJobs as $jobId) {
                Artisan::call('queue:retry', ['id' => $jobId]);
            }
            
            $this->selectedJobs = [];
            $this->selectAll = false;
            $this->loadStats();
            
            $this->dispatch('alert', [
                'type' => 'success',
                'title' => 'Success!',
                'message' => $retryCount . ' job(s) have been queued for retry.'
            ]);
            
            $this->dispatch('jobRetried');
            
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Failed to retry jobs: ' . $e->getMessage()
            ]);
        }
    }

    public function clearAllFailedJobs()
    {
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'message' => 'Clear All Failed Jobs?',
            'text' => 'This will permanently delete all failed jobs. This action cannot be undone!',
            'id' => 'all',
            'method' => 'confirmClearAllFailedJobs'
        ]);
    }

    public function confirmClearAllFailedJobs()
    {
        try {
            DB::table('failed_jobs')->truncate();
            
            $this->loadStats();
            
            $this->dispatch('alert', [
                'type' => 'success',
                'title' => 'Cleared!',
                'message' => 'All failed jobs have been cleared.'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error!',
                'message' => 'Failed to clear jobs: ' . $e->getMessage()
            ]);
        }
    }

    // Debug method for testing
    public function testSelect()
    {
        $this->selectedJobs = ['1', '2', '3'];
        $this->dispatch('alert', [
            'type' => 'info',
            'title' => 'Test',
            'message' => 'Test selection: ' . implode(', ', $this->selectedJobs)
        ]);
    }
}
