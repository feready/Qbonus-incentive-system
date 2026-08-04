<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::latest()->paginate(10);

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|unique:employees',
            'name' => 'required',
        ]);

        Employee::create([
            'nik' => $request->nik,
            'name' => $request->name,
            'division' => $request->division,
            'position' => $request->position,
            'bank' => $request->bank,
            'account_number' => $request->account_number,
            'npwp' => $request->npwp,
            'join_date' => $request->join_date,
            'active' => $request->has('active'),
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee)
    {
        //
    }

    public function edit(Employee $employee)
    {
        //
    }

    public function update(Request $request, Employee $employee)
    {
        //
    }

    public function destroy(Employee $employee)
    {
        //
    }
}