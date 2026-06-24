<?php

namespace App\Livewire\Collector;

use Livewire\Component;

/**
 * Admin/office-facing checklist for setting up a new collector's phone as an
 * installable PWA. This is a one-time, per-device process (see build spec 9.4):
 * a PWA must be loaded online once before offline mode works, so office staff
 * need a repeatable script rather than relying on memory.
 */
class OnboardingComponent extends Component
{
    public function render()
    {
        return view('livewire.collector.onboarding-component')
            ->layout('components.layout.standalone-page', [
                'title' => 'Collector App Setup — ' . config('app.name'),
            ]);
    }
}
