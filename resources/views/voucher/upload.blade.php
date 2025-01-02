@extends('adminlte::page')

@section('title', 'Registro de Comprobante')

@section('content')

<div class="container p-3">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h4 class="text-center mb-4 text-primary">Subir Comprobante XML</h4>

            <hr >

            <form action="{{ route('voucher.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file" class="col-form-label">Selecciona archivos XML</label>
                    <div class="input-group">
                        <div class="custom-file">
                            <!-- Atributo 'multiple' para cargar varios archivos -->
                            <input type="file" class="custom-file-input" id="file" name="files[]" accept=".xml" required multiple>
                            <label class="custom-file-label" for="file">Elige archivos</label>
                        </div>
                    </div>

                    <!-- Contenedor para mostrar los íconos y nombres de los archivos seleccionados -->
                    <div id="fileList" class="mt-2 d-flex flex-wrap"></div>

                    @error('files')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-block">Subir Comprobantes</button>
            </form>


            <!-- Mostrar mensajes de éxito, error y alerta -->
            @if(session('status'))
                <script>
                    window.onload = function() {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: '{{ session('status') }}',
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            timer: 5000,
                        });
                    }
                </script>
            @endif

            @if(session('error'))
                <script>
                    window.onload = function() {
                        Swal.fire({
                            icon: 'error',
                            title: '¡Error!',
                            text: '{{ session('error') }}',
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            showCancelButton: true,
                            cancelButtonText: 'Cancelar',
                            timer: 5000,
                        });
                    }
                </script>
            @endif

            @if(session('alert'))
                <script>
                    window.onload = function() {
                        Swal.fire({
                            icon: 'warning',
                            title: '¡Alerta!',
                            text: '{{ session('alert') }}',
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            showCancelButton: true,
                            cancelButtonText: 'Cancelar',
                            timer: 5000,
                        });
                    }
                </script>
            @endif

        </div>
    </div>
</div>

<div class="container mb-0 p-0">
    <!-- Card -->
    <div class="card">
        <!-- Card Header -->
        <div class="card-header bg-primary text-white">
            <h4 class="text-center mb-0 ">Comprobantes Registrados</h4>
        </div>

        <!-- Card Body -->
        <div class="card-body">
            <!-- Tabla de Vouchers -->
            <table id="vouchersTable" class="table table-bordered table-striped text-sm table-responsive">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Emisor</th>
                        <th>Receptor</th>
                        <th>Total</th>
                        <th>Moneda</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vouchers as $voucher)
                        <tr>
                            <td>{{ $voucher->invoice_id }}</td>
                            <td>{{ $voucher->issuer_name }} ({{ $voucher->issuer_document_number }})</td>
                            <td>{{ $voucher->receiver_name }} ({{ $voucher->receiver_document_number }})</td>
                            <td>{{ $voucher->currency == 'PEN' ? 'S/ ' . number_format($voucher->total_amount, 2) : '$ ' . number_format($voucher->total_amount, 2) }}</td>
                            <td>{{ $voucher->currency }}</td>
                            <td>{{ $voucher->issue_date }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Card Footer -->
        <div class="card-footer text-center">
            <p class="text-muted mb-0">Total de comprobantes registrados: {{ $vouchers->count() }}</p>
        </div>
    </div>
</div>

<div class="p-3 float-right col-md-4">
    <!-- Botón con ícono que redirige a una nueva URL -->
    <a href="{{ route('vouchers') }}" class="btn btn-success d-flex align-items-center">
        <i class="fas fa-plus-circle mr-2"></i> Ver más
        <!-- Ícono de flecha que se mueve -->
        <i class="fas fa-arrow-right ml-2 arrow-moving"></i>
    </a>
</div>




@stop

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.16/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    @keyframes moveArrow {
        0% {
            transform: translateX(0);
        }
        50% {
            transform: translateX(5px);
        }
        100% {
            transform: translateX(0);
        }
    }
    .arrow-moving {
        animation: moveArrow 1s ease-in-out infinite;
    }
</style>

@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.16/dist/sweetalert2.min.js"></script>


    <script>
        $(document).ready(function() {
            $('#vouchersTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
        });

        // Mostrar los íconos y nombres de los archivos seleccionados
document.getElementById("file").addEventListener("change", function() {
    var fileList = this.files;
    var fileListContainer = document.getElementById("fileList");

    // Limpiar el contenedor de archivos previos
    fileListContainer.innerHTML = '';

    // Si no hay archivos seleccionados
    if (fileList.length === 0) {
        fileListContainer.innerHTML = "<p class='text-muted'>Ningún archivo seleccionado</p>";
        return;
    }

    // Iterar a través de los archivos seleccionados y mostrar el ícono y nombre
    Array.from(fileList).forEach(file => {
        var fileWrapper = document.createElement("div");
        fileWrapper.classList.add("file-item", "text-center", "mr-3", "mb-3");

        // Crear el ícono del archivo (usaremos un ícono genérico de archivo)
        var fileIcon = document.createElement("i");
        fileIcon.classList.add("fas", "fa-file", "fa-3x", "text-primary");

        // Crear el nombre del archivo
        var fileName = document.createElement("p");
        fileName.classList.add("mt-2", "text-muted");
        fileName.textContent = file.name;

        // Agregar el ícono y el nombre al contenedor
        fileWrapper.appendChild(fileIcon);
        fileWrapper.appendChild(fileName);

        // Agregar el contenedor de archivo al contenedor principal
        fileListContainer.appendChild(fileWrapper);
    });
});

    </script>
@endpush
