<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/clientes",
     *     summary="Listar todos los clientes",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por nombre, email o teléfono",
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
     *         description="Lista de clientes",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Customer"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = Customer::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($customers);
    }

    /**
     * @OA\Post(
     *     path="/clientes",
     *     summary="Crear un nuevo cliente",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cliente creado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Customer")
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return response()->json([
            'message' => 'Customer created successfully',
            'data' => $customer
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/clientes/{id}",
     *     summary="Obtener un cliente por ID",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Customer")
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Cliente no encontrado")
     * )
     */
    public function show(Customer $cliente): JsonResponse
    {
        $cliente->load('orders');

        return response()->json($cliente);
    }

    /**
     * @OA\Put(
     *     path="/clientes/{id}",
     *     summary="Actualizar un cliente",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente actualizado",
     *         @OA\JsonContent(ref="#/components/schemas/Customer")
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Cliente no encontrado")
     * )
     */
    public function update(UpdateCustomerRequest $request, Customer $cliente): JsonResponse
    {
        $cliente->update($request->validated());

        return response()->json([
            'message' => 'Customer updated successfully',
            'data' => $cliente
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/clientes/{id}",
     *     summary="Eliminar un cliente",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente eliminado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Cliente no encontrado")
     * )
     */
    public function destroy(Customer $cliente): JsonResponse
    {
        $cliente->delete();

        return response()->json([
            'message' => 'Customer deleted successfully'
        ], 200);
    }
}
