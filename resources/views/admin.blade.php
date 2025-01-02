@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="row">
    <!-- Total de Vouchers -->
    <div class="col-lg-3 col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-header">
                <i class="fas fa-file-alt"></i> Total de Comprobantes
            </div>
            <div class="card-body">
                <h5 class="card-title">{{ $totalVouchers }}</h5>
            </div>
        </div>
    </div>

    <!-- Ganancias Totales -->
    <div class="col-lg-3 col-md-6">
        <div class="card bg-success text-white">
            <div class="card-header">
                <i class="fas fa-dollar-sign"></i> Ganancias Totales
            </div>
            <div class="card-body">
                <h5 class="card-title">S/ {{ number_format($totalGanancias, 2) }}</h5>
            </div>
        </div>
    </div>

    <!-- Total de Impuestos -->
    <div class="col-lg-3 col-md-6">
        <div class="card bg-warning text-white">
            <div class="card-header">
                <i class="fas fa-file-invoice-dollar"></i> Total de Impuestos
            </div>
            <div class="card-body">
                <h5 class="card-title">S/ {{ number_format($totalImpuestos, 2) }}</h5>
            </div>
        </div>
    </div>

    <!-- Total de Usuarios -->
    <div class="col-lg-3 col-md-6">
        <div class="card bg-info text-white">
            <div class="card-header">
                <i class="fas fa-users"></i> Usuarios Registrados
            </div>
            <div class="card-body">
                <h5 class="card-title">{{ $totalUsuarios }}</h5>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Total de Productos Registrados -->
    <div class="col-lg-3 col-md-6">
        <div class="card bg-secondary text-white">
            <div class="card-header">
                <i class="fas fa-cogs"></i> Productos Registrados
            </div>
            <div class="card-body">
                <h5 class="card-title">{{ $totalProductos }}</h5>
            </div>
        </div>
    </div>

    <!-- Total de Valor de Productos -->
    <div class="col-lg-3 col-md-6">
        <div class="card bg-danger text-white">
            <div class="card-header">
                <i class="fas fa-tags"></i> Valor Total de Productos
            </div>
            <div class="card-body">
                <h5 class="card-title">S/ {{ number_format($totalValorProductos, 2) }}</h5>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-green text-center">
                <h6>Artículos Registrados</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped text-sm" id="tableArticulos">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach($voucherLines as $line)
                            <tr>
                                <td>{{ $line->description }}</td>
                                <td>{{ number_format($line->quantity, 2) }}</td>
                                <td>S/ {{ number_format($line->unit_price, 2) }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="row mt-4">
    <!-- Gráfico de Día con Más Registros -->
    <div class="col-lg-6 col-md-6">
        <div class="card">
            <div class="card-header bg-warning">Día con Más Registros <i class="fas fa-calendar-day"></i></div>
            <div class="card-body">
                <h5 class="card-title">{{ $diaMasRegistros->day }} - {{ $diaMasRegistros->count }} registros</h5>
            </div>
        </div>
    </div>

    <!-- Usuarios con Más Ganancias -->
    <div class="col-lg-6 col-md-6">
        <div class="card">
            <div class="card-header bg-primary">Usuarios con Más Ganancias <i class="fas fa-users"></i></div>
            <div class="card-body">
                <ul class="styled-list">
                    @foreach ($usuariosConMasGanancias as $usuario)
                        <li>User ID: {{ $usuario->user_id }} - Ganancias: S/ {{ number_format($usuario->total, 2) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>



<div class="row mt-4">
    <!-- Gráfico de Ganancias por Día -->
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header bg-red">
                <i class="fas fa-chart-bar"></i> Ganancias por Día
            </div>
            <div class="card-body">
                <canvas id="barChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>


    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header bg-success">
                <i class="fas fa-chart-line"></i> Impuestos por Mes
            </div>
            <div class="card-body">
                <canvas id="lineChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

@stop

@push('css')
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Gráfico de barras para Ganancias por Día
    var ctxBar = document.getElementById('barChart').getContext('2d');
    var barChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: @json($gananciasPorDia->pluck('day')),
            datasets: [{
                label: 'Ganancias por Día',
                data: @json($gananciasPorDia->pluck('total')),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        }
    });


    var ctxLine = document.getElementById('lineChart').getContext('2d');
    var lineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: @json($impuestosPorMes->pluck('month')),
            datasets: [{
                label: 'Impuestos por Mes',
                data: @json($impuestosPorMes->pluck('total')),
                fill: false,
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.1
            }]
        }
    });

    $(document).ready(function() {
    // Inicializar las tablas de forma más compacta
    ['#tableArticulos'].forEach(function(table) {
        $(table).DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            pageLength: 2,
            lengthMenu: [2, 5, 10, 25, 50],
        });
    });
});

</script>

@endpush
