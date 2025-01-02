<?php

namespace App\Jobs;

use App\Models\Voucher;
use App\Models\VoucherLine;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SaveVoucherJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $data;
    protected $xmlContent;
    protected $userId;

    /**
     * Crear una nueva instancia del job.
     */
    public function __construct(array $data, string $xmlContent, $userId)
    {
        $this->data = $data;
        $this->xmlContent = $xmlContent;
        $this->userId = $userId;
    }

    /**
     * Ejecutar el job.
     */
    public function handle()
    {
        $userId = $this->userId;


        if (empty($userId) || $userId == '0') {
            throw new Exception("El user_id proporcionado no es válido.");
        }


        $userExists = \App\Models\User::where('id', $userId)->exists();

        if (!$userExists) {
            throw new Exception("El user_id proporcionado no existe en la base de datos.");
        }


        if (Voucher::where('invoice_id', $this->data['voucher']['invoice_id'])->exists()) {
            return;
        }

        $voucher = Voucher::create([

            'user_id' => $this->userId,
            'invoice_id' => $this->data['voucher']['invoice_id'],
            'issue_date' => $this->data['voucher']['issue_date'],
            'issue_time' => $this->data['voucher']['issue_time'],
            'currency' => $this->data['voucher']['currency'],
            'issuer_name' => $this->data['voucher']['issuer_name'],
             'issuer_document_type' => $this->data['voucher']['issuer_document_type'] ?? null,
            'issuer_document_number' => $this->data['voucher']['issuer_document_number'],
            'receiver_name' => $this->data['voucher']['receiver_name'],
            'receiver_document_number' => $this->data['voucher']['receiver_document_number'],
            'total_amount' => $this->data['voucher']['total_amount'] ?? null,
            'payable_amount' => $this->data['voucher']['payable_amount'],

            'xml_content' => $this->xmlContent,
        ]);


        if (!empty($this->data['allowance_charges'])) {
            foreach ($this->data['allowance_charges'] as $charge) {
                $voucher->allowanceCharges()->create([
                    'charge_indicator' => $charge['charge_indicator'],
                    'reason_code' => $charge['reason_code'],
                    'amount' => $charge['amount'],
                    'base_amount' => $charge['base_amount'],
                ]);
            }
        }


        if (!empty($this->data['tax_totals'])) {
            foreach ($this->data['tax_totals'] as $tax) {
                $voucher->taxTotals()->create([
                    'tax_amount' => $tax['tax_amount'],
                    'taxable_amount' => $tax['taxable_amount'],
                    'tax_name' => $tax['tax_name'],
                    'tax_code' => $tax['tax_code'],
                ]);
            }
        }

        foreach ($this->data['lines'] as $line) {

            VoucherLine::create([

                'voucher_id' => $voucher->id,
                'line_id' => $line['line_id'],
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'line_extension_amount' => $line['line_extension_amount'],
                'tax_amount' => $line['tax_amount'],
                'item_id' => $line['item_id'],
            ]);
        }
    }
}
