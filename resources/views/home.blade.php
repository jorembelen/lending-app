<x-admin-layout title="Home">

    <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-16 h-16 rounded-full bg-surface-container-low border border-outline-variant flex items-center justify-center mb-6">
            <span class="material-symbols-outlined text-on-surface-variant text-[32px]">person_off</span>
        </div>
        <h2 class="text-xl font-bold text-on-surface">No role assigned</h2>
        <p class="text-sm text-on-surface-variant mt-2 max-w-xs">Your account has not been assigned a role yet. Please contact an administrator.</p>
        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit"
                    class="h-10 px-6 border border-outline-variant text-on-surface-variant rounded-xl text-sm hover:border-outline hover:text-on-surface transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">logout</span>
                Sign Out
            </button>
        </form>
    </div>

</x-admin-layout>
