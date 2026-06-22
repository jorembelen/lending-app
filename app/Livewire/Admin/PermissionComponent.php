<?php

namespace App\Livewire\Admin;

use App\Livewire\BaseComponent;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionComponent extends BaseComponent
{
    public $showModal = false;
    public $modalMode = 'create'; // create, edit
    public $permissionId;
    public $state = [
        'name' => '',
        'guard_name' => 'web'
    ];
    public $searchTerm = '';
    public $filterCategory = 'all';
    public $filterGuard = 'all';
    public $sortBy = 'name'; // name, created_at, roles_count
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $selectedPermissionIds = []; // For bulk operations
    
    protected $queryString = ['searchTerm', 'filterCategory', 'sortBy'];

    public function rules()
    {
        $rules = [
            'state.name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_]+$/',
                'unique:permissions,name,' . $this->permissionId
            ],
            'state.guard_name' => 'required|string|in:web,api'
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'state.name.required' => 'Permission name is required.',
            'state.name.regex' => 'Permission name can only contain letters, numbers, and underscores.',
            'state.name.unique' => 'A permission with this name already exists.',
            'state.name.max' => 'Permission name cannot exceed 255 characters.',
            'state.guard_name.required' => 'Guard name is required.',
            'state.guard_name.in' => 'Guard name must be either web or api.'
        ];
    }

    public function getPermissionCategoriesProperty()
    {
        // Get unique prefixes from existing permissions
        $prefixes = Permission::select('name')
            ->get()
            ->map(function ($permission) {
                // Extract prefix (everything before the first underscore)
                $parts = explode('_', $permission->name);
                return count($parts) > 1 ? $parts[0] . '_' : $permission->name;
            })
            ->unique()
            ->sort()
            ->mapWithKeys(function ($prefix) {
                // Convert prefix to readable label
                $label = str_replace('_', '', $prefix);
                $label = ucfirst($label);
                return [$prefix => $label];
            });

        return $prefixes->toArray();
    }

    public function mount()
    {
        // Initialize component
    }

    public function render()
    {
        $query = Permission::query()->with('roles');

        // Apply search filter
        if (!empty($this->searchTerm)) {
            $query->where('name', 'like', '%' . $this->searchTerm . '%');
        }

        // Apply category filter
        if ($this->filterCategory && $this->filterCategory !== 'all') {
            $query->where('name', 'like', $this->filterCategory . '%');
        }

        // Apply guard filter
        if ($this->filterGuard && $this->filterGuard !== 'all') {
            $query->where('guard_name', $this->filterGuard);
        }

        // Apply sorting
        if ($this->sortBy === 'roles_count') {
            $query->withCount('roles')->orderBy('roles_count', $this->sortDirection);
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        $permissions = $query->paginate($this->perPage);

        // Get statistics
        $totalPermissions = Permission::count();
        $webPermissions = Permission::where('guard_name', 'web')->count();
        $apiPermissions = Permission::where('guard_name', 'api')->count();
        $unusedPermissions = Permission::doesntHave('roles')->count();

        return view('livewire.admin.permission-component', compact(
            'permissions',
            'totalPermissions',
            'webPermissions', 
            'apiPermissions',
            'unusedPermissions'
        ));
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal($permissionId)
    {
        $permission = Permission::findOrFail($permissionId);
        $this->resetForm();
        $this->modalMode = 'edit';
        $this->permissionId = $permissionId;
        $this->state['name'] = $permission->name;
        $this->state['guard_name'] = $permission->guard_name;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $message = '';
                if ($this->modalMode === 'create') {
                    $permission = Permission::create([
                        'name' => $this->state['name'],
                        'guard_name' => $this->state['guard_name']
                    ]);
                    $message = 'Permission created successfully!';
                    
                    $msg = 'Permission created successfully';
                    activity()->withProperties(['attributes' => ['name' => 'permission create']])->log($msg);
                } else {
                    $permission = Permission::findOrFail($this->permissionId);
                    
                    // Check for changes
                    $hasChanges = $permission->name !== $this->state['name'] || 
                                 $permission->guard_name !== $this->state['guard_name'];
                    
                    if (!$hasChanges) {
                        $this->dispatch('alert', [
                            'type' => 'info',
                            'title' => 'No Changes',
                            'message' => 'No changes were made to the permission.'
                        ]);
                        $this->closeModal();
                        return;
                    }
                    
                    $permission->update([
                        'name' => $this->state['name'],
                        'guard_name' => $this->state['guard_name']
                    ]);
                    $message = 'Permission updated successfully!';
                    
                    $msg = 'Permission updated successfully';
                    activity()->withProperties(['attributes' => ['name' => 'permission update']])->log($msg);
                }

                $this->closeModal();
                $this->dispatch('alert', [
                    'type' => 'success',
                    'title' => 'Success',
                    'message' => $message
                ]);
            });
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'An error occurred while saving the permission. Please try again.'
            ]);
        }
    }

    #[On('confirmDelete')]
    public function confirmDelete($permissionId)
    {
        $permission = Permission::findOrFail($permissionId);
        
        // Check if permission has roles
        if ($permission->roles()->count() > 0) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Cannot Delete',
                'message' => 'This permission is assigned to roles and cannot be deleted.'
            ]);
            return;
        }

        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'title' => 'Delete Permission',
            'text' => "Are you sure you want to delete the permission '{$permission->name}'?",
            'confirmText' => 'Yes, Delete',
            'method' => 'deletePermission',
            'params' => $permissionId
        ]);
    }

    #[On('confirmBulkDelete')]
    public function confirmBulkDelete()
    {
        if (empty($this->selectedPermissionIds)) {
            $this->dispatch('alert', [
                'type' => 'warning',
                'title' => 'No Selection',
                'message' => 'Please select permissions to delete.'
            ]);
            return;
        }

        $permissionsWithRoles = Permission::whereIn('id', $this->selectedPermissionIds)
            ->has('roles')
            ->count();

        if ($permissionsWithRoles > 0) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Cannot Delete',
                'message' => 'Some selected permissions are assigned to roles and cannot be deleted.'
            ]);
            return;
        }

        $count = count($this->selectedPermissionIds);
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'title' => 'Delete Permissions',
            'text' => "Are you sure you want to delete {$count} selected permissions?",
            'confirmText' => 'Yes, Delete All',
            'method' => 'bulkDeletePermissions',
            'params' => $this->selectedPermissionIds
        ]);
    }

    #[On('deletePermission')]
    public function deletePermission($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $permissionName = $permission->name;
            
            $permission->delete();
            
            $msg = 'Permission deleted successfully';
            activity()->withProperties(['attributes' => ['name' => 'permission delete']])->log($msg);

            $this->dispatch('alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Permission deleted successfully!'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'An error occurred while deleting the permission.'
            ]);
        }
    }

    #[On('bulkDeletePermissions')]
    public function bulkDeletePermissions($permissionIds)
    {
        try {
            $permissions = Permission::whereIn('id', $permissionIds)->get();
            $permissionNames = $permissions->pluck('name')->toArray();
            
            Permission::whereIn('id', $permissionIds)->delete();
            
            $msg = 'Bulk permissions deleted successfully';
            activity()->withProperties(['attributes' => ['name' => 'permissions bulk delete']])->log($msg);

            $this->selectedPermissionIds = [];
            
            $this->dispatch('alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => count($permissionNames) . ' permissions deleted successfully!'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'An error occurred while deleting the permissions.'
            ]);
        }
    }

    public function sortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedFilterCategory()
    {
        $this->resetPage();
    }

    public function updatedFilterGuard()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->searchTerm = '';
        $this->filterCategory = 'all';
        $this->filterGuard = 'all';
        $this->sortBy = 'name';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function exportPermissions()
    {
        $permissions = Permission::with('roles')->get();
        
        $export = $permissions->map(function ($permission) {
            return [
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'roles' => $permission->roles->pluck('name')->toArray(),
                'roles_count' => $permission->roles()->count(),
                'created_at' => $permission->created_at->format('Y-m-d H:i:s')
            ];
        });

        $this->dispatch('download-json', [
            'data' => $export->toJson(),
            'filename' => 'permissions_export_' . now()->format('Y_m_d_H_i_s') . '.json'
        ]);

        $this->dispatch('alert', [
            'type' => 'success',
            'title' => 'Export Complete',
            'message' => 'Permissions exported successfully!'
        ]);
    }

    private function resetForm()
    {
        $this->state = [
            'name' => '',
            'guard_name' => 'web'
        ];
        $this->permissionId = null;
    }
}
