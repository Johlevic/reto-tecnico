<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'voucher_id', 'line_id', 'description', 'quantity',
        'unit_price', 'line_extension_amount', 'tax_amount', 'item_id',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
