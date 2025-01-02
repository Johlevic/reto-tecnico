<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class allowanceCharge extends Model
{
    use HasFactory;


    protected $table = 'allowance_charges';


    protected $fillable = [
        'id',
        'voucher_id',
        'charge_indicator',
        'reason_code',
        'amount',
        'base_amount'
    ];


    protected $keyType = 'string';


    public $timestamps = true;

    
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
