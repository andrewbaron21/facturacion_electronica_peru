<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Restaurant;
use App\Models\Tenant\Menu;
use App\Models\Tenant\Table;
use App\Models\Tenant\NewOrder;
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

    public function polling()
    {
        // Recuperar todos los pedidos pendientes o listos
        $orders = NewOrder::with('table')->whereIn('status', ['pendiente', 'listo'])->get();

        // Retornar una respuesta JSON con los datos de los pedidos
        return response()->json(['orders' => $orders]);
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
        $menus = Menu::all();
        return view('tenant.restaurants.menus.index', compact('menus'));
    }

    public function createMenu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'restaurant_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::connection('tenant')
                        ->table('restaurants')
                        ->where('id', $value)
                        ->exists();

                    if (!$exists) {
                        $fail('El ID del restaurante no existe en la base de datos de tenant.');
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
        $restaurants = Restaurant::all(); 
        return view('tenant.restaurants.menus.create', compact('restaurants'));
    }

    public function showAvailableMenus()
    {
        // Obtener los menús disponibles
        $menus = Menu::where('status', true)->get();

        // Retornar la vista con los menús
        return view('tenant.restaurants.menus.available', compact('menus'));
    }

    public function storeTable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|string',
            'number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Validar en la conexión de tenant
                    $exists = \DB::connection('tenant')
                        ->table('tables')
                        ->where('number', $value)
                        ->exists();
    
                    if ($exists) {
                        $fail('El número de mesa ya está en uso.');
                    }
                },
            ],
            'restaurant_id' => 'required|exists:tenant.restaurants,id',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        Table::create($request->all());
    
        return redirect()->route('tables.showCreateForm')->with('success', 'Mesa creada con éxito');
    }    

    public function showCreateTableForm()
    {
        $restaurants = Restaurant::all();
        return view('tenant.restaurants.tables.create', compact('restaurants'));
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
        $tables = Table::where('restaurant_id', $restaurantId)->get();
        return view('tenant.restaurants.tables.index', compact('tables'));
    }
    
    public function deleteTable($id)
    {
        $table = Table::findOrFail($id);
        $table->delete();

        return redirect()->route('tables.list')->with('success', 'Table deleted successfully');
    }

    public function deliveredOrders(Request $request)
    {
        // Filtrar por fecha y mesa
        $date = $request->input('date');
        $tableId = $request->input('table_id');

        // Consulta base para pedidos entregados
        $query = NewOrder::with(['table', 'items.menu'])
            ->where('status', 'entregado');
            // dd($query);

        // Aplicar filtro por fecha
        if ($date) {
            $query->whereDate('updated_at', Carbon::parse($date));
        }

        // Aplicar filtro por mesa
        if ($tableId) {
            $query->where('table_id', $tableId);
        }

        $orders = $query->get();

        // Obtener la lista de mesas para el filtro
        $tables = Table::all();

        return view('tenant.restaurants.orders.delivered', compact('orders', 'tables'));
    }

    // Polling para obtener los pedidos entregados
    public function pollDeliveredOrders(Request $request)
    {
        $date = $request->input('date');
        $tableId = $request->input('table_id');

        $query = NewOrder::with(['table', 'items.menu'])
            ->where('status', 'entregado');

        if ($date) {
            $query->whereDate('updated_at', Carbon::parse($date));
        }

        if ($tableId) {
            $query->where('table_id', $tableId);
        }

        $orders = $query->get();

        return response()->json(['orders' => $orders], 200);
    }

    // Polling para actualizar la lista de pedidos automáticamente
    public function pollOrders()
    {
        $orders = NewOrder::with('table')->where('status', 'pendiente')->get();
        return response()->json(['orders' => $orders], 200);
    }

    public function pollReadyOrders()
    {
        $orders = NewOrder::with('table')->where('status', 'listo')->get();
        return response()->json(['orders' => $orders], 200);
    }

    public function indexChefsOrders()
    {
        $orders = NewOrder::with('table')->where('status', 'pendiente')->get(); // Relación con mesas
        return view('tenant.restaurants.orders.chefs', compact('orders'));
    }

    public function indexWaitersOrders()
    {
        $orders = NewOrder::with('table')->where('status', 'pendiente')->get(); // Relación con mesas
        return view('tenant.restaurants.orders.waiters', compact('orders'));
    }
   
    public function createOrderView()
    {
        $tables = Table::all(); // Obtener todas las mesas disponibles
        return view('tenant.restaurants.orders.create', compact('tables'));
    }

    public function createOrder(Request $request)
    {
        // $request->validate([
        //     'table_id' => 'required|exists:tables,id',
        // ]);

        NewOrder::create([
            'table_id' => $request->table_id,
            'status' => 'pendiente',
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
        $order = NewOrder::with('table')->findOrFail($orderId);
        $orderItems = OrderItem::where('order_id', $orderId)->with('menu')->get();
        $menus = Menu::all();

        return view('tenant.restaurants.orders.orderitems.index', compact('order', 'orderItems', 'menus'));
    }

     // Función para listar pedidos listos
     public function readyOrders()
     {
         $orders = NewOrder::with('table')->where('status', 'listo')->get();
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
        // $request->validate([
        //     'order_id' => 'required|exists:new_orders,id',
        //     'menu_id' => 'required|exists:menus,id',
        //     'quantity' => 'required|integer|min:1',
        // ]);

        $menu = Menu::findOrFail($request->menu_id);
        OrderItem::create([
            'order_id' => $request->order_id,
            'menu_id' => $menu->id,
            'quantity' => $request->quantity,
            'price' => $menu->price,
        ]);

        return redirect()->route('orderItems.list', $request->order_id)->with('success', 'Plato agregado exitosamente.');
    }

    public function deleteOrderItem($id)
    {
        $item = OrderItem::findOrFail($id);
        $orderId = $item->order_id;
        $item->delete();

        return redirect()->route('orderItems.list', $orderId)->with('success', 'Ítem eliminado exitosamente.');
    }

}
