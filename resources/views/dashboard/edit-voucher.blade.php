@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h5 class=" col-md-12 text-center">Editar Comprobante</h5>
@stop

@section('content')



    <div class="card mb-5 max-h-screen p-5 ">

        <!-- Mensajes de éxito, error y advertencia -->
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

        <form action="{{ route('voucher.update', $voucher->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Serie -->
                <div class="col-md-6 mb-3">
                    <label for="invoice_id" class="form-label">
                        <i class="fas fa-file-invoice"></i> Serie
                    </label>
                    <input type="text" class="form-control" id="invoice_id" name="invoice_id" value="{{ old('invoice_id', $voucher->invoice_id) }}" required>
                </div>

                <!-- Fecha de emisión -->
                <div class="col-md-6 mb-3">
                    <label for="issue_date" class="form-label">
                        <i class="fas fa-calendar-alt"></i> Fecha de Emisión
                    </label>
                    <input type="date" class="form-control" id="issue_date" name="issue_date" value="{{ old('issue_date', $voucher->issue_date->toDateString()) }}" required>
                </div>

                <!-- Hora de emisión -->
                <div class="col-md-6 mb-3">
                    <label for="issue_time" class="form-label">
                        <i class="fas fa-clock"></i> Hora de Emisión
                    </label>
                    <input type="time" class="form-control" id="issue_time" name="issue_time" value="{{ old('issue_time', $voucher->issue_time) }}">
                </div>

                <!-- Moneda -->
                <div class="col-md-6 mb-3">
                    <label for="currency" class="form-label">
                        <i class="fas fa-money-bill-wave"></i> Moneda
                    </label>
                    <input type="text" class="form-control" id="currency" name="currency" value="{{ old('currency', $voucher->currency) }}" required>
                </div>

                <!-- Nombre del Emisor -->
                <div class="col-md-6 mb-3">
                    <label for="issuer_name" class="form-label">
                        <i class="fas fa-user-tie"></i> Nombre del Emisor
                    </label>
                    <input type="text" class="form-control" id="issuer_name" name="issuer_name" value="{{ old('issuer_name', $voucher->issuer_name) }}" required>
                </div>

                <!-- Tipo de documento del Emisor -->
                <div class="col-md-6 mb-3">
                    <label for="issuer_document_type" class="form-label">
                        <i class="fas fa-id-card"></i> Tipo de Documento del Emisor
                    </label>
                    <input type="text" class="form-control" id="issuer_document_type" name="issuer_document_type" value="{{ old('issuer_document_type', $voucher->issuer_document_type) }}">
                </div>

                <!-- Número de documento del Emisor -->
                <div class="col-md-6 mb-3">
                    <label for="issuer_document_number" class="form-label">
                        <i class="fas fa-id-card-alt"></i> Número de Documento del Emisor
                    </label>
                    <input type="text" class="form-control" id="issuer_document_number" name="issuer_document_number" value="{{ old('issuer_document_number', $voucher->issuer_document_number) }}">
                </div>

                <!-- Nombre del Receptor -->
                <div class="col-md-6 mb-3">
                    <label for="receiver_name" class="form-label">
                        <i class="fas fa-user"></i> Nombre del Receptor
                    </label>
                    <input type="text" class="form-control" id="receiver_name" name="receiver_name" value="{{ old('receiver_name', $voucher->receiver_name) }}">
                </div>

                <!-- Número de documento del Receptor -->
                <div class="col-md-6 mb-3">
                    <label for="receiver_document_number" class="form-label">
                        <i class="fas fa-id-card-alt"></i> Número de Documento del Receptor
                    </label>
                    <input type="text" class="form-control" id="receiver_document_number" name="receiver_document_number" value="{{ old('receiver_document_number', $voucher->receiver_document_number) }}">
                </div>

                <!-- Monto Total -->
                <div class="col-md-6 mb-3">
                    <label for="total_amount" class="form-label">
                        <i class="fas fa-calculator"></i> Monto Total
                    </label>
                    <input type="number" class="form-control" id="total_amount" name="total_amount" value="{{ old('total_amount', $voucher->total_amount) }}" required>
                </div>

                <!-- Monto a Pagar -->
                <div class="col-md-6 mb-3">
                    <label for="payable_amount" class="form-label">
                        <i class="fas fa-credit-card"></i> Monto a Pagar
                    </label>
                    <input type="number" class="form-control" id="payable_amount" name="payable_amount" value="{{ old('payable_amount', $voucher->payable_amount) }}">
                </div>

                <!-- Contenido XML -->
                <div class="col-12 mb-3">
                    <label for="xml_content" class="form-label">
                        <i class="fas fa-file-code"></i> Contenido XML
                    </label>
                    <textarea class="form-control" id="xml_content" name="xml_content">{{ old('xml_content', $voucher->xml_content) }}</textarea>
                </div>
            </div>

            <!-- Botones -->
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar
                </button>
                <a href="{{ route('voucher.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
@stop

@section('js')
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
@stop
