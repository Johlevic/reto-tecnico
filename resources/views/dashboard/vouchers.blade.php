@extends('adminlte::page')

@section('title', 'Dashboard')

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

<div class="card card-primary  overflow-auto">
    <div class="card-header">
        <h3 class="card-title">Filtros de Consulta</h3>
    </div>
    <div class="card-body">
        <form id="filter-form" method="GET" action="{{ route('vouchers') }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-2">
                    <label for="serie">Serie</label>
                    <input type="text" name="serie" class="form-control" placeholder="Serie" value="{{ request()->serie }}">
                </div>
                <div class="col-md-2">
                    <label for="numero">Número</label>
                    <input type="text" name="numero" class="form-control" placeholder="Número" value="{{ request()->numero }}">
                </div>
                <div class="col-md-2">
                    <label for="tipo_comprobante">Tipo de Comprobante</label>
                    <select name="tipo_comprobante" class="form-control">
                        <option value="">Tipo de Comprobante</option>
                        <option value="Factura" {{ request()->tipo_comprobante == 'Factura' ? 'selected' : '' }}>Factura</option>
                        <option value="Boleta" {{ request()->tipo_comprobante == 'Boleta' ? 'selected' : '' }}>Boleta</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="moneda">Moneda</label>
                    <select name="moneda" class="form-control">
                        <option value="">Moneda</option>
                        <option value="PEN" {{ request()->moneda == 'PEN' ? 'selected' : '' }}>Soles</option>
                        <option value="USD" {{ request()->moneda == 'USD' ? 'selected' : '' }}>Dólares</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="fecha_inicio">Fecha de Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ request()->fecha_inicio }}">
                </div>
                <div class="col-md-2">
                    <label for="fecha_fin">Fecha de Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ request()->fecha_fin }}">
                </div>

                <div class="col-md-2 mt-5 col-md-12 ">
                    <button type="submit" class="btn btn-primary mr-3">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="/comprobantes-registrados"  class="btn btn-secondary" id="reset-filters">
                        <i class="fas fa-undo"></i> Restablecer
                    </a>
                </div>
            </div>
        </form>

    </div>
</div>





@stop

@section('content')

<div class="container ">
    @include('dashboard.total_amounts', ['vouchers' => $vouchers])
</div>




@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Enviar formulario de filtros usando AJAX
            $('#filter-form').on('submit', function(e) {
                e.preventDefault(); // Evitar el comportamiento por defecto del formulario

                $.ajax({
                    url: '{{ route('vouchers') }}', // URL de la ruta donde se procesarán los filtros
                    method: 'GET',
                    data: $(this).serialize(), // Pasar los filtros seleccionados
                    success: function(response) {
                        $('#voucher-list').html(response); // Actualizar solo el contenido de la tabla
                    },
                    error: function(xhr, status, error) {
                        alert('Hubo un error al aplicar los filtros.');
                    }
                });
            });
        });
    </script>


@stop
