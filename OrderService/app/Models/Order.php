<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     title="Order",
 *     description="Modelo de pedido",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="customer_id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="ORD-12345"),
 *     @OA\Property(property="status", type="string", enum={"pending", "processing", "completed", "cancelled"}, example="pending"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=199.99),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="notes", type="string", example="Order notes"),
 *     @OA\Property(property="order_date", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="customer", ref="#/components/schemas/Customer")
 * )
 */
class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * Order status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'order_number',
        'status',
        'total_amount',
        'currency',
        'notes',
        'order_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the order.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by customer.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    /**
     * Mark order as completed.
     */
    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }

    /**
     * Mark order as cancelled.
     */
    public function markAsCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }
}
