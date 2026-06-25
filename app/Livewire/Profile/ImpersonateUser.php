<?php

namespace App\Livewire\Profile;

use App\Livewire\Traits\HasNotification;
use App\Models\User;
use App\Services\ImpersonateService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class ImpersonateUser extends Component
{
    use WithPagination, AuthorizesRequests, HasNotification;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $roleFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function startImpersonate($userId, ImpersonateService $service)
    {
        $target = User::findOrFail($userId);
        $this->authorize('impersonate', $target);

        $service->start($target);

        $this->notifySuccess("Anda sekarang beraksi sebagai {$target->name}.");
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        $query = User::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->roleFilter, fn($q) => $q->role($this->roleFilter))
            ->where('id', '!=', auth()->id())
            ->orderBy('name');

        return view('livewire.profile.impersonate-user', [
            'users' => $query->paginate(8),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}
