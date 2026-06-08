<?php

namespace App\Livewire\MasterData;

use App\Enums\RiskLevel;
use App\Exports\ModulesExport;
use App\Livewire\Traits\HasNotification;
use App\Models\Module;
use App\Services\ModuleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class ModuleManagement extends Component
{
    use WithPagination, AuthorizesRequests, HasNotification;

    public $search = '';
    public $riskFilter = '';
    public $showModal = false;
    public $editMode = false;

    public $moduleId;
    public $code;
    public $name;
    public $scope;
    public $method;
    public $resource;
    public $duration;
    public $risk_level = 'low';
    public $pricing_baseline;
    public $is_active = 1;
    public $notes;

    // Nested data structures
    public $workOrderItems = [];
    public $workOrderReferences = [];
    public $teams = [];
    public $tools = [];
    public $deliverables = [];

    public $showDeleteModal = false;
    public $deletingModuleId;
    public $deletingModuleName;

    public function mount()
    {
        $this->authorize('viewAny', Module::class);
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:50', $this->editMode ? 'unique:modules,code,' . $this->moduleId : 'unique:modules,code'],
            'name' => 'required|string|max:255',
            'scope' => 'nullable|string',
            'method' => 'nullable|string|max:255',
            'resource' => 'nullable|string',
            'duration' => 'nullable|string|max:255',
            'deliverable' => 'nullable|string',
            'risk_level' => ['required', 'string', 'in:' . implode(',', RiskLevel::values())],
            'pricing_baseline' => 'nullable|numeric|min:0',
            'is_active' => 'required|in:0,1',
            'notes' => 'nullable|string',
        ];
    }

    public function validationAttributes()
    {
        return [
            'code' => 'kode modul',
            'name' => 'nama modul',
            'scope' => 'scope',
            'method' => 'metode',
            'resource' => 'resource',
            'duration' => 'durasi',
            'risk_level' => 'tingkat risiko',
            'pricing_baseline' => 'harga baseline',
            'is_active' => 'status aktif',
            'notes' => 'catatan',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRiskFilter()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->authorize('create', Module::class);
        return $this->redirect(route('master-data.modules.create'), navigate: true);
    }

    public function edit($id)
    {
        $module = Module::findOrFail($id);
        $this->authorize('update', $module);
        return $this->redirect(route('master-data.modules.edit', $module), navigate: true);
    }

    public function save(ModuleService $service)
    {
        $this->validate();

        try {
            $data = [
                'code' => $this->code,
                'name' => $this->name,
                'scope' => $this->scope,
                'method' => $this->method,
                'resource' => $this->resource,
                'duration' => $this->duration,
                'deliverable' => $this->deliverable,
                'risk_level' => $this->risk_level,
                'pricing_baseline' => $this->pricing_baseline,
                'is_active' => $this->is_active,
                'notes' => $this->notes,
                'work_order_items' => $this->workOrderItems,
                'work_order_references' => $this->workOrderReferences,
                'teams' => $this->teams,
                'tools' => $this->tools,
                'deliverables' => $this->deliverables,
            ];

            if ($this->editMode) {
                $module = Module::findOrFail($this->moduleId);
                $this->authorize('update', $module);
                $service->update($module, $data);
                $message = 'Modul berhasil diupdate!';
            } else {
                $this->authorize('create', Module::class);
                $service->create($data);
                $message = 'Modul berhasil ditambahkan!';
            }

            $this->notifySuccess($message);
            $this->closeModal();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk melakukan aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $module = Module::findOrFail($id);
        $this->deletingModuleId = $module->id;
        $this->deletingModuleName = $module->name;
        $this->showDeleteModal = true;
    }

    public function delete(ModuleService $service)
    {
        try {
            $module = Module::findOrFail($this->deletingModuleId);
            $this->authorize('delete', $module);

            $service->delete($module);
            $this->notifySuccess('Modul berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus modul ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id, ModuleService $service)
    {
        try {
            $module = Module::findOrFail($id);
            $this->authorize('toggleStatus', $module);

            $service->toggleStatus($module);
            $status = $module->fresh()->is_active ? 'aktif' : 'non-aktif';
            $this->notifySuccess("Status modul berhasil diubah menjadi {$status}!");
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengubah status modul.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm()
    {
        $this->reset([
            'moduleId',
            'code',
            'name',
            'scope',
            'method',
            'resource',
            'duration',
            'risk_level',
            'pricing_baseline',
            'is_active',
            'notes',
            'workOrderItems',
            'workOrderReferences',
            'teams',
            'tools',
            'deliverables',
        ]);

        $this->risk_level = RiskLevel::Low->value;
        $this->is_active = 1;
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', Module::class);

        return (new ModulesExport($this->search, $this->riskFilter))
            ->download('modul-' . now()->format('Y-m-d-His') . '.xlsx');
    }

    public function exportPdf(ModuleService $service)
    {
        $this->authorize('exportPdf', Module::class);

        $modules = Module::with('deliverables')->withCount('projects')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('code', 'like', "%{$this->search}%")
                      ->orWhere('name', 'like', "%{$this->search}%")
                      ->orWhere('scope', 'like', "%{$this->search}%");
                });
            })
            ->when($this->riskFilter !== null && $this->riskFilter !== '', function ($q) {
                $q->where('risk_level', $this->riskFilter);
            })
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.modules-pdf', ['modules' => $modules]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'modul-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function render(ModuleService $service)
    {
        return view('livewire.master-data.module-management', [
            'modules' => $service->getFiltered(
                $this->search,
                $this->riskFilter,
                false
            ),
            'riskLevels' => RiskLevel::cases(),
            'competencies' => \App\Models\Competency::active()->get(),
        ]);
    }

    // Helper methods for nested arrays
    public function addWorkOrderItem()
    {
        $this->workOrderItems[] = [
            'order' => count($this->workOrderItems) + 1,
            'name' => '',
            'description' => '',
            'nature' => 'mandatory',
            'is_active' => true,
            'subitems' => [],
        ];
    }

    public function removeWorkOrderItem($index)
    {
        unset($this->workOrderItems[$index]);
        $this->workOrderItems = array_values($this->workOrderItems);
        $this->reorderWorkOrderItems();
    }

    public function addWorkOrderSubitem($itemIndex)
    {
        $this->workOrderItems[$itemIndex]['subitems'][] = [
            'order' => count($this->workOrderItems[$itemIndex]['subitems']) + 1,
            'name' => '',
            'description' => '',
            'nature' => $this->workOrderItems[$itemIndex]['nature'],
            'is_active' => true,
        ];
    }

    public function removeWorkOrderSubitem($itemIndex, $subitemIndex)
    {
        unset($this->workOrderItems[$itemIndex]['subitems'][$subitemIndex]);
        $this->workOrderItems[$itemIndex]['subitems'] = array_values($this->workOrderItems[$itemIndex]['subitems']);
        $this->reorderWorkOrderSubitems($itemIndex);
    }

    public function addWorkOrderReference()
    {
        $this->workOrderReferences[] = [
            'document_name' => '',
            'document_id' => '',
            'file_path' => '',
        ];
    }

    public function removeWorkOrderReference($index)
    {
        unset($this->workOrderReferences[$index]);
        $this->workOrderReferences = array_values($this->workOrderReferences);
    }

    public function addTeam()
    {
        $this->teams[] = [
            'position_name' => '',
            'quantity' => 1,
            'nature' => 'mandatory',
            'competencies' => [],
        ];
    }

    public function removeTeam($index)
    {
        unset($this->teams[$index]);
        $this->teams = array_values($this->teams);
    }

    public function addTool()
    {
        $this->tools[] = [
            'name' => '',
            'requires_calibration' => false,
            'quantity' => 1,
        ];
    }

    public function removeTool($index)
    {
        unset($this->tools[$index]);
        $this->tools = array_values($this->tools);
    }

    public function addDeliverable()
    {
        $this->deliverables[] = [
            'order' => count($this->deliverables) + 1,
            'name' => '',
            'description' => '',
            'nature' => 'mandatory',
            'is_active' => true,
        ];
    }

    public function removeDeliverable($index)
    {
        unset($this->deliverables[$index]);
        $this->deliverables = array_values($this->deliverables);
        $this->reorderDeliverables();
    }

    private function reorderWorkOrderItems()
    {
        foreach ($this->workOrderItems as $index => $item) {
            $this->workOrderItems[$index]['order'] = $index + 1;
        }
    }

    private function reorderWorkOrderSubitems($itemIndex)
    {
        foreach ($this->workOrderItems[$itemIndex]['subitems'] as $index => $subitem) {
            $this->workOrderItems[$itemIndex]['subitems'][$index]['order'] = $index + 1;
        }
    }

    private function reorderDeliverables()
    {
        foreach ($this->deliverables as $index => $deliverable) {
            $this->deliverables[$index]['order'] = $index + 1;
        }
    }
}
