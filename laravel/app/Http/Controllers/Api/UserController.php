<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $authUser = $request->user();

        $users = User::with('roles', 'operation')
            ->when($authUser->hasRole('Gerente'), function ($query) use ($authUser) {
                $query->where('operation_id', $authUser->operation_id);
            })
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $authUser = $request->user();

        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|exists:roles,name',
        ];

        // Solo Admin y Secretario deben enviar operación válida
        if (!$authUser->hasRole('Gerente')) {
            $rules['operation_id'] = 'required|exists:operations,id';
        }

        $request->validate($rules);

        // Restricción: Gerente solo puede crear Asesor
        if ($authUser->hasRole('Gerente') && $request->role !== 'Asesor') {
            return response()->json([
                'success' => false,
                'message' => 'Gerente solo puede crear Asesor'
            ], 403);
        }

        // Restricción: Secretario no puede crear Administrador
        if ($authUser->hasRole('Secretario') && $request->role === 'Administrador') {
            return response()->json([
                'success' => false,
                'message' => 'Secretario no puede crear Administrador'
            ], 403);
        }

        // Si es Gerente, forzamos la operación
        $operationId = $authUser->hasRole('Gerente')
            ? $authUser->operation_id
            : $request->operation_id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'operation_id' => $operationId,
            'active' => true
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente',
            'data' => $user->load('roles', 'operation')
        ], 201);
    }



    public function show(User $user)
    {
        $this->authorize('view', $user);
        return response()->json([
            'success' => true,
            'data' => $user->load('roles', 'operation')
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $authUser = $request->user();

        // Only administrators may change the operation_id; managers/secretaries
        // are not allowed to move users between operations.
        $data = $request->only('name', 'email', 'active');

        if ($authUser->hasRole('Administrador')) {
            $data['operation_id'] = $request->input('operation_id');
        }

        // role is intentionally ignored here – changes must happen through a
        // dedicated endpoint if needed to avoid unauthorized escalations.

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'data' => $user->load('roles', 'operation')
        ]);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
}
