<?php

namespace App\Livewire\MasterData;

use App\Models\Module;
use Livewire\Component;

class ModuleDetail extends Component
{
    public $moduleId;

    public function mount(Module $module)
    {
        $this->moduleId = $module->id;
    }

    public function goBack()
    {
        return $this->redirect(route('master-data.modules.index'), navigate: true);
    }

    public function render()
    {
        $module = Module::with([
            'workOrderItems.subitems' => fn($q) => $q->orderBy('order'),
            'workOrderReferences',
            'personels.competencies',
            'tools.peralatan',
            'deliverables' => fn($q) => $q->orderBy('order'),
            'reviewer',
        ])->withCount('projects')->findOrFail($this->moduleId);

        return view('livewire.master-data.module-detail', [
            'module' => $module,
        ]);
    }
}
