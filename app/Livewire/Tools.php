<?php

namespace App\Livewire;

use App\Models\Tool;
use App\Models\History;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Tools extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $showModal = false;
    public $showDeleteModal = false;
    public $showImageViewer = false;
    public $editingToolId = null;
    public $viewingImage = null;

    // Form fields
    public $image;
    public $existingImage = null;
    public $quantity;
    public $brand;
    public $model;
    public $description;
    public $ownership_type = 'company';
    public $released_to;
    public $release_date;

    protected $rules = [
        'image' => 'nullable|image|max:2048',
        'quantity' => 'required|integer|min:1',
        'brand' => 'required|string|max:255',
        'model' => 'nullable|string|max:255',
        'description' => 'required|string|max:255',
        'ownership_type' => 'required|in:company,employee',
        'released_to' => 'nullable|string|max:255',
        'release_date' => 'nullable|date',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal($toolId = null)
    {
        $this->resetValidation();
        
        if ($toolId) {
            $tool = Tool::findOrFail($toolId);
            $this->editingToolId = $tool->id;
            $this->existingImage = $tool->hasImageBlob() ? $tool->image_url : null;
            $this->quantity = $tool->quantity;
            $this->brand = $tool->brand;
            $this->model = $tool->model;
            $this->description = $tool->description;
            $this->ownership_type = $tool->ownership_type ?? 'company';
            $this->released_to = $tool->released_to;
            $this->release_date = $tool->release_date?->format('Y-m-d');
        } else {
            $this->resetForm();
        }
        
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

        $data = [
            'quantity' => $this->quantity,
            'brand' => $this->brand,
            'model' => $this->model,
            'description' => $this->description,
            'ownership_type' => $this->ownership_type,
            'released_to' => $this->released_to,
            'release_date' => $this->release_date,
        ];

        // Handle image upload
        if ($this->image) {
            $imageData = file_get_contents($this->image->getRealPath());
            $data['image_blob'] = base64_encode($imageData);
            $data['image_mime_type'] = $this->image->getMimeType();
            $data['image_filename'] = $this->image->getClientOriginalName();
        }

        if ($this->editingToolId) {
            $tool = Tool::findOrFail($this->editingToolId);
            $oldValues = [
                'quantity' => $tool->quantity,
                'brand' => $tool->brand,
                'model' => $tool->model,
                'description' => $tool->description,
                'ownership_type' => $tool->ownership_type,
                'released_to' => $tool->released_to,
                'release_date' => $tool->release_date?->format('Y-m-d'),
                'has_image' => $tool->hasImageBlob(),
            ];
            $tool->update($data);

            // Check for actual changes (exclude calculated fields like has_image)
            $changes = [];
            $userEditableFields = ['quantity', 'brand', 'model', 'description', 'ownership_type', 'released_to', 'release_date'];
            foreach ($userEditableFields as $field) {
                if (isset($data[$field]) && $oldValues[$field] != $data[$field]) {
                    $changes[$field] = $data[$field];
                }
            }

            // Only log if there are actual changes
            if (!empty($changes)) {
                History::create([
                    'user_id' => auth()->id(),
                    'action' => 'update',
                    'model' => 'tool',
                    'model_id' => $tool->id,
                    'old_values' => $oldValues,
                    'changes' => $changes,
                ]);
            }
            session()->flash('message', 'Tool updated successfully.');
        } else {
            $tool = Tool::create($data);
            History::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model' => 'tool',
                'model_id' => $tool->id,
                'changes' => [
                    'quantity' => $data['quantity'],
                    'brand' => $data['brand'],
                    'model' => $data['model'],
                    'description' => $data['description'],
                    'ownership_type' => $data['ownership_type'],
                    'released_to' => $data['released_to'],
                    'release_date' => $data['release_date'],
                    // has_image is calculated, don't include in changes for create either
                ],
            ]);
            session()->flash('message', 'Tool added successfully.');
        }

        $this->closeModal();
    }

    public function openDeleteModal($toolId)
    {
        $this->editingToolId = $toolId;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->editingToolId = null;
    }

    public function viewImage($toolId)
    {
        $tool = Tool::findOrFail($toolId);
        if ($tool->hasImageBlob()) {
            History::create([
                'user_id' => auth()->id(),
                'action' => 'view',
                'model' => 'tool',
                'model_id' => $toolId,
                'changes' => ['viewed_image' => true],
            ]);
            $this->viewingImage = $tool->image_url;
            $this->showImageViewer = true;
        }
    }

    public function closeImageViewer()
    {
        $this->showImageViewer = false;
        $this->viewingImage = null;
    }

    public function deleteTool()
    {
        if ($this->editingToolId) {
            $tool = Tool::findOrFail($this->editingToolId);

            // Capture all tool details before deletion
            $toolDetails = [
                'brand' => $tool->brand,
                'model' => $tool->model,
                'description' => $tool->description,
                'quantity' => $tool->quantity,
                'ownership_type' => $tool->ownership_type,
                'released_to' => $tool->released_to,
                'release_date' => $tool->release_date?->format('Y-m-d'),
                'has_image' => $tool->hasImageBlob(),
                'deleted' => true
            ];

            $tool->delete();
            History::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model' => 'tool',
                'model_id' => $this->editingToolId,
                'changes' => $toolDetails,
            ]);
            session()->flash('message', 'Tool deleted successfully.');
        }

        $this->closeDeleteModal();
    }

    private function resetForm()
    {
        $this->editingToolId = null;
        $this->image = null;
        $this->existingImage = null;
        $this->quantity = null;
        $this->brand = '';
        $this->model = '';
        $this->description = '';
        $this->ownership_type = 'company';
        $this->released_to = '';
        $this->release_date = null;
    }

    public function render()
    {
        $tools = Tool::query()
            ->when($this->search, function ($query) {
                $query->where('brand', 'like', '%' . $this->search . '%')
                    ->orWhere('model', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('released_to', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.tools', [
            'tools' => $tools,
        ]);
    }
}
