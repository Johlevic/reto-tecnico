<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Jobs\SaveVoucherJob;
use App\Models\Voucher;
use App\Models\VoucherLine;
use App\Services\VoucherService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Guid\Guid;

class VoucherController extends Controller
{





    protected $voucherService;


    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    public function upload(Request $request)
    {

        if (!Auth::check()) {
            return response()->json(['message' => 'Por favor, inicie sesión para registrar el comprobante.'], 401);
        }


        $request->validate([
            'files' => 'required|array',
            'files.*' => 'mimes:xml|max:2048',
        ]);


        $files = $request->file('files');
        $userId = Auth::id();


        $errorFiles = [];


        foreach ($files as $file) {

            $xmlContent = file_get_contents($file);


            $data = $this->voucherService->extractDataFromXml($xmlContent);


            if (Voucher::where('invoice_id', $data['voucher']['invoice_id'])->exists()) {

                $errorFiles[] = [
                    'file_name' => $file->getClientOriginalName(),
                    'error_message' => 'Comprobante duplicado',

                ];

                continue;
            }


            if (empty($data['voucher']) || empty($data['voucher']['invoice_id'])) {

                $errorFiles[] = [
                    'file_name' => $file->getClientOriginalName(),
                    'error_message' => 'El archivo XML no contiene datos válidos.'
                ];
                continue;
            }


            Bus::dispatch(new SaveVoucherJob($data, $xmlContent, $userId, $file->getClientOriginalName()));
        }


        if (!empty($errorFiles)) {

            cache()->put('voucher_upload_errors_' . $userId, $errorFiles, now()->addMinutes(10)); // Los errores se almacenan 10 minutos
            return redirect()->route('upload.errors')->with('alert', 'Algunos comprobantes no fueron registrados debido a errores.');
        }


        if (count($files) === 1) {
            return redirect()->back()->with('status', 'Comprobante registrado exitosamente.');
        }


        return redirect()->back()->with('status', 'Comprobantes registrados exitosamente.');
    }



    public function showUploadForm()
    {

        $userId = Auth::id();


        $vouchers = Voucher::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();


        return view('voucher.upload', compact('vouchers'));
    }

    public function regularizarComprobantes()
    {
        try {

            $vouchers = Voucher::whereNull('serie')
                ->orWhereNull('numero')
                ->orWhereNull('tipo_comprobante')
                ->orWhereNull('moneda')
                ->get();

            if ($vouchers->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No hay comprobantes para regularizar.',
                    'data' => []
                ]);
            }

            $regularizedVouchers = [];


            foreach ($vouchers as $voucher) {

                $data = $this->voucherService->extractDataFromXml($voucher->xml_content);


                $voucher->serie = $data['serie'];
                $voucher->numero = $data['numero'];
                $voucher->tipo_comprobante = $data['tipo_comprobante'];
                $voucher->moneda = $data['moneda'];
                $voucher->save();

                $regularizedVouchers[] = [
                    'comprobante_id' => $voucher->id,
                    'serie' => $voucher->serie,
                    'numero' => $voucher->numero,
                    'tipo_comprobante' => $voucher->tipo_comprobante,
                    'moneda' => $voucher->moneda,
                ];
            }


            return response()->json([
                'status' => 'success',
                'message' => 'Comprobantes regularizados correctamente.',
                'data' => $regularizedVouchers
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un error al regularizar los comprobantes.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function showUploadErrors()
    {
        $userId = Auth::id();


        $errors = cache()->get('voucher_upload_errors_' . $userId);


        return view('lister', compact('errors'));
    }


    public function getFilteredVouchers(Request $request)
    {

        $userId = Auth::id();


        $vouchers = Voucher::where('user_id', $userId)
            ->with([
                'allowanceCharges',
                'voucherLines',
                'taxTotals'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);



        $userId = Auth::id();


        $query = Voucher::where('user_id', $userId);


        if ($request->has('serie') && !empty($request->serie)) {
            $query->where('invoice_id', 'like', $request->serie . '%');
        }

        if ($request->has('numero') && !empty($request->numero)) {
            $query->where('invoice_id', 'like', '%' . $request->numero);
        }

        if ($request->has('tipo_comprobante') && !empty($request->tipo_comprobante)) {
            $query->where('issuer_document_type', $request->tipo_comprobante);
        }

        if ($request->has('moneda') && !empty($request->moneda)) {
            $query->where('currency', $request->moneda);
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                $request->fecha_inicio . ' 00:00:00',
                $request->fecha_fin . ' 23:59:59'
            ]);
        }


        $vouchers = $query->orderBy('created_at', 'desc')->paginate(10);


        if ($request->ajax()) {
            return view('dashboard.total_amounts', compact('vouchers'));
        }

        return view('dashboard.vouchers', compact('vouchers'));
    }


    public function getTotalAmountsByCurrency()
    {

        $userId = Auth::id();


        $totals = Voucher::where('user_id', $userId)
            ->selectRaw('currency, SUM(total_amount) as total_amount')
            ->groupBy('currency')
            ->whereIn('currency', ['PEN', 'USD']) // Filtrar solo Soles y Dólares
            ->get();


        return view('dashboard.total_amounts', compact('totals'));
    }

    public function VoucherArticulos()
    {

        if (!Auth::check()) {

            return redirect()->route('login')->with('error', 'Por favor, inicie sesión para acceder a esta sección.');
        }


        $userId = Auth::id();


        $vouchers = Voucher::where('user_id', $userId)
            ->with(['voucherLines', 'user', 'taxTotals', 'allowanceCharges'])
            ->paginate(10);

        return view('dashboard.articulos', compact('vouchers'));
    }



    public function searchVoucherArticulos(Request $request)
    {

        $userId = Auth::id();


        $search = $request->input('search');
        $searchBy = $request->input('search_by');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');


        $vouchers = Voucher::query()
            ->with(['voucherLines', 'user', 'taxTotals', 'allowanceCharges'])
            ->where('user_id', $userId) // Filtrar solo los comprobantes del usuario autenticado
            ->when($search, function ($query, $search) use ($searchBy) {
                return $query->where(function ($query) use ($search, $searchBy) {
                    if ($searchBy === 'created_at') {
                        $query->whereDate('created_at', '=', $search);
                    } else {
                        $query->where('invoice_id', 'like', "%$search%")
                            ->orWhere('currency', 'like', "%$search%")
                            ->orWhere('issuer_name', 'like', "%$search%")
                            ->orWhere('receiver_name', 'like', "%$search%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%$search%");
                            })
                            ->orWhereHas('voucherLines', function ($lineQuery) use ($search) {
                                $lineQuery->where('description', 'like', "%$search%")
                                    ->orWhere('item_id', 'like', "%$search%")
                                    ->orWhere('unit_price', 'like', "%$search%");
                            })
                            ->orWhereHas('taxTotals', function ($taxQuery) use ($search) {
                                $taxQuery->where('tax_name', 'like', "%$search%")
                                    ->orWhere('tax_code', 'like', "%$search%");
                            })
                            ->orWhereHas('allowanceCharges', function ($chargeQuery) use ($search) {
                                $chargeQuery->where('reason_code', 'like', "%$search%");
                            });
                    }
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when($startDate && !$endDate, function ($query) use ($startDate) {
                return $query->where('created_at', '>=', $startDate);
            })
            ->when(!$startDate && $endDate, function ($query) use ($endDate) {
                return $query->where('created_at', '<=', $endDate);
            })
            ->paginate(10);


        return view('dashboard.articulos', compact('vouchers'));
    }





    public function index()
    {
        $vouchers = Voucher::paginate(10);
        return view('dashboard.articulos', compact('vouchers'));
    }


    public function edit($id)
    {
        $voucher = Voucher::findOrFail($id);


        $voucher->issue_date = Carbon::parse($voucher->issue_date);

        return view('dashboard.edit-voucher', compact('voucher'));
    }


    public function update(Request $request, $id)
    {

        $request->validate([
            'invoice_id' => 'required|string',
            'issue_date' => 'required|date',
            'currency' => 'required|string',
            'total_amount' => 'required|numeric',

        ]);


        $voucher = Voucher::findOrFail($id);


        $voucher->update([
            'invoice_id' => $request->input('invoice_id'),
            'issue_date' => $request->input('issue_date'),
            'issue_time' => $request->input('issue_time'),
            'currency' => $request->input('currency'),
            'issuer_name' => $request->input('issuer_name'),
            'issuer_document_type' => $request->input('issuer_document_type'),
            'issuer_document_number' => $request->input('issuer_document_number'),
            'receiver_name' => $request->input('receiver_name'),
            'receiver_document_number' => $request->input('receiver_document_number'),
            'total_amount' => $request->input('total_amount'),
            'payable_amount' => $request->input('payable_amount'),
            'xml_content' => $request->input('xml_content'),
        ]);


        return redirect()->route('vouchers')->with('success', 'Comprobante actualizado correctamente');
    }


    public function destroy($id)
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->delete(); 

        return redirect()->back()->with('success', 'Comprobante eliminado correctamente');
    }
}
