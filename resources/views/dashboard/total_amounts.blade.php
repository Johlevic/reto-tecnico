@extends('adminlte::page')

@section('title', 'Total')

@section('content_header')

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@elseif(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@elseif(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@stop

@section('content')
<div class="container mb-5 max-h-screen p-0 shadow-sm">
    <!-- Contenedor para hacer la tabla responsiva con scroll horizontal -->
    <div class="table-responsive">
        <table id="voucher-table" class="table table-bordered table-hover table-striped">
            <thead>
                <tr class="bg-primary text-white">
                    <th class="px-4 py-2 text-left text-sm font-medium">Serie</th>
                    <th class="px-4 py-2 text-left text-sm font-medium">Número</th>
                    <th class="px-4 py-2 text-left text-sm font-medium">Fecha</th>
                    <th class="px-4 py-2 text-left text-sm font-medium">Moneda</th>
                    <th class="px-4 py-2 text-left text-sm font-medium">Total</th>
                    <th class="px-4 py-2 text-left text-sm font-medium">Cargo/Descuento</th>
                    <th class="px-4 py-2 text-left text-sm font-medium">Descripción</th>
                    <th class="px-4 py-2 text-left text-sm font-medium">Impuesto Total</th>
                    <th class="px-4 py-2 text-left text-sm font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vouchers as $voucher)
                    <tr class="border-t hover:bg-gray-100">
                        <td class="px-4 py-2 text-sm">{{ $voucher->invoice_id }}</td>
                        <td class="px-4 py-2 text-sm">{{ $voucher->issuer_document_number }}</td>
                        <td class="px-4 py-2 text-sm">{{ $voucher->issue_date }}</td>
                        <td class="px-4 py-2 text-sm">{{ $voucher->currency }}</td>

                        <!-- Total con símbolo de moneda -->
                        <td class="px-4 py-2 text-sm">
                            @if($voucher->currency == 'PEN')
                                S/ {{ number_format($voucher->total_amount, 2) }}
                            @elseif($voucher->currency == 'USD')
                                $ {{ number_format($voucher->total_amount, 2) }}
                            @else
                                {{ number_format($voucher->total_amount, 2) }}
                            @endif
                        </td>

                        <!-- Cargo/Descuento con símbolo de moneda -->
                        <td class="px-4 py-2 text-sm">
                            @foreach ($voucher->allowanceCharges as $charge)
                                @if($voucher->currency == 'PEN')
                                    S/ {{ number_format($charge->amount, 2) }}
                                @elseif($voucher->currency == 'USD')
                                    $ {{ number_format($charge->amount, 2) }}
                                @else
                                    {{ number_format($charge->amount, 2) }}
                                @endif
                            @endforeach
                        </td>

                        <!-- Descripción -->
                        <td class="px-4 py-2 text-sm">
                            @foreach ($voucher->voucherLines as $line)
                                {{ $line->description }}
                            @endforeach
                        </td>

                        <!-- Impuesto Total con símbolo de moneda -->
                        <td class="px-4 py-2 text-sm">
                            @foreach ($voucher->taxTotals as $tax)
                                @if($voucher->currency == 'PEN')
                                    S/ {{ number_format($tax->tax_amount, 2) }}
                                @elseif($voucher->currency == 'USD')
                                    $ {{ number_format($tax->tax_amount, 2) }}
                                @else
                                    {{ number_format($tax->tax_amount, 2) }}
                                @endif
                            @endforeach
                        </td>

                        <!-- Botones de acción -->
                        <td class="px-4 py-2 text-sm">
                            <a href="{{ route('voucher.edit', $voucher->id) }}" title="Editar" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('voucher.destroy', $voucher->id) }}" title="Eliminar" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este comprobante?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-2 text-center text-sm">No se encontraron comprobantes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $vouchers->links() }} <!-- Paginación -->
    </div>
</div>

@stop

@section('css')

@stop

@section('js')


<script>


    $(document).ready(function() {
            // Inicializar las tablas de forma más compacta
            ['#voucher-table'].forEach(function(table) {
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
