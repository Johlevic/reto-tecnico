@extends('adminlte::page')

@section('title', 'Lista de comprobantes no registrados')

@section('content')
<div class="container">

    <h4 class="text-center text-primary p-3 ">Errores de carga de comprobantes</h4>
    <hr>

    @if (isset($errors) && count($errors) > 0)
        <div class="alert alert-warning">
            <strong>Se encontraron los siguientes errores:</strong>
        </div>

        <table class="table table-bordered" id="errorsTable">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th><i class="fas fa-file"></i> Archivo</th>
                    <th><i class="fas fa-exclamation-triangle text-danger"></i> Error</th>
                </tr>
            </thead>
            <tbody>
                @foreach($errors as $index => $error)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $error['file_name'] }}</td>
                        <td class="text-danger">{{ $error['error_message'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">
            <a href="/comprobante/subir" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Retornar
            </a>
        </div>
    @else
        <div class="alert alert-info">
            No hay errores de carga de comprobantes recientes.
        </div>
    @endif
</div>
@stop


@push('css')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.16/dist/sweetalert2.min.css" rel="stylesheet">

@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.16/dist/sweetalert2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#errorsTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
        });

        // Mostrar los nombres de los archivos seleccionados
    document.getElementById("file").addEventListener("change", function() {
        var fileName = this.files.length ? Array.from(this.files).map(file => file.name).join(', ') : "Ningún archivo seleccionado";
        document.getElementById("fileName").textContent = "Archivos seleccionados: " + fileName;

        // Actualizar el nombre en el label del input de archivo
        var fileInput = this;
        var label = fileInput.nextElementSibling;
        label.textContent = fileName;
    });
    </script>
@endpush
