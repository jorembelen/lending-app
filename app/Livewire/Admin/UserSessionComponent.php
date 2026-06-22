<?php

namespace App\Livewire\Admin;


use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\User;


class UserSessionComponent extends Component
{
    public $search = '';
    public $perPage = 10;
    public $selectedUserId = null;
    public $confirmingLogoutSessionId = null;
    public $showAllSessions = false;

    public function getUsersProperty()
    {
        return User::orderBy('name')->get();
    }

    public function getSessionsProperty()
    {
        $query = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select('sessions.*', 'users.name as user_name', 'users.email as user_email');

        if (!$this->showAllSessions) {
            $query->whereNotNull('sessions.user_id');
        }
        if ($this->selectedUserId) {
            $query->where('sessions.user_id', $this->selectedUserId);
        }
        if ($this->search) {
            $query->where(function($q) {
                $q->where('users.name', 'like', '%'.$this->search.'%')
                  ->orWhere('users.email', 'like', '%'.$this->search.'%')
                  ->orWhere('sessions.ip_address', 'like', '%'.$this->search.'%');
            });
        }
        $query->orderByDesc('last_activity');
        return $query->paginate($this->perPage);
    }

    public function confirmLogout($sessionId)
    {
        $this->confirmingLogoutSessionId = $sessionId;
    }

    public function forceLogout($sessionId)
    {
        DB::table('sessions')->where('id', $sessionId)->delete();
        if (Session::getId() === $sessionId) {
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();
            return redirect()->route('login');
        }
        $this->confirmingLogoutSessionId = null;
        session()->flash('success', 'Session terminated successfully.');
    }

    public function render()
    {
        return view('livewire.admin.user-session-component', [
            'sessions' => $this->sessions,
            'users' => $this->users,
        ]);
    }
}
