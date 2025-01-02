@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <p>Welcome to this beautiful admin panel.</p>
    <form method="GET" action="{{ route('voucher.search') }}" class="mb-3">
        <div class="input-group">
            <!-- Campo de búsqueda con icono -->
            <input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request('search') }}">

            <!-- Campos de rango de fechas -->
            <input type="date" name="start_date" class="form-control ml-2" value="{{ request('start_date') }}">
            <input type="date" name="end_date" class="form-control ml-2" value="{{ request('end_date') }}">

            <!-- Selección de campo -->
            <select name="search_by" class="form-control ml-2">
                <option value="all" {{ request('search_by') == 'all' ? 'selected' : '' }}>
                    <i class="fas fa-search"></i> Buscar en todos
                </option>
                <option value="invoice_id" {{ request('search_by') == 'invoice_id' ? 'selected' : '' }}>
                    <i class="fas fa-file-invoice"></i> Buscar por Serie
                </option>
                <option value="issuer_name" {{ request('search_by') == 'issuer_name' ? 'selected' : '' }}>
                    <i class="fas fa-user-tie"></i> Buscar por Emisor
                </option>
                <option value="receiver_name" {{ request('search_by') == 'receiver_name' ? 'selected' : '' }}>
                    <i class="fas fa-user"></i> Buscar por Receptor
                </option>
                <option value="user_name" {{ request('search_by') == 'user_name' ? 'selected' : '' }}>
                    <i class="fas fa-users"></i> Buscar por Usuario
                </option>
                <option value="line_description" {{ request('search_by') == 'line_description' ? 'selected' : '' }}>
                    <i class="fas fa-cogs"></i> Buscar por Descripción de Artículo
                </option>
                <option value="currency" {{ request('search_by') == 'currency' ? 'selected' : '' }}>
                    <i class="fas fa-money-bill-wave"></i> Buscar por Moneda
                </option>
            </select>

            <!-- Botón de búsqueda con icono -->
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>

            <!-- Botón para resetear la búsqueda -->
            <div class="input-group-append ml-2">
                <a href="{{ route('voucher.search') }}" class="btn btn-secondary">
                    <i class="fas fa-sync-alt"></i> Resetear
                </a>
            </div>
        </div>
    </form>




    <!-- Contenedor con scroll horizontal -->
    <div class="table-responsive p-2">
        <table class="table table-bordered table-hover table-striped text-sm" id="tableVoucher">
            <thead style="background-color: #007bff; color: white;">
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Serie</th>
                    <th>Fecha de Emisión</th>
                    <th>Moneda</th>
                    <th>Emisor</th>
                    <th>Receptor</th>
                    <th>Total</th>
                    <th>Detalles de Líneas</th>
                    <th>Impuestos</th>
                    <th>Cargos/Descuentos</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vouchers as $voucher)
                    <tr>
                        <td>{{ $voucher->id }}</td>
                        <td class="text-truncate" style="max-width: 100px;">{{ $voucher->user->name }}</td>
                        <td class="text-truncate" style="max-width: 100px;">{{ $voucher->invoice_id }}</td>
                        <td>{{ $voucher->issue_date }}</td>
                        <td>{{ $voucher->currency }}</td>
                        <td class="text-truncate" style="max-width: 200px;">{{ $voucher->issuer_name }}</td>
                        <td class="text-truncate" style="max-width: 200px;">{{ $voucher->receiver_name }}</td>
                        <td>{{ number_format($voucher->total_amount, 2) }}</td>
                        <td>
                            <!-- Botón para ver los detalles de las líneas -->
                            <button class="btn btn-info btn-sm" title="Detalles" data-toggle="modal" data-target="#modalDetails{{$voucher->id}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 2048 2048"><path fill="currentColor" d="m1344 2l704 352v785l-128-64V497l-512 256v258l-128 64V753L768 497v227l-128-64V354zm0 640l177-89l-463-265l-211 106zm315-157l182-91l-497-249l-149 75zm-507 654l-128 64v-1l-384 192v455l384-193v144l-448 224L0 1735v-676l576-288l576 288zm-640 710v-455l-384-192v454zm64-566l369-184l-369-185l-369 185zm576-1l448-224l448 224v527l-448 224l-448-224zm384 576v-305l-256-128v305zm384-128v-305l-256 128v305zm-320-288l241-121l-241-120l-241 120z"/></svg>
                            </button>
                        </td>
                        <td>
                            <!-- Botón para ver los impuestos -->
                            <button class="btn btn-warning btn-sm" title="Ver Impuestos" data-toggle="modal" data-target="#modalTaxes{{$voucher->id}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M8.487 21h7.026a4 4 0 0 0 3.808-5.224l-1.706-5.306A5 5 0 0 0 12.855 7h-1.71a5 5 0 0 0-4.76 3.47l-1.706 5.306A4 4 0 0 0 8.487 21M15 3q-1 4-3 4T9 3z"/><path d="M14 11h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3H10m2-7v1m0 6v1"/></g></svg>
                            </button>
                        </td>
                        <td>
                            <!-- Botón para ver cargos/descuentos -->
                            <button class="btn btn-success btn-sm" data-toggle="modal" title="Ver Cargos/Descuentos" data-target="#modalAllowances{{$voucher->id}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"><path fill="currentColor" d="M12.79 21L3 11.21v2c0 .53.21 1.04.59 1.41l7.79 7.79c.78.78 2.05.78 2.83 0l6.21-6.21c.78-.78.78-2.05 0-2.83z"/><path fill="currentColor" d="M11.38 17.41c.39.39.9.59 1.41.59s1.02-.2 1.41-.59l6.21-6.21c.78-.78.78-2.05 0-2.83L12.62.58C12.25.21 11.74 0 11.21 0H5C3.9 0 3 .9 3 2v6.21c0 .53.21 1.04.59 1.41zM5 2h6.21L19 9.79L12.79 16L5 8.21z"/><circle cx="7.25" cy="4.25" r="1.25" fill="currentColor"/></svg>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-3">
        {{ $vouchers->links('pagination::bootstrap-4') }}
    </div>

    <!-- Modal para los detalles de las líneas -->
    @foreach ($vouchers as $voucher)
        <div class="modal fade" id="modalDetails{{$voucher->id}}" tabindex="-1" role="dialog" aria-labelledby="modalDetailsLabel{{$voucher->id}}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalDetailsLabel{{$voucher->id}}">Detalles de Líneas - Voucher #{{$voucher->id}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-bordered" id="tableArticulos">
                            <thead>
                                <tr style="background-color: #eaf4fc;">
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Impuesto</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($voucher->voucherLines as $line)
                                    <tr>
                                        <td class="text-truncate" style="max-width: 150px;">{{ $line->description }}</td>
                                        <td>{{ number_format($line->quantity, 2) }}</td>
                                        <td>{{ number_format($line->unit_price, 2) }}</td>
                                        <td>{{ number_format($line->tax_amount, 2) }}</td>
                                        <td>{{ number_format($line->line_extension_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para los impuestos -->
        <div class="modal fade" id="modalTaxes{{$voucher->id}}" tabindex="-1" role="dialog" aria-labelledby="modalTaxesLabel{{$voucher->id}}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTaxesLabel{{$voucher->id}}">Impuestos - Voucher #{{$voucher->id}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-bordered" id="tableImpuestos">
                            <thead>
                                <tr>
                                    <th>Nombre del Impuesto</th>
                                    <th>Código del Impuesto</th>
                                    <th>Monto Gravable</th>
                                    <th>Monto del Impuesto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($voucher->taxTotals as $taxTotal)
                                    <tr>
                                        <td>{{ $taxTotal->tax_name }}</td>
                                        <td>{{ $taxTotal->tax_code }}</td>
                                        <td>{{ number_format($taxTotal->taxable_amount, 2) }}</td>
                                        <td>{{ number_format($taxTotal->tax_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para cargos/descuentos -->
        <div class="modal fade" id="modalAllowances{{$voucher->id}}" tabindex="-1" role="dialog" aria-labelledby="modalAllowancesLabel{{$voucher->id}}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAllowancesLabel{{$voucher->id}}">Cargos/Descuentos - Voucher #{{$voucher->id}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-bordered" id="tableMontos">
                            <thead>
                                <tr>
                                    <th>Indicador de Cargo</th>
                                    <th>Código del Motivo</th>
                                    <th>Monto Base</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($voucher->allowanceCharges as $allowance)
                                    <tr>
                                        <td>{{ $allowance->charge_indicator ? 'Cargo' : 'Descuento' }}</td>
                                        <td>{{ $allowance->reason_code }}</td>
                                        <td>{{ number_format($allowance->base_amount, 2) }}</td>
                                        <td>{{ number_format($allowance->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@stop

@section('css')
    {{-- Agregar aquí estilos extra si es necesario --}}
@stop

@section('js')
    <script>
        console.log("Hi, I'm using the Laravel-AdminLTE package!");
    </script>
    <script>
        $(document).ready(function() {
            // Inicializar las tablas de forma más compacta
            ['#tableVoucher', '#tableArticulos', '#tableImpuestos', '#tableMonto'].forEach(function(table) {
                $(table).DataTable({
                    dom: 'Bfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                    }
                });
            });
        });
    </script>

@stop
