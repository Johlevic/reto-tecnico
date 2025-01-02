<?php

namespace App\Http\Controllers;

use App\Models\taxTotal;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{



    public function index()
    {

        $totalVouchers = Voucher::count();

        $totalGanancias = Voucher::sum('total_amount');


        $totalImpuestos = TaxTotal::sum('tax_amount');


        $totalUsuarios = DB::table('users')->count();


        $totalProductos = VoucherLine::count();


        $totalValorProductos = VoucherLine::sum('line_extension_amount');


        // Si solo necesitas el primer resultado
        $diaMasRegistros = Voucher::selectRaw('DATE(created_at) as day, COUNT(*) as count')
        ->groupBy('day')
        ->orderByDesc('count')
        ->limit(1)  // Solo obtiene el día con más registros
        ->first();  // Obtener el primer resultado como un solo objeto



        $usuariosConMasGanancias = Voucher::select('user_id', DB::raw('SUM(total_amount) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();


        $gananciasPorDia = Voucher::select(DB::raw('DATE(issue_date) as day'), DB::raw('SUM(total_amount) as total'))
            ->groupBy(DB::raw('DATE(issue_date)'))
            ->orderBy('day')
            ->get();


        $impuestosPorMes = TaxTotal::select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(tax_amount) as total'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();


            $voucherLines = \App\Models\VoucherLine::with('voucher') // Si necesitas más detalles del voucher
            ->get();


        return view('admin', compact(
            'totalVouchers',
            'totalGanancias',
            'totalImpuestos',
            'totalUsuarios',
            'totalProductos',
            'totalValorProductos',
            'diaMasRegistros',
            'usuariosConMasGanancias',
            'gananciasPorDia',
            'impuestosPorMes',
            'voucherLines'
        ));
    }


}
