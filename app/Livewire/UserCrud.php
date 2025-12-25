<?php

namespace App\Livewire;

use App\Models\Company;
use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;


class UserCrud extends Component
{
    use WithPagination;

    public $name, $email, $password, $user_id, $phone, $address, $is_active;
    public $isModalOpen = false;
    public $company;
    public $company_id;


    //crear funcion mount para inicializar company_id si es necesario
    public function mount()
    {
        // mostrar lista de companies, buscar lista de companies
        $this->company = Company::all();

    }   

    public function render()
    {
        return view('livewire.user-crud', [
            'users' => User::paginate(10)
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->user_id = null;
        $this->company_id = null;
        $this->phone = '';
        $this->address = '';
        $this->is_active = true;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'company_id' => 'required',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'company_id' => $this->company_id,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ]);

        session()->flash('message', 'Usuario creado exitosamente.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->user_id = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = ''; // No pre-llenar la contraseña
        $this->company_id = $user->company_id;
        $this->phone = $user->phone;
        $this->address = $user->address;
        $this->is_active = $user->is_active;

        $this->openModal();
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->user_id,
            'password' => 'nullable|string|min:8', // La contraseña es opcional en la actualización
            'company_id' => 'required',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($this->user_id) {
            $user = User::find($this->user_id);
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'company_id' => $this->company_id,
                'phone' => $this->phone,
                'address' => $this->address,
                'is_active' => $this->is_active,
            ];

            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }

            $user->update($userData);
            session()->flash('message', 'Usuario actualizado exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        User::find($id)->delete();
        session()->flash('message', 'Usuario eliminado exitosamente.');
    }
}
