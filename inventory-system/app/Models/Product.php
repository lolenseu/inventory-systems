<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'sku',
        'name',
        'description',
        'quantity',
        'price',
        'supplier_id',
        'image',
        'image_type',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    protected $appends = ['image_url'];

    public function supplier()
      {
        return $this->belongsTo(Supplier::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image && $this->image_type) {
            return 'data:' . $this->image_type . ';base64,' . base64_encode($this->image);
        }
        return null;
    }
}
