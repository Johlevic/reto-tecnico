<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class taxTotal extends Model
{
    use HasFactory;

    // Definir el nombre de la tabla (si es diferente del nombre plural del modelo)
    protected $table = 'tax_totals';

    // Asegurarse de que no intente asignar los campos `id`, `created_at`, `updated_at` si no se están usando
    protected $fillable = [
        'id',
        'voucher_id',
        'tax_amount',
        'taxable_amount',
        'tax_name',
        'tax_code'
    ];

    // Especificar que la clave primaria es un UUID
    protected $keyType = 'string';

    // Si no estás utilizando timestamps, puedes desactivar esta opción
    public $timestamps = true;

    // Relación con el modelo Voucher (un tax_total pertenece a un voucher)
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
