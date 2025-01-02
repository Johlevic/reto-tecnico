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
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json(['message' => 'Por favor, inicie sesión para registrar el comprobante.'], 401);
        }

        // Validar que 'files' es un array y cada archivo sea un XML válido
        $request->validate([
            'files' => 'required|array', // Asegúrate de que 'files' es un array
            'files.*' => 'mimes:xml|max:2048', // Validar que cada archivo es XML y tiene un tamaño máximo de 2MB
        ]);

        // Obtener todos los archivos del request
        $files = $request->file('files');
        $userId = Auth::id(); // Obtener el ID del usuario autenticado

        // Inicializar un array para almacenar los errores
        $errorFiles = [];

        // Iterar sobre los archivos y procesarlos
        foreach ($files as $file) {
            // Leer el contenido del archivo XML
            $xmlContent = file_get_contents($file);

            // Extraer los datos del XML
            $data = $this->voucherService->extractDataFromXml($xmlContent);

            // Verificar si ya existe un comprobante con el mismo invoice_id
            if (Voucher::where('invoice_id', $data['voucher']['invoice_id'])->exists()) {
                // Si ya existe, almacenar el error en cache y continuar con el siguiente archivo
                $errorFiles[] = [
                    'file_name' => $file->getClientOriginalName(),
                    'error_message' => 'Comprobante duplicado',

                ];

                continue;
            }

            // Verificar si los datos extraídos del XML son válidos
            if (empty($data['voucher']) || empty($data['voucher']['invoice_id'])) {
                // Almacenar el error en cache y continuar con el siguiente archivo
                $errorFiles[] = [
                    'file_name' => $file->getClientOriginalName(),
                    'error_message' => 'El archivo XML no contiene datos válidos.'
                ];
                continue;
            }

            // Enviar el Job a la cola para procesamiento (si los datos son válidos)
            Bus::dispatch(new SaveVoucherJob($data, $xmlContent, $userId, $file->getClientOriginalName()));
        }

        // Si hay errores, almacenarlos en el cache
        if (!empty($errorFiles)) {
            // Usamos el cache para almacenar los errores con una clave única
            cache()->put('voucher_upload_errors_' . $userId, $errorFiles, now()->addMinutes(10)); // Los errores se almacenan 10 minutos
            return redirect()->route('upload.errors')->with('alert', 'Algunos comprobantes no fueron registrados debido a errores.');
        }

        // Si solo se subió un archivo, el mensaje será específico para ello
        if (count($files) === 1) {
            return redirect()->back()->with('status', 'Comprobante registrado exitosamente.');
        }

        // Si varios archivos fueron subidos
        return redirect()->back()->with('status', 'Comprobantes registrados exitosamente.');
    }



    public function showUploadForm()
    {
         // Obtener los primeros 10 vouchers del usuario autenticado
         $userId = Auth::id(); // Obtener el ID del usuario autenticado

         // Obtener los 10 primeros vouchers de este usuario
         $vouchers = Voucher::where('user_id', $userId) // Filtrar por el ID del usuario
                             ->orderBy('created_at', 'desc') // Ordenar por fecha de creación (descendente)
                             ->limit(10) // Limitar a 10 registros
                             ->get();


        return view('voucher.upload', compact('vouchers'));
    }

    public function regularizarComprobantes()
{
    try {
        // Obtener todos los comprobantes que necesitan ser regularizados (extraídos de xml_content)
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

        // Extraer y regularizar los comprobantes
        foreach ($vouchers as $voucher) {
            // Aquí deberás extraer los datos desde el XML (o cualquier otro mecanismo que uses)
            $data = $this->voucherService->extractDataFromXml($voucher->xml_content);

            // Actualizar el comprobante con los datos extraídos
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

        // Respuesta exitosa con los comprobantes regularizados
        return response()->json([
            'status' => 'success',
            'message' => 'Comprobantes regularizados correctamente.',
            'data' => $regularizedVouchers
        ]);
    } catch (\Exception $e) {
        // Manejo de error
        return response()->json([
            'status' => 'error',
            'message' => 'Hubo un error al regularizar los comprobantes.',
            'details' => $e->getMessage()
        ], 500);  // Código de error 500 para errores internos
    }
}
public function showUploadErrors()
{
    $userId = Auth::id(); // Obtener el ID del usuario autenticado

    // Obtener los errores del cache
    $errors = cache()->get('voucher_upload_errors_' . $userId);

    // Pasar los errores a la vista
    return view('lister', compact('errors'));
}


public function getFilteredVouchers(Request $request)
{
    // Obtener el ID del usuario autenticado
    $userId = Auth::id();

    // Obtener los vouchers del usuario con paginación y sus relaciones
    $vouchers = Voucher::where('user_id', $userId) // Filtrar por el ID del usuario
                        ->with([
                            'allowanceCharges',  // Relación con allowance_charges
                            'voucherLines',      // Relación con voucher_lines
                            'taxTotals'          // Relación con tax_totals
                        ])
                        ->orderBy('created_at', 'desc')  // Ordenar por la fecha de creación
                        ->paginate(10);  // Paginación: 10 registros por página


                        // Obtener el ID del usuario autenticado
        $userId = Auth::id();

        // Inicializar la consulta base para los comprobantes
        $query = Voucher::where('user_id', $userId);

        // Aplicar filtros opcionales si están presentes en la solicitud
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

        // Obtener los comprobantes con paginación
        $vouchers = $query->orderBy('created_at', 'desc')->paginate(10);

        // Verificar si la solicitud es AJAX
        if ($request->ajax()) {
            return view('dashboard.total_amounts', compact('vouchers')); // Solo la parte de la vista que contiene la tabla
        }
    // Retornar la vista con los datos de los vouchers
    return view('dashboard.vouchers', compact('vouchers'));
}


public function getTotalAmountsByCurrency()
{
    // Obtener el ID del usuario autenticado
    $userId = Auth::id();

    // Consultar los montos totales acumulados por moneda (Soles y Dólares)
    $totals = Voucher::where('user_id', $userId)
                     ->selectRaw('currency, SUM(total_amount) as total_amount')
                     ->groupBy('currency')
                     ->whereIn('currency', ['PEN', 'USD']) // Filtrar solo Soles y Dólares
                     ->get();

    // Retornar la vista con los montos acumulados por moneda
    return view('dashboard.total_amounts', compact('totals'));
}

public function VoucherArticulos()
{
    // Verificar si el usuario está autenticado
    if (!Auth::check()) {
        // Redirigir al inicio de sesión con un mensaje de error
        return redirect()->route('login')->with('error', 'Por favor, inicie sesión para acceder a esta sección.');
    }

    // Obtener el ID del usuario autenticado
    $userId = Auth::id();

    // Cargar vouchers del usuario autenticado con relaciones y paginación
    $vouchers = Voucher::where('user_id', $userId)
        ->with(['voucherLines', 'user', 'taxTotals', 'allowanceCharges'])
        ->paginate(10);

    return view('dashboard.articulos', compact('vouchers'));
}



public function searchVoucherArticulos(Request $request)
{

    $search = $request->input('search');
    $searchBy = $request->input('search_by');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');


    $vouchers = Voucher::query()
        ->with(['voucherLines', 'user', 'taxTotals', 'allowanceCharges'])
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
        $vouchers = Voucher::paginate(10); // Traer los comprobantes paginados
        return view('dashboard.articulos', compact('vouchers'));
    }

    // Mostrar el formulario de edición de un comprobante
    public function edit($id)
    {
        $voucher = Voucher::findOrFail($id);

        // Convertir la fecha de emisión a un objeto Carbon si no lo es
        $voucher->issue_date = Carbon::parse($voucher->issue_date);

        return view('dashboard.edit-voucher', compact('voucher'));
    }

    // Actualizar un comprobante
    public function update(Request $request, $id)
    {
        // Validación de los campos
        $request->validate([
            'invoice_id' => 'required|string',
            'issue_date' => 'required|date',
            'currency' => 'required|string',
            'total_amount' => 'required|numeric',
            // Otros campos también pueden ser validados si es necesario
        ]);

        // Encontrar el comprobante por ID
        $voucher = Voucher::findOrFail($id);

        // Actualizar los campos del comprobante
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

        // Redirigir a la lista de comprobantes con un mensaje de éxito
        return redirect()->route('vouchers')->with('success', 'Comprobante actualizado correctamente');
    }

    // Eliminar un comprobante
    public function destroy($id)
    {
        $voucher = Voucher::findOrFail($id); // Encontrar el comprobante por ID
        $voucher->delete(); // Eliminar el comprobante

        return redirect()->back()->with('success', 'Comprobante eliminado correctamente');
    }




}
