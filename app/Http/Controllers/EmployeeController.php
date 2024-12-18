<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Employee;
use App\Models\Tenant\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule; 

class EmployeeController extends Controller
{
    public function index()
    {
        // Verificar si la tabla roles está vacía y agregar roles predeterminados si es necesario
        if (Role::count() == 0) {
            Role::insert([
                ['name' => 'Cocinero', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Mesero', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Caja', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $employees = Employee::with('branches', 'roles')->get();
        $branches = Branch::all();
        $roles = Role::all();

        return view('tenant.employees.index', compact('employees', 'branches', 'roles'));
    }

    public function create()
    {
        $branches = Branch::all();
        $roles = Role::all();
        return view('tenant.employees.create', compact('branches', 'roles'));
    }

    public function store(Request $request)
    {
        // Validar los datos incluyendo la existencia del correo electrónico en la tabla employees del tenant
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:tenant.employees,email'], // Especificamos la conexión y tabla
            'phone' => 'nullable|string|max:15',
            'password' => 'required|string|min:8',
        ]);

        // Crear empleado
        $employee = Employee::create($request->only(['name', 'email', 'phone']));

        // Crear usuario asociado en la tabla users
        DB::connection('tenant')->table('users')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'type' => 'client',
            'establishment_id' => 1,
            'password' => Hash::make($request->password), // Hashear la contraseña
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Empleado creado con éxito');
    }

    public function edit($id)
    {
        // Buscar el empleado por ID
        $employee = Employee::findOrFail($id);

        // Mostrar la vista de edición
        return view('tenant.employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        // Validar los datos incluyendo la existencia del correo electrónico en la tabla employees del tenant
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('tenant.employees')->ignore($id)], // Ignorar el email actual
            'phone' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:8', // Contraseña opcional
        ]);

        // Buscar el empleado
        $employee = Employee::findOrFail($id);

        // Actualizar los datos del empleado
        $employee->update($request->only(['name', 'email', 'phone']));

        // Actualizar la tabla de usuarios
        $userData = ['name' => $request->name, 'email' => $request->email];
        if ($request->password) {
            $userData['password'] = Hash::make($request->password);
        }
        DB::connection('tenant')->table('users')
            ->where('email', $employee->getOriginal('email')) // Usamos el email original para buscar el usuario
            ->update($userData);

        return redirect()->route('employees.index')->with('success', 'Empleado actualizado con éxito');
    }

    public function destroy($id)
    {
        // Buscar el empleado por ID
        $employee = Employee::findOrFail($id);
    
        // Eliminar las relaciones del empleado en la tabla branch_employee_roles
        $this->removeRole(new Request(), $id); // Llamamos a la función removeRole
    
        // Generar una nueva contraseña aleatoria
        $newPassword = Str::random(10);
    
        // Actualizar la contraseña del usuario asociado en la tabla users
        DB::connection('tenant')->table('users')
            ->where('email', $employee->email)
            ->update(['password' => bcrypt($newPassword)]);
    
        // Eliminar el empleado en la tabla employees
        $employee->delete();
    
        return redirect()->route('employees.index')->with('success', 'Empleado eliminado y roles actualizados con éxito. La contraseña del usuario ha sido reiniciada.');
    }    

    public function assignRole(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'branch_id' => 'required|exists:tenant.branches,id',
            'employee_id' => 'required|exists:tenant.employees,id',
            'role_id' => 'required|exists:tenant.roles,id',
        ]);

        // Encontrar el empleado
        $employee = Employee::find($request->employee_id);

        // Verificar si ya existe la combinación de branch_id, employee_id y role_id
        $exists = DB::connection('tenant')->table('branch_employee_roles')
            ->where('branch_id', $request->branch_id)
            ->where('employee_id', $request->employee_id)
            ->where('role_id', $request->role_id)
            ->exists();

        if ($exists) {
            return redirect()->route('employees.index')->withErrors(['error' => 'Este rol ya está asignado a esta sede para este empleado.']);
        }

        // Asignar el nuevo rol a la sede
        $employee->branches()->attach($request->branch_id, ['role_id' => $request->role_id]);

        return redirect()->route('employees.index')->with('success', 'Rol asignado con éxito');
    }

    public function removeRole(Request $request, $employeeId)
    {
        // Encontrar el empleado
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return redirect()->back()->withErrors(['error' => 'Empleado no encontrado']);
        }

        // Eliminar todas las relaciones del empleado en la tabla branch_employee_roles
        DB::connection('tenant')->table('branch_employee_roles')
            ->where('employee_id', $employeeId)
            ->delete();

        return redirect()->route('employees.index')->with('success', 'Todas las relaciones del empleado han sido eliminadas con éxito');
    }

}
