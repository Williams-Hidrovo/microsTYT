<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/pedidos",
     *     summary="Listar todos los pedidos",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado (pending, processing, completed, cancelled)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "processing", "completed", "cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="query",
     *         description="Filtrar por ID de cliente",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por número de pedido",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Cantidad de resultados por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pedidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $customerId = $request->get('customer_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');

        $query = Order::with('customer');

        // Filter by status
        if ($status) {
            $query->status($status);
        }

        // Filter by customer
        if ($customerId) {
            $query->forCustomer($customerId);
        }

        // Filter by date range
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        // Search by order number
        if ($search) {
            $query->where('order_number', 'like', "%{$search}%");
        }

        $orders = $query->orderBy('order_date', 'desc')->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * @OA\Post(
     *     path="/pedidos",
     *     summary="Crear un nuevo pedido",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_id","total_amount"},
     *             @OA\Property(property="customer_id", type="integer", example=1),
     *             @OA\Property(property="order_number", type="string", example="ORD-12345"),
     *             @OA\Property(property="status", type="string", enum={"pending", "processing", "completed", "cancelled"}, example="pending"),
     *             @OA\Property(property="total_amount", type="number", format="float", example=199.99),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="notes", type="string", example="Order notes"),
     *             @OA\Property(property="order_date", type="string", format="date-time", example="2024-12-14T10:30:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pedido creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Generate order number if not provided
        if (!isset($data['order_number'])) {
            $data['order_number'] = 'ORD-' . strtoupper(uniqid());
        }

        $order = Order::create($data);
        $order->load('customer');

        return response()->json([
            'message' => 'Order created successfully',
            'data' => $order
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/pedidos/{id}",
     *     summary="Obtener un pedido por ID",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Pedido no encontrado")
     * )
     */
    public function show(Order $pedido): JsonResponse
    {
        $pedido->load('customer');

        return response()->json([
            'data' => $pedido
        ]);
    }

    /**
     * @OA\Put(
     *     path="/pedidos/{id}",
     *     summary="Actualizar un pedido",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_id", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"pending", "processing", "completed", "cancelled"}),
     *             @OA\Property(property="total_amount", type="number", format="float"),
     *             @OA\Property(property="currency", type="string"),
     *             @OA\Property(property="notes", type="string"),
     *             @OA\Property(property="order_date", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido actualizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Pedido no encontrado")
     * )
     */
    public function update(UpdateOrderRequest $request, Order $pedido): JsonResponse
    {
        $pedido->update($request->validated());
        $pedido->load('customer');

        return response()->json([
            'message' => 'Order updated successfully',
            'data' => $pedido
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/pedidos/{id}/complete",
     *     summary="Marcar pedido como completado",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido marcado como completado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order marked as completed"),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Pedido no encontrado")
     * )
     */
    public function complete(Order $order): JsonResponse
    {
        $order->markAsCompleted();
        $order->load('customer');

        return response()->json([
            'message' => 'Order marked as completed',
            'data' => $order
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/pedidos/{id}/cancel",
     *     summary="Marcar pedido como cancelado",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido marcado como cancelado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order marked as cancelled"),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Pedido no encontrado")
     * )
     */
    public function cancel(Order $order): JsonResponse
    {
        $order->markAsCancelled();
        $order->load('customer');

        return response()->json([
            'message' => 'Order marked as cancelled',
            'data' => $order
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/pedidos/{id}",
     *     summary="Eliminar un pedido",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido eliminado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Pedido no encontrado")
     * )
     */
    public function destroy(Order $pedido): JsonResponse
    {
        $pedido->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ], 200);
    }
}
