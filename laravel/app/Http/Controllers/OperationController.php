<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use Illuminate\Http\Request;

class OperationController extends Controller
{
    public function index(Request $request)
    {
        $operations = Operation::with(['users', 'contracts'])
            ->withCount(['users', 'contracts'])
            ->paginate(20);

        return view('operations.index', [
            'operations' => $operations,
        ]);
    }

    public function create()
    {
        return view('operations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:operations',
            'description' => 'nullable|string|max:1000',
            'monthly_goal' => 'nullable|numeric|min:0',
            'contracts_goal' => 'nullable|integer|min:0',
            'sales_goal' => 'nullable|numeric|min:0',
        ]);

        Operation::create($request->only(['name', 'description', 'monthly_goal', 'contracts_goal', 'sales_goal']));

        return redirect()->route('operations.index')->with('success', 'Operación creada correctamente.');
    }

    public function edit(Operation $operation)
    {
        return view('operations.edit', compact('operation'));
    }

    public function update(Request $request, Operation $operation)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:operations,name,' . $operation->id,
            'description' => 'nullable|string|max:1000',
            'monthly_goal' => 'nullable|numeric|min:0',
            'contracts_goal' => 'nullable|integer|min:0',
            'sales_goal' => 'nullable|numeric|min:0',
        ]);

        $operation->update($request->only(['name', 'description', 'monthly_goal', 'contracts_goal', 'sales_goal']));

        return redirect()->route('operations.index')->with('success', 'Operación actualizada correctamente.');
    }

    public function destroy(Operation $operation)
    {
        // Verificar que no tenga usuarios asignados
        if ($operation->users()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar una operación que tiene usuarios asignados.']);
        }

        $operation->delete();

        return redirect()->route('operations.index')->with('success', 'Operación eliminada correctamente.');
    }
}
