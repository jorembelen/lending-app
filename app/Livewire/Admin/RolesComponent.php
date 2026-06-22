<?php

namespace App\Livewire\Admin;

use App\Livewire\BaseComponent;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RolesComponent extends BaseComponent
{
    public $showModal = false;
    public $showTemplateModal = false;
    public $showCompareModal = false;
    public $modalMode = 'create'; // create, edit, assign, clone, compare
    public $roleId;
    public $state = [
        'name' => '',
        'permissions' => []
    ];
    public $selectedRole;
    public $selectedRoles = []; // For comparison
    public $availablePermissions = [];
    public $rolePermissions = [];
    public $searchTerm = '';
    public $filterCategory = '';
    public $filterStatus = 'all'; // all, active, inactive
    public $sortBy = 'name'; // name, created_at, users_count
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $selectedRoleIds = []; // For bulk operations
    public $permissionSearch = '';
    public $showPermissionDetails = false;
    
    // Role templates
    public $roleTemplates = [
        'admin' => [
            'name' => 'Administrator',
            'permissions' => ['*'] // All permissions
        ],
        'manager' => [
            'name' => 'Manager',
            'permissions' => ['employee_*', 'view_*', 'export_*']
        ],
        'hr' => [
            'name' => 'Human Resources',
            'permissions' => ['employee_*', 'user_*', 'training_*', 'vacation_*']
        ],
        'viewer' => [
            'name' => 'Viewer',
            'permissions' => ['view_*']
        ]
    ];
    
    protected $queryString = ['searchTerm', 'filterCategory', 'sortBy'];

    public function updatedRolePermissions()
    {
        // Clean the permissions array whenever it's updated
        $this->rolePermissions = array_filter($this->rolePermissions, function($id) {
            return !empty($id) && is_numeric($id);
        });
    }
    
    /**
     * Get valid permission IDs from the given array, filtering out invalid values
     */
    private function getValidPermissionIds($permissionIds)
    {
        if (empty($permissionIds) || !is_array($permissionIds)) {
            return [];
        }
        
        // Filter out empty values, null, and non-numeric IDs
        $cleanIds = array_filter($permissionIds, function($id) {
            return !empty($id) && is_numeric($id) && $id > 0;
        });
        
        // Ensure we only get existing permission IDs
        return Permission::whereIn('id', $cleanIds)->pluck('id')->toArray();
    }

    public function rules()
    {
        $rules = [
            'state.name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s_-]+$/',
                'unique:roles,name,' . $this->roleId
            ],
            'state.permissions' => 'array'
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'state.name.required' => 'Role name is required.',
            'state.name.regex' => 'Role name can only contain letters, numbers, spaces, hyphens, and underscores.',
            'state.name.unique' => 'A role with this name already exists.',
            'state.name.max' => 'Role name cannot exceed 255 characters.'
        ];
    }

    public function mount()
    {
        $this->loadPermissions();
    }

    public function render()
    {
        $query = Role::with(['permissions', 'users'])
            ->whereNotIn('name', ['s_admin'])
            ->withCount('users');

        // Apply search filter
        if ($this->searchTerm) {
            $query->where('name', 'like', '%' . $this->searchTerm . '%');
        }

        // Apply category filter
        if ($this->filterCategory && $this->filterCategory !== 'all') {
            $query->whereHas('permissions', function ($q) {
                $q->where('name', 'like', $this->filterCategory . '%');
            });
        }

        // Apply status filter
        if ($this->filterStatus === 'active') {
            $query->has('users');
        } elseif ($this->filterStatus === 'inactive') {
            $query->doesntHave('users');
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'users_count':
                $query->orderBy('users_count', $this->sortDirection);
                break;
            case 'created_at':
                $query->orderBy('created_at', $this->sortDirection);
                break;
            default:
                $query->orderBy('name', $this->sortDirection);
        }

        $roles = $query->paginate($this->perPage);
        
        // Statistics
        $totalRoles = Role::whereNotIn('name', ['s_admin'])->count();
        $totalPermissions = Permission::count();
        $activeRoles = Role::whereNotIn('name', ['s_admin'])->has('users')->count();
        $recentRoles = Role::whereNotIn('name', ['s_admin'])
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return view('livewire.admin.roles-component', compact(
            'roles', 
            'totalRoles', 
            'totalPermissions', 
            'activeRoles',
            'recentRoles'
        ));
    }

    public function loadPermissions()
    {
        $this->availablePermissions = Permission::orderBy('name')->get();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedFilterCategory()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function setSortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function toggleRoleSelection($roleId)
    {
        if (in_array($roleId, $this->selectedRoleIds)) {
            $this->selectedRoleIds = array_diff($this->selectedRoleIds, [$roleId]);
        } else {
            $this->selectedRoleIds[] = $roleId;
        }
    }

    public function selectAllRoles()
    {
        $roleIds = Role::whereNotIn('name', ['s_admin'])->pluck('id')->toArray();
        $this->selectedRoleIds = $roleIds;
    }

    public function deselectAllRoles()
    {
        $this->selectedRoleIds = [];
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openTemplateModal()
    {
        $this->showTemplateModal = true;
    }

    public function createFromTemplate($templateKey)
    {
        $template = $this->roleTemplates[$templateKey] ?? null;
        if (!$template) return;

        $this->resetForm();
        $this->modalMode = 'create';
        $this->state['name'] = $template['name'];
        
        // Set permissions based on template
        if (in_array('*', $template['permissions'])) {
            $this->rolePermissions = Permission::pluck('id')->toArray();
        } else {
            $permissions = [];
            foreach ($template['permissions'] as $pattern) {
                if (str_ends_with($pattern, '*')) {
                    $prefix = rtrim($pattern, '*');
                    $matched = Permission::where('name', 'like', $prefix . '%')->pluck('id');
                    $permissions = array_merge($permissions, $matched->toArray());
                } else {
                    $permission = Permission::where('name', $pattern)->first();
                    if ($permission) {
                        $permissions[] = $permission->id;
                    }
                }
            }
            $this->rolePermissions = array_unique($permissions);
        }
        
        $this->showTemplateModal = false;
        $this->showModal = true;
    }

    public function openEditModal($roleId)
    {
        $role = Role::findOrFail($roleId);
        $this->resetForm();
        $this->modalMode = 'edit';
        $this->roleId = $roleId;
        $this->state['name'] = $role->name;
        $this->showModal = true;
    }

    public function openCloneModal($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        $this->resetForm();
        $this->modalMode = 'create';
        $this->state['name'] = 'Copy of ' . $role->name;
        $this->rolePermissions = $role->permissions->pluck('id')->toArray();
        $this->showModal = true;
    }

    public function openPermissionsModal($roleId)
    {
        $this->selectedRole = Role::with('permissions')->findOrFail($roleId);
        $this->rolePermissions = $this->selectedRole->permissions->pluck('id')->toArray();
        $this->modalMode = 'assign';
        $this->showModal = true;
    }

    public function openCompareModal()
    {
        if (count($this->selectedRoleIds) < 2) {
            $this->dispatch('alert', [
                'type' => 'warning',
                'title' => 'Selection Required',
                'message' => 'Please select at least 2 roles to compare.'
            ]);
            return;
        }

        $this->selectedRoles = Role::with('permissions')
            ->whereIn('id', $this->selectedRoleIds)
            ->get();
        $this->showCompareModal = true;
    }

    public function exportRoles()
    {
        $roles = Role::with('permissions')->whereNotIn('name', ['s_admin'])->get();
        
        $export = $roles->map(function ($role) {
            return [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
                'users_count' => $role->users()->count(),
                'created_at' => $role->created_at->format('Y-m-d H:i:s')
            ];
        });

        $this->dispatch('download-json', [
            'data' => $export->toJson(),
            'filename' => 'roles_export_' . now()->format('Y_m_d_H_i_s') . '.json'
        ]);

        $this->dispatch('alert', [
            'type' => 'success',
            'title' => 'Export Complete',
            'message' => 'Roles have been exported successfully.'
        ]);
    }

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $message = '';
                if ($this->modalMode === 'create') {
                    $role = Role::create([
                        'name' => $this->state['name']
                    ]);
                    
                    // Assign permissions if any were selected during creation
                    if (!empty($this->rolePermissions)) {
                        $validPermissionIds = $this->getValidPermissionIds($this->rolePermissions);
                        $role->syncPermissions($validPermissionIds);
                    }
                    
                    $message = 'Role created successfully!';
                    
                    $msg = 'Role created successfully';
                    activity()->withProperties(['attributes' => ['name' => 'role create']])->log($msg);
                } else {
                    $role = Role::findOrFail($this->roleId);
                    
                    // Check if there are any changes
                    $hasChanges = false;
                    
                    if ($role->name !== $this->state['name']) {
                        $hasChanges = true;
                    }
                    
                    if (!$hasChanges) {
                        $this->closeModal();
                        $this->dispatch('alert', [
                            'type' => 'info',
                            'title' => 'No Changes',
                            'message' => 'No changes were made to the role.'
                        ]);
                        return;
                    }
                    
                    $role->update([
                        'name' => $this->state['name']
                    ]);
                    $message = 'Role updated successfully!';
                    
                    $msg = 'Role updated successfully';
                    activity()->withProperties(['attributes' => ['name' => 'role update']])->log($msg);
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
                'message' => 'An error occurred while saving the role. Please try again.'
            ]);
        }
    }

    public function updatePermissions()
    {
        if (!$this->selectedRole) {
            return;
        }

        try {
            DB::transaction(function () {
                $validPermissionIds = $this->getValidPermissionIds($this->rolePermissions);
                
                // Check if there are any changes to permissions
                $currentPermissionIds = $this->selectedRole->permissions->pluck('id')->sort()->values()->toArray();
                $newPermissionIds = collect($validPermissionIds)->sort()->values()->toArray();
                
                if ($currentPermissionIds === $newPermissionIds) {
                    $this->closeModal();
                    $this->dispatch('alert', [
                        'type' => 'info',
                        'title' => 'No Changes',
                        'message' => 'No changes were made to the role permissions.'
                    ]);
                    return;
                }
                
                // Sync permissions
                $this->selectedRole->syncPermissions($validPermissionIds);
                
                $msg = 'Role permissions updated successfully';
                activity()->withProperties(['attributes' => ['name' => 'role permissions update']])->log($msg);
                    
                $this->closeModal();
                $this->dispatch('alert', [
                    'type' => 'success',
                    'title' => 'Success',
                    'message' => 'Permissions updated successfully!'
                ]);
            });
        } catch (\Exception $e) {
            dd($e);
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'An error occurred while updating permissions. Please try again.'
            ]);
        }
    }

    public function confirmDelete($roleId)
    {
        $role = Role::findOrFail($roleId);
      
        // Check if role has users
        if ($role->users()->count() > 0) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Cannot Delete',
                'message' => 'This role is assigned to users and cannot be deleted.'
            ]);
            return;
        }

        $this->dispatch('delete:confirm', [
            'type' => 'warning',
            'title' => 'Delete Role',
            'text' => "Are you sure you want to delete the role '{$role->name}'?",
            'id' => $roleId
        ]);
    }

    #[On('confirmBulkDelete')]
    public function confirmBulkDelete()
    {
        if (empty($this->selectedRoleIds)) {
            $this->dispatch('alert', [
                'type' => 'warning',
                'title' => 'No Selection',
                'message' => 'Please select roles to delete.'
            ]);
            return;
        }

        $rolesWithUsers = Role::whereIn('id', $this->selectedRoleIds)
            ->has('users')
            ->count();

        if ($rolesWithUsers > 0) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Cannot Delete',
                'message' => 'Some selected roles are assigned to users and cannot be deleted.'
            ]);
            return;
        }

        $count = count($this->selectedRoleIds);
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'title' => 'Delete Roles',
            'text' => "Are you sure you want to delete {$count} selected roles?",
            'confirmText' => 'Yes, Delete All',
            'method' => 'bulkDeleteRoles',
            'params' => $this->selectedRoleIds
        ]);
    }

    #[On('deleteRole')]
    public function deleteRole($id)
    {
        try {
            $role = Role::findOrFail($id);
            $roleName = $role->name;

            // Detach all permissions assigned to this role to avoid orphaned relations
            if (method_exists($role, 'syncPermissions')) {
                $role->syncPermissions([]);
            } else {
                // fallback: detach via relation if syncPermissions is not available
                if (method_exists($role, 'permissions')) {
                    $role->permissions()->detach();
                }
            }

            $role->delete();

            $msg = 'Role deleted successfully';
            activity()->withProperties(['attributes' => ['name' => 'role delete', 'role_name' => $roleName]])->log($msg);

            $this->dispatch('alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => 'Role deleted successfully!'
            ]);
        } catch (\Exception $e) {
            // Log exception for debugging
            logger()->error('Error deleting role', ['id' => $id, 'error' => $e->getMessage()]);

            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'An error occurred while deleting the role.'
            ]);
        }
    }

    #[On('bulkDeleteRoles')]
    public function bulkDeleteRoles($roleIds)
    {
        try {
            $roles = Role::whereIn('id', $roleIds)->get();
            $roleNames = $roles->pluck('name')->toArray();
            
            Role::whereIn('id', $roleIds)->delete();
            
            $msg = 'Bulk roles deleted successfully';
            activity()->withProperties(['attributes' => ['name' => 'roles bulk delete']])->log($msg);

            $this->selectedRoleIds = [];
            
            $this->dispatch('alert', [
                'type' => 'success',
                'title' => 'Success',
                'message' => count($roleNames) . ' roles deleted successfully!'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'An error occurred while deleting the roles.'
            ]);
        }
    }

    public function bulkAssignPermissions($permissionCategory)
    {
        if (!$this->selectedRole) {
            return;
        }

        $categoryPermissions = Permission::where('name', 'like', $permissionCategory . '%')
            ->pluck('id')
            ->toArray();

        $this->rolePermissions = array_unique(array_merge($this->rolePermissions, $categoryPermissions));
    }

    public function toggleCategoryPermissions($category)
    {
        if (!$this->selectedRole) {
            return;
        }

        $categoryPermissions = Permission::where('name', 'like', $category . '%')
            ->pluck('id')
            ->toArray();

        // Check if all permissions in this category are already selected
        $allSelected = !array_diff($categoryPermissions, $this->rolePermissions);

        if ($allSelected) {
            // Remove all permissions in this category
            $this->rolePermissions = array_diff($this->rolePermissions, $categoryPermissions);
        } else {
            // Add all permissions in this category
            $this->rolePermissions = array_unique(array_merge($this->rolePermissions, $categoryPermissions));
        }
    }

    public function removeAllPermissions()
    {
        $this->rolePermissions = [];
    }

    public function selectAllPermissions()
    {
        $this->rolePermissions = Permission::pluck('id')->toArray();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showTemplateModal = false;
        $this->showCompareModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm()
    {
        $this->state = [
            'name' => '',
            'permissions' => []
        ];
        $this->roleId = null;
        $this->selectedRole = null;
        $this->selectedRoles = [];
        $this->rolePermissions = [];
        $this->permissionSearch = '';
    }

    public function getPermissionCategoriesProperty()
    {
        $permissions = collect($this->availablePermissions);
        
        // Filter permissions if search is active
        if ($this->permissionSearch) {
            $permissions = $permissions->filter(function ($permission) {
                return stripos($permission->name, $this->permissionSearch) !== false;
            });
        }

        return $permissions->groupBy(function ($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'general';
        })->map(function ($permissions, $category) {
            return [
                'name' => $category,
                'display_name' => ucfirst(str_replace('_', ' ', $category)),
                'permissions' => $permissions->mapWithKeys(function ($permission) {
                    return [$permission->id => $permission->name];
                }),
                'count' => $permissions->count(),
                'selected_count' => $permissions->filter(function ($permission) {
                    return in_array($permission->id, $this->rolePermissions);
                })->count()
            ];
        });
    }

    public function getRoleAnalyticsProperty()
    {
        return [
            'most_used_roles' => Role::withCount('users')
                ->whereNotIn('name', ['s_admin'])
                ->orderBy('users_count', 'desc')
                ->limit(5)
                ->get(),
            'permission_usage' => Permission::withCount('roles')
                ->orderBy('roles_count', 'desc')
                ->limit(10)
                ->get(),
            'recent_activities' => activity()
                ->where('description', 'like', '%role%')
                ->latest()
                ->limit(10)
                ->get()
        ];
    }

    public function getFilteredPermissionsProperty()
    {
        $permissions = collect($this->availablePermissions);
        
        if ($this->permissionSearch) {
            $permissions = $permissions->filter(function ($permission) {
                return stripos($permission->name, $this->permissionSearch) !== false ||
                       stripos(str_replace('_', ' ', $permission->name), $this->permissionSearch) !== false;
            });
        }

        return $permissions;
    }
}
