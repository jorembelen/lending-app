<?php

namespace App\Livewire\Admin;

use App\Livewire\BaseComponent;
use App\Models\LoginSecurity;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;
use Spatie\Permission\Models\Role;

class UsersComponent extends BaseComponent
{
    public $userId;
    public $state = [];
    public $initialData;
    public $role;
    public $showTable = true;
    public $updatePass = false;
    public $showPass = false;
    public $showAdd = false;
    public $forcePasswordReset = false;

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['role'])) {
            $this->dispatch('hide-modal');
            $this->resetPage();
        }
    }

    public function render()
    {
        $records = new User();

        $query = $records->orderBy($this->sortColumnName, $this->sortDirection);

        if ($this->role) {
            $query->whereHas('roles', fn($query) => $query->where('name', $this->role));
        }

        if ($this->search) {
            $query->search($this->search);
        }

        if (!auth()->user()->hasRole('super admin')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super admin');
            });
        }

        $total = $query->count();
        $users = $query->paginate(10);
        $roles = Role::query();
        if (!auth()->user()->hasRole('super admin')) {
            $roles->where('name', '!=', 'super admin');
        }

        $roles = $roles->select('name')->get();

        return view('livewire.admin.users-component', compact('users', 'total', 'roles'));
    }

    public function clear() 
    {
        $this->role = null;
        $this->dispatch('hide-modal');
    }

    public function togglePassword()
    {
        $this->showPass = !$this->showPass;
    }

    public function changeStatus(User $user)
    {
        $oldStatus = $user->status;
        $status = $user->status ? 0 : 1;
        $user->update(['status' => $status]);
        
        // Log the activity
        activity()
            ->withProperties([
                'attributes' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'changed_by' => auth()->user()->name
                ]
            ])
            ->log("User {$user->name} status changed from " . ($oldStatus ? 'Active' : 'Inactive') . " to " . ($status ? 'Active' : 'Inactive') . " by " . auth()->user()->name);
            
        $this->getAlert('Status was successfully updated to ' . $user->user_status);
        $this->close();
    }

    public function add()
    {
        $this->showAdd = true;
        $this->forcePasswordReset = false; // Default to not forcing password reset for new users
        $this->dispatch('showUserModal');
        
        // Log add user form opening
        activity()
            ->withProperties([
                'attributes' => [
                    'action' => 'add_user_form_opened',
                    'opened_by' => auth()->user()->name
                ]
            ])
            ->log("Add user form opened by " . auth()->user()->name);
    }

    public function showPass()
    {
        $this->updatePass = !$this->updatePass;
    }

    public function edit(User $user)
    {
        $this->dispatch('showUserModal');
        $this->userId = $user->id;
        $this->state = $user->toArray();
        $this->state['role'] = $user->role_name;
        $this->initialData = $this->state;
        // Include password_reset in initialData for proper comparison
        $this->initialData['password_reset'] = $user->password_reset;
        
        // Set forcePasswordReset based on current password_reset value (0 = force reset, 1 = no reset)
        $this->forcePasswordReset = $user->password_reset == 0;
    }

    public function close()
    {
        $this->dispatch('hide-modal');
        $this->updatePass = false;
        $this->forcePasswordReset = false;
        $this->state = null;
        $this->userId = null;
        $this->showPass = false;
        $this->showAdd = false;
        $this->resetValidation();
    }

    public function getAlert($msg)
    {
        $this->dispatch('alert', [
            'type' => 'success',
            'title' => 'Success',
            'message' => $msg
        ]);
    }

    public function validateForm()
    {
        $data = Validator::make($this->state ?? [], [
            'name' => 'required',
            'role' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ])->validate();

        return $data;
    }

    public function validateFormForUpdate()
    {
        $data = Validator::make($this->state ?? [], [
            'name' => 'required',
            'role' => 'required',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'username' => 'required|unique:users,username,' . $this->userId,
            'password' => $this->updatePass ? 'required|min:6|confirmed' : 'nullable|min:6|confirmed',
        ])->validate();

        return $data;
    }

    public function generatePassword()
    {
        $password = Str::password(12, true, true, false, false);
        $this->state['password'] = $password;
        $this->state['password_confirmation'] = $password;
        
        // Log password generation
        activity()
            ->withProperties([
                'attributes' => [
                    'action' => 'password_generated',
                    'for_user' => $this->state['name'] ?? 'New User',
                    'generated_by' => auth()->user()->name
                ]
            ])
            ->log("Password generated for user " . ($this->state['name'] ?? 'New User') . " by " . auth()->user()->name);
    }

    public function submit()
    {
        if ($this->userId) {
            $data = $this->validateFormForUpdate();
        } else {
            $data = $this->validateForm();
        }
        DB::beginTransaction();

        try {
            // Check if there are any changes (including password_reset changes from forcePasswordReset toggle)
            $hasFormChanges = $this->state != $this->initialData;
            $hasPasswordResetChange = false;
            
            if ($this->userId) {
                $currentPasswordResetInData = $this->forcePasswordReset ? 0 : 1;
                $hasPasswordResetChange = $this->initialData['password_reset'] != $currentPasswordResetInData;
            }
            
            if (!$hasFormChanges && !$hasPasswordResetChange) {
                return $this->addError('error', 'Sorry, no changes has been made.');
            } else {
                // Set password_reset value based on forcePasswordReset setting
                if ($this->userId) {
                    // For updates: if forcePasswordReset is checked, set to 0 (force reset), otherwise set to 1 (no reset needed)
                    $data['password_reset'] = $this->forcePasswordReset ? 0 : 1;
                } else {
                    // For new users: if forcePasswordReset is checked, set to 0 (force reset), otherwise set to 1 (no reset needed)
                    $data['password_reset'] = $this->forcePasswordReset ? 0 : 1;
                }

                $role = $data['role'];
                unset($data['role']);
                $user = User::updateOrCreate([
                    'id' => $this->userId,
                ], $data);

                if ($this->userId) {
                    if ($role != ($this->initialData['role'] ?? null)) {
                        $user->syncRoles($role);
                    }
                } else {
                    $user->assignRole($role);
                }

                // Log the activity
                $action = $this->userId ? 'updated' : 'created';
                $logData = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role,
                    'password_reset_required' => $this->forcePasswordReset ? 'Yes' : 'No',
                    'action' => $action
                ];
                
                if ($this->userId) {
                    $logData['changes'] = array_diff_assoc($this->state, $this->initialData);
                }

                activity()
                    ->withProperties(['attributes' => $logData])
                    ->log("User {$user->name} was {$action} by " . auth()->user()->name);
            }
            
            DB::commit();
            $action = $this->userId ? 'updated.' : 'added.';
            $this->getAlert($this->state['name'] . ' was successfully ' . $action);
            $this->close();
        } catch (\Exception $error) {
            DB::rollBack();
            $msg = $error->getMessage();
            dd($msg);
            activity()->withProperties(['attributes' => ['name' => 'error from users create']])->log($msg);
            return $this->addError('error', 'Sorry, transaction cannot be process. Please contact your system administrator.');
        }
    }

    public function showFilter() 
    {
        $this->dispatch('filterRecords');
    }


    /**
     * show confirmation on delete
     *
     * @param  mixed $id
     * @return void
     */
    public function alertConfirm($id)
    {
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'message' => 'Are you sure?',
            'text' => 'Are you sure? Please confirm to proceed.',
            'id' => $id
        ]);
    }

    #[On('delete')]
    public function delete($id)
    {
        $user = User::findOrFail($id);
        if ($user->id == auth()->id() || $user->logs()->exists()) {
            // Log failed deletion attempt
            activity()
                ->withProperties([
                    'attributes' => [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'reason' => $user->id == auth()->id() ? 'Cannot delete own account' : 'User has existing logs',
                        'attempted_by' => auth()->user()->name
                    ]
                ])
                ->log("Failed deletion attempt for user {$user->name} by " . auth()->user()->name);
                
            return $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Failed!',
                'text' => 'Sorry, this user cannot be deleted.',
            ]);
            $this->dispatch('showTable');
        }
        
        // Log successful deletion
        activity()
            ->withProperties([
                'attributes' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'deleted_by' => auth()->user()->name
                ]
            ])
            ->log("User {$user->name} was deleted by " . auth()->user()->name);

        // $user->logs()->delete(); 
        $user->delete();
        $oldFilePath = "uploads/avatar/$user->avatar";
        Storage::disk('s3')->delete($oldFilePath);
        return $this->getAlert($user->name . ' was successfully deleted.');
    }

    public function deleteConfirm($id)
    {
        $this->dispatch('delete:confirm', [
            'type' => 'warning',
            'message' => 'Are you sure?',
            'text' => 'Delete 2FA? Please confirm to proceed.',
            'id' => $id
        ]);
    }

    #[On('removeSecurityStatus')]
    public function removeSecurityStatus($id)
    {
        $user = User::findOrFail($id);
        $this->deleteSecurityRecord($user->id);
        
        // Enhanced logging for 2FA removal
        activity()
            ->withProperties([
                'attributes' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'action' => '2fa_removed',
                    'removed_by' => auth()->user()->name
                ]
            ])
            ->log("2FA removed for user {$user->name} by " . auth()->user()->name);
            
        return $this->getAlert("$user->name 2FA was successfully deleted.");
    }

    public function deleteSecurityRecord($id)
    {
        $security = LoginSecurity::whereUserId($id)->first();
        if (isset($security)) {
            $security->delete();
        }
        return null;
    }
}
