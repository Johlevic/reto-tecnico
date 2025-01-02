<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'user_id', 'invoice_id', 'issue_date', 'issue_time', 'currency', 'issuer_name',
        'issuer_document_type', 'issuer_document_number', 'receiver_name', 'receiver_document_number',
        'payable_amount', 'xml_content', 'receiver_document_type','total_amount',  // nuevo campo
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function allowanceCharges()
    {
        return $this->hasMany(AllowanceCharge::class);
    }

    public function taxTotals()
    {
        return $this->hasMany(TaxTotal::class);
    }

    public function voucherLines()
    {
        return $this->hasMany(VoucherLine::class);
    }

     // Relación con tax_totals
    


}
