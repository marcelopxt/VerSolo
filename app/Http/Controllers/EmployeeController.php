<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('company')->latest()->paginate(10);

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('employees.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Employee::create($validatedData);

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário cadastrado com sucesso.');
    }

    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $companies = Company::all();
        return view('employees.edit', compact('employee', 'companies'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (empty($validatedData['password'])) {
            unset($validatedData['password']);
        }

        $employee->update($validatedData);

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário atualizado com sucesso.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Funcionário excluído com sucesso.');
    }
}