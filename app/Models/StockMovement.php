<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id','warehouse_id','quantity','type','movement_date'
    ];
    protected $casts = ['movement_date'=>'date'];

    public function product() { 
    	return $this->belongsTo(Product::class); 
    }
    
    public function warehouse() { 
    	return $this->belongsTo(Warehouse::class); 
    }

    // convenience accessor
    public function getSignedQtyAttribute(): int
    {
        return $this->type === 'in' ? $this->quantity : -$this->quantity;
    }
}
