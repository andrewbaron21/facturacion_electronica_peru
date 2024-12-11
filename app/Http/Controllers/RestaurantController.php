<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Restaurant;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Menu;
use App\Models\Tenant\Table;
use App\Models\Tenant\NewOrder;
use App\Models\Tenant\Employee;
use App\Models\Tenant\OrderItem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RestaurantController extends Controller
{
    // Mostrar lista de restaurantes
    public function index()
    {
        $restaurants = Restaurant::all(); // Obtener todos los restaurantes
        return view('tenant.restaurants.index', compact('restaurants'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        // VALIDATION FOR CREATE A RESTAURANT
        // $userId = auth()->user()->id;

        // if (!$this->isModuleEnabled($userId)) {
        //     return response()->json(['error' => 'Restaurant module not enabled'], 403);
        // }

        // Verificar si ya existe un restaurante
        if (Restaurant::count() > 0) {
            return redirect()->route('restaurants.list')->with('error', 'Solo puedes crear un restaurante.');
        }

        return view('tenant.restaurants.create');
    }

    // Guardar restaurante
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        Restaurant::create([
            'name' => $request->name,
            'address' => $request->address,
        ]);

        return redirect()->route('restaurants.list')->with('success', 'Restaurante creado con éxito.');
    }

    // Eliminar restaurante
    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->delete();

        return redirect()->route('restaurants.list')->with('success', 'Restaurante eliminado con éxito.');
    }

    // Función para listar las sedes de un restaurante
    public function branches(Restaurant $restaurant)
    {
        $branches = $restaurant->branches()->get();
        return view('tenant.restaurants.branches.index', compact('restaurant', 'branches'));
    }

    // Función para almacenar una nueva sede
    public function storeBranch(Request $request, Restaurant $restaurant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $restaurant->branches()->create($validated);

        return redirect()->route('branches.index', $restaurant->id)->with('success', 'Sede creada con éxito.');
    }

    // Función para eliminar una sede
    public function destroyBranch(Branch $branch)
    {
        $branch->delete();

        return redirect()->back()->with('success', 'Sede eliminada con éxito.');
    }

    private function isModuleEnabled($userId)
    {
        $module = DB::table('modules')->where('value', 'restaurant_app')->first();
        if (!$module) {
            return false;
        }

        return DB::table('module_user')
            ->where('module_id', $module->id)
            ->where('user_id', $userId)
            ->exists();
    }

    public function indexMenus()
    {
        $branches = Branch::with('menus')->get();

        return view('tenant.restaurants.menus.index', compact('branches'));
    }

    public function createMenu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'branch_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::connection('tenant')
                        ->table('branches')
                        ->where('id', $value)
                        ->exists();

                    if (!$exists) {
                        $fail('El ID de la Sede no existe en la base de datos de tenant.');
                    }
                },
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg|max:2048', // Validación del archivo como archivo genérico
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();

        if ($request->hasFile('image')) { // Verifica la presencia del archivo
            // Guarda la imagen en el disco público
            $path = $request->file('image')->store('menu_images', 'public');
            $data['image'] = $path; // Guarda solo el path relativo en la base de datos
        }

        Menu::create($data);

        return redirect()->route('menus.index')->with('success', 'Menú creado exitosamente.');
    }
      
    public function updateMenu(Request $request, $id)
    {
        $request->validate([
            'name' => 'string',
            'price' => 'numeric',
            'status' => 'boolean',
            'description' => 'nullable|string',
            'image' => 'nullable|mimes:jpg,png,jpeg|max:2048',
        ]);
    
        $menu = Menu::findOrFail($id);
    
        $data = $request->all();
    
        if ($request->hasFile('image')) {
            // Guarda la nueva imagen en el disco público
            $path = $request->file('image')->store('menu_images', 'public');
            $data['image'] = $path;
        }
    
        $menu->update($data);
    
        return redirect()->route('menus.index')->with('success', 'Plato actualizado con éxito.');
    }
    
    public function editMenu($id)
    {
        $menu = Menu::findOrFail($id);
        return view('tenant.restaurants.menus.edit', compact('menu'));
    }

    public function destroyMenu($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Platos eliminado con éxito.');
    }

    public function showCreateMenuForm()
    {
        $branches = Branch::all(); // Asegúrate de cargar las sucursales
        return view('tenant.restaurants.menus.create', compact('branches'));
    }

    public function showAvailableMenus($branchId)
    {
        // Verificar que la sede exista
        $branch = Branch::find($branchId);
    
        if (!$branch) {
            return redirect()->back()->withErrors(['error' => 'Sede no encontrada']);
        }
    
        // Obtener los menús disponibles para la sede
        $menus = Menu::where('status', true)->where('branch_id', $branchId)->get();
    
        // Retornar la vista con los menús
        return view('tenant.restaurants.menus.available', compact('menus', 'branch'));
    }    

    public function storeTable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    // Validar que el número de mesa no se repita en la misma sede
                    $exists = \DB::connection('tenant')
                        ->table('tables')
                        ->where('number', $value)
                        ->where('branch_id', $request->branch_id)
                        ->exists();

                    if ($exists) {
                        $fail("El número de mesa '$value' ya está en uso en esta sede.");
                    }
                },
            ],
            'branch_id' => 'required|exists:tenant.branches,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Table::create($request->all());

        return redirect()->route('tables.showCreateForm')->with('success', 'Mesa creada con éxito');
    } 

    public function showCreateTableForm()
    {
        $branches = Branch::all(); // Asegúrate de cargar las sucursales
        return view('tenant.restaurants.tables.create', compact('branches'));
    }

    public function updateTable(Request $request, $id)
    {
        $request->validate([
            'number' => 'string|unique:tables,number,' . $id,
            'restaurant_id' => 'exists:restaurants,id',
        ]);

        $table = Table::findOrFail($id);
        $table->update($request->all());
        return response()->json($table, 200);
    }

    public function listTables()
    {
        $restaurantId;
        // Obtener el ID del primer restaurante si no se proporciona uno
            $firstRestaurant = Restaurant::first();
            if ($firstRestaurant) {
                $restaurantId = $firstRestaurant->id;
            } else {
                return response()->json(['message' => 'No restaurants found.'], 404);
            }

        // Obtener las mesas del restaurante especificado
        // HERE WE ARE  GOING TO USE THE  BRANCH RESTAURANT USER TABLE TO KNOW WHAT TABLES GET WITH THE AUTH USER
        // $tables = Table::where('restaurant_id', $restaurantId)->get();
        $branches = Branch::with('tables')->get();
        return view('tenant.restaurants.tables.index', compact('branches'));
    }
    
    public function deleteTable($id)
    {
        $table = Table::findOrFail($id);
        $table->delete();

        return redirect()->route('tables.list')->with('success', 'Table deleted successfully');
    }

    public function deliveredOrders(Request $request)
    {
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', auth()->user()->email)
            ->value('type') === 'admin';

        $date = $request->input('date');
        $tableId = $request->input('table_id');

        if ($isAdmin) {
            // Administrador: obtiene todas las órdenes entregadas y todas las mesas
            $query = NewOrder::with(['table', 'items.menu'])->where('status', 'entregado');
            $tables = Table::all();
        } else {
            // Empleado: obtiene las órdenes entregadas y mesas de su sede
            $employee = Employee::where('email', auth()->user()->email)->first();
            if (!$employee) {
                return redirect()->back()->withErrors(['error' => 'Debe estar registrado como empleado para acceder a las órdenes entregadas.']);
            }

            $branchId = DB::connection('tenant')->table('branch_employee_roles')
                ->where('employee_id', $employee->id)
                ->value('branch_id');

            if (!$branchId) {
                return redirect()->back()->withErrors(['error' => 'Debe estar asignado a una sede para acceder a las órdenes entregadas.']);
            }

            $query = NewOrder::with(['table', 'items.menu'])
                ->where('status', 'entregado')
                ->whereHas('table.branch', function ($q) use ($branchId) {
                    $q->where('id', $branchId);
                });

            $tables = Table::where('branch_id', $branchId)->get();
        }

        // Aplicar filtros
        if ($date) {
            $query->whereDate('updated_at', Carbon::parse($date));
        }

        if ($tableId) {
            $query->where('table_id', $tableId);
        }

        $orders = $query->get();

        return view('tenant.restaurants.orders.delivered', compact('orders', 'tables', 'isAdmin'));
    }

    public function pollDeliveredOrders(Request $request)
    {
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', auth()->user()->email)
            ->value('type') === 'admin';

        $date = $request->input('date');
        $tableId = $request->input('table_id');

        if ($isAdmin) {
            // Administrador: obtiene todas las órdenes entregadas y todas las mesas
            $query = NewOrder::with(['table', 'items.menu'])->where('status', 'entregado');
            $tables = Table::all();
        } else {
            // Empleado: obtiene las órdenes entregadas y mesas de su sede
            $employee = Employee::where('email', auth()->user()->email)->first();
            if (!$employee) {
                return redirect()->back()->withErrors(['error' => 'Debe estar registrado como empleado para acceder a las órdenes entregadas.']);
            }

            $branchId = DB::connection('tenant')->table('branch_employee_roles')
                ->where('employee_id', $employee->id)
                ->value('branch_id');

            if (!$branchId) {
                return redirect()->back()->withErrors(['error' => 'Debe estar asignado a una sede para acceder a las órdenes entregadas.']);
            }

            $query = NewOrder::with(['table', 'items.menu'])
                ->where('status', 'entregado')
                ->whereHas('table.branch', function ($q) use ($branchId) {
                    $q->where('id', $branchId);
                });

            $tables = Table::where('branch_id', $branchId)->get();
        }

        // Aplicar filtros
        if ($date) {
            $query->whereDate('updated_at', Carbon::parse($date));
        }

        if ($tableId) {
            $query->where('table_id', $tableId);
        }

        $orders = $query->get();

        return view('tenant.restaurants.orders.delivered', compact('orders', 'tables', 'isAdmin'));
    }

    public function pollOrders()
    {
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', auth()->user()->email)
            ->value('type') === 'admin';

        if ($isAdmin) {
            // Si es admin, obtiene todas las órdenes pendientes
            $orders = NewOrder::with('table.branch')->where('status', 'pendiente')->get();
        } else {
            // Si no es admin, filtra por las órdenes de la sede asignada
            $employee = Employee::where('email', auth()->user()->email)->first();
            if (!$employee) {
                return response()->json(['error' => 'Empleado no encontrado'], 404);
            }

            $branchId = DB::connection('tenant')->table('branch_employee_roles')
                ->where('employee_id', $employee->id)
                ->value('branch_id');

            $orders = NewOrder::with('table.branch')
                ->whereHas('table', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->where('status', 'pendiente')
                ->get();
        }

        $orders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'status' => $order->status,
                'table_number' => $order->table->number ?? null,
                'branch_name' => $order->table->branch->name ?? null,
            ];
        });

        return response()->json(['orders' => $orders], 200);
    }
    
    public function pollOrdersWaiters()
    {
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', auth()->user()->email)
            ->value('type') === 'admin';

        if ($isAdmin) {
            // Si es admin, obtiene todas las órdenes pendientes con sus ítems
            $orders = NewOrder::with(['table.branch', 'items.menu'])->where('status', 'pendiente')->get();
        } else {
            // Si no es admin, obtiene las órdenes pendientes del empleado autenticado
            $employee = Employee::where('email', auth()->user()->email)->first();
            if (!$employee) {
                return response()->json(['error' => 'Empleado no encontrado'], 404);
            }

            $orders = NewOrder::with(['table.branch', 'items.menu'])
                ->where('status', 'pendiente')
                ->where('employee_id', $employee->id)
                ->get();
        }

        // Formatear los datos para incluir los ítems
        $orders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'status' => $order->status,
                'table_number' => $order->table->number ?? null,
                'items' => $order->items->map(function ($item) {
                    return [
                        'name' => $item->menu->name ?? 'Sin nombre',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->quantity * $item->price,
                        'status' => ucfirst($item->status),
                    ];
                }),
            ];
        });

        return response()->json(['orders' => $orders], 200);
    }

    public function pollReadyOrders()
    {
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', auth()->user()->email)
            ->value('type') === 'admin';
    
        if ($isAdmin) {
            // Administrador: obtiene todas las órdenes con estado "listo"
            $orders = NewOrder::with('table')->where('status', 'listo')->get();
        } else {
            // Empleado: obtiene solo las órdenes con estado "listo" que corresponden al empleado autenticado
            $employee = Employee::where('email', auth()->user()->email)->first();
    
            if (!$employee) {
                return response()->json(['error' => 'Empleado no encontrado'], 404);
            }
    
            $orders = NewOrder::with('table')
                ->where('status', 'listo')
                ->where('employee_id', $employee->id) // Filtrar por el ID del empleado autenticado
                ->get();
        }
    
        return response()->json(['orders' => $orders], 200);
    }

    public function indexChefsOrders()
    {
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', auth()->user()->email)
            ->value('type') === 'admin';

        if ($isAdmin) {
            // Si es admin, obtiene todas las órdenes pendientes
            $orders = NewOrder::with('table.branch')->where('status', 'pendiente')->get();
        } else {
            // Si no es admin, filtra por las órdenes de la sede asignada
            $employee = Employee::where('email', auth()->user()->email)->first();
            if (!$employee) {
                return redirect()->back()->withErrors(['error' => 'Empleado no encontrado']);
            }

            $branchId = DB::connection('tenant')->table('branch_employee_roles')
                ->where('employee_id', $employee->id)
                ->value('branch_id');

            $orders = NewOrder::with('table.branch')
                ->whereHas('table', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                })
                ->where('status', 'pendiente')
                ->get();
        }

        return view('tenant.restaurants.orders.chefs', compact('orders'));
    }
    
    public function indexWaitersOrders()
    {
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', auth()->user()->email)
            ->value('type') === 'admin';

        if ($isAdmin) {
            // Administrador: obtiene todas las órdenes pendientes con ítems
            $orders = NewOrder::with(['table.branch', 'items.menu'])->where('status', 'pendiente')->get();
        } else {
            // Mesero: obtiene las órdenes pendientes del empleado autenticado con ítems
            $employee = Employee::where('email', auth()->user()->email)->first();

            if (!$employee) {
                return redirect()->back()->withErrors(['error' => 'Debe estar registrado como empleado para gestionar pedidos.']);
            }

            $orders = NewOrder::with(['table.branch', 'items.menu'])
                ->where('status', 'pendiente')
                ->where('employee_id', $employee->id)
                ->get();
        }

        return view('tenant.restaurants.orders.waiters', compact('orders', 'isAdmin'));
    }

    public function createOrderView()
    {
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', auth()->user()->email)
            ->value('type') === 'admin';

        if ($isAdmin) {
            // Si es administrador, obtiene todas las mesas
            $tables = Table::with('branch')->get();
        } else {
            // Si no es administrador, busca la sede a la que pertenece el empleado
            $employee = Employee::where('email', auth()->user()->email)->first();

            if (!$employee) {
                return redirect()->back()->withErrors(['error' => 'Debe estar registrado como empleado para crear pedidos.']);
            }

            $branchId = DB::connection('tenant')->table('branch_employee_roles')
                ->where('employee_id', $employee->id)
                ->value('branch_id');

            if (!$branchId) {
                return redirect()->back()->withErrors(['error' => 'Debe pertenecer a una sede para crear pedidos.']);
            }

            // Obtiene las mesas de la sede asignada
            $tables = Table::with('branch')->where('branch_id', $branchId)->get();
        }

        return view('tenant.restaurants.orders.create', compact('tables', 'isAdmin'));
    }

    public function createOrder(Request $request)
    {
        // Obtener el empleado autenticado
        $employee = Employee::where('email', auth()->user()->email)->first();
    
        // Crear nuevo pedido con el ID del empleado autenticado
        NewOrder::create([
            'table_id' => $request->table_id,
            'status' => 'pendiente',
            'employee_id' => $employee->id, // Agregar el ID del empleado
        ]);
    
        return redirect()->route('orders.waiters')->with('success', 'Pedido creado exitosamente.');
    }
    
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pendiente,listo,entregado',
        ]);

        $order = NewOrder::findOrFail($id);
        $order->update(['status' => $request->status]);

        return redirect()->route('orders.chefs')->with('success', 'Estado del pedido actualizado.');
    }

    public function listOrders($tableId)
    {
        $orders = NewOrder::where('table_id', $tableId)->get();
        return response()->json($orders, 200);
    }

    public function deleteOrder($id)
    {
        $order = NewOrder::findOrFail($id);
        $order->delete();

        return redirect()->route('orders.waiters')->with('success', 'Pedido eliminado exitosamente.');
    }
    
    public function listOrderItems($orderId)
    {
        $user = auth()->user();
    
        // Validar si el usuario es administrador
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', $user->email)
            ->value('type') === 'admin';
    
        // Obtener los menús según el rol del usuario
        if ($isAdmin) {
            $menus = Menu::all();
        } else {
            $employee = Employee::where('email', $user->email)->first();
            if (!$employee) {
                return redirect()->back()->withErrors(['error' => 'Empleado no encontrado']);
            }
    
            // Obtener la sede del empleado
            $branchId = DB::connection('tenant')->table('branch_employee_roles')
                ->where('employee_id', $employee->id)
                ->value('branch_id');
            
            if (!$branchId) {
                return redirect()->back()->withErrors(['error' => 'No se pudo determinar la sede del empleado.']);
            }
    
            // Filtrar los menús por la sede
            $menus = Menu::where('branch_id', $branchId)->get();
        }
    
        $order = NewOrder::with('table')->findOrFail($orderId);
        $orderItems = OrderItem::where('order_id', $orderId)->with('menu')->get();
    
        return view('tenant.restaurants.orders.orderitems.index', compact('order', 'orderItems', 'menus'));
    }  
    

    public function readyOrders()
    {
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', auth()->user()->email)
            ->value('type') === 'admin';

        if ($isAdmin) {
            // Administrador: obtiene todas las órdenes con estado "listo"
            $orders = NewOrder::with('table')->where('status', 'listo')->get();
        } else {
            // Empleado: obtiene solo las órdenes con estado "listo" que corresponden al empleado autenticado
            $employee = Employee::where('email', auth()->user()->email)->first();

            if (!$employee) {
                return redirect()->back()->withErrors(['error' => 'Empleado no encontrado']);
            }

            $orders = NewOrder::with('table')
                ->where('status', 'listo')
                ->where('employee_id', $employee->id) // Filtrar por el ID del empleado autenticado
                ->get();
        }

        return view('tenant.restaurants.orders.ready', compact('orders'));
    }
 
    // Función para actualizar el estado del pedido a "entregado"
    public function deliverOrder($id)
    {
        $order = NewOrder::findOrFail($id);
        $order->update(['status' => 'entregado']);

        return redirect()->route('orders.ready')->with('success', 'Pedido entregado exitosamente.');
    }

    public function addItemToOrder(Request $request)
    {
        $user = auth()->user();

        // Validar si el usuario es administrador
        $isAdmin = DB::connection('tenant')->table('users')
            ->where('email', $user->email)
            ->value('type') === 'admin';

        // Verificar que el menú pertenece a la sede si no es administrador
        $menu = Menu::findOrFail($request->menu_id);
        if (!$isAdmin) {
            $employee = Employee::where('email', $user->email)->first();
            if (!$employee) {
                return redirect()->back()->withErrors(['error' => 'Empleado no encontrado']);
            }

            // Obtener la sede del empleado
            $branchId = DB::connection('tenant')->table('branch_employee_roles')
                ->where('employee_id', $employee->id)
                ->value('branch_id');
            
            if (!$branchId || $menu->branch_id != $branchId) {
                return redirect()->back()->with('error', 'No puedes agregar un plato que no pertenece a tu sede.');
            }
        }

        // Crear el ítem de la orden
        OrderItem::create([
            'order_id' => $request->order_id,
            'menu_id' => $menu->id,
            'quantity' => $request->quantity,
            'price' => $menu->price,
        ]);

        // Actualizar el estado de la orden a "pendiente"
        $order = NewOrder::findOrFail($request->order_id);
        $order->status = 'pendiente';
        $order->save();

        return redirect()->route('orderItems.list', $request->order_id)->with('success', 'Plato agregado exitosamente.');
    }
    
    public function markItemAsReady($id)
    {
        // Obtener el ítem de la orden
        $orderItem = OrderItem::findOrFail($id);

        // Cambiar el estado a 'listo'
        $orderItem->status = 'listo';
        $orderItem->save();

        // Verificar si todos los ítems de la orden están en estado 'listo'
        $orderId = $orderItem->order_id;
        $allItemsReady = OrderItem::where('order_id', $orderId)
            ->where('status', '!=', 'listo')
            ->doesntExist();

        if ($allItemsReady) {
            // Cambiar el estado del pedido a 'listo'
            $order = NewOrder::findOrFail($orderId);
            $order->status = 'listo';
            $order->save();
        }

        return redirect()->back()->with('success', 'El ítem ha sido marcado como listo.');
    }

    public function deleteOrderItem($id)
    {
        $item = OrderItem::findOrFail($id);
        $orderId = $item->order_id;
        $item->delete();

        return redirect()->route('orderItems.list', $orderId)->with('success', 'Ítem eliminado exitosamente.');
    }
}
