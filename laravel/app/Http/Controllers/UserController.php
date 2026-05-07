<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{


    public function index(Request $request)
    {
        $user = $request->user();

        $query = User::with('roles', 'operation');

        if ($user->hasRole('Gerente')) {
            // Gerente solo ve Asesores de su operación
            $query->where('operation_id', $user->operation_id)
                ->role('Asesor');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhereHas('operation', fn($o) => $o->where('name', 'like', '%' . $search . '%'));
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('active')) {
            $query->where('active', (bool) $request->active);
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function show(User $user)
    {
        $user->load('roles', 'operation');
        return view('users.show', compact('user'));
    }

    public function create()
    {
        $operations = Operation::all();
        $roles = Role::all();

        return view('users.create', [
            'operations' => $operations,
            'roles' => $roles,
        ]);
    }

    public function edit(User $user)
    {
        $operations = Operation::all();
        $roles = Role::all();

        return view('users.edit', [
            'user' => $user,
            'operations' => $operations,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $selectedRole = is_array($request->roles) ? $request->roles[0] : null;
        $globalRoles  = ['Administrador', 'Secretario'];
        $needsOp      = !in_array($selectedRole, $globalRoles);

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:6|confirmed',
            'operation_id' => $needsOp ? 'required|exists:operations,id' : 'nullable|exists:operations,id',
            'roles'        => 'required|array|min:1',
            'roles.*'      => 'exists:roles,name',
        ]);

        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'operation_id' => $needsOp ? $request->operation_id : null,
        ]);

        $user->syncRoles($request->roles);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $selectedRole = is_array($request->roles) ? $request->roles[0] : null;
        $globalRoles  = ['Administrador', 'Secretario'];
        $needsOp      = !in_array($selectedRole, $globalRoles);

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'     => 'nullable|string|min:6|confirmed',
            'operation_id' => $needsOp ? 'required|exists:operations,id' : 'nullable|exists:operations,id',
            'roles'        => 'required|array|min:1',
            'roles.*'      => 'exists:roles,name',
        ]);

        $user->update([
            'name'         => $request->name,
            'email'        => $request->email,
            'operation_id' => $needsOp ? $request->operation_id : null,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $user->syncRoles($request->roles);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggle(User $user)
    {
        $user->update(['active' => !$user->active]);

        $state = $user->active ? 'activado' : 'desactivado';

        return back()->with('success', "Usuario {$user->name} {$state} correctamente.");
    }

    public function destroy(User $user)
    {
        // Verificar que no tenga contratos asignados
        if ($user->contracts()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar un usuario que tiene contratos asignados.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
