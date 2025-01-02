<?php

namespace App\Services;

use SimpleXMLElement;

class VoucherService
{
    public function extractDataFromXml(string $xmlContent): array
    {
        $xml = new SimpleXMLElement($xmlContent);
        $namespaces = $xml->getNamespaces(true);

        // Registrar namespaces
        $xml->registerXPathNamespace('cbc', $namespaces['cbc']);
        $xml->registerXPathNamespace('cac', $namespaces['cac']);

        // Datos de la cabecera
        $voucher = [
            'invoice_id' => $this->getValue($xml, '//cbc:ID'),
            'issue_date' => $this->getValue($xml, '//cbc:IssueDate'),
            'issue_time' => $this->getValue($xml, '//cbc:IssueTime'),
            'currency' => $this->getValue($xml, '//cbc:DocumentCurrencyCode'),
            'issuer_document_type' => $this->getIssuerDocumentType($xml),
            'issuer_name' => $this->getValue($xml, '//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName'),
            'issuer_document_number' => $this->getValue($xml, '//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID'),
            'receiver_name' => $this->getValue($xml, '//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName'),
            'receiver_document_number' => $this->getValue($xml, '//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID'),
            'receiver_document_type' => $this->getReceiverDocumentType($xml),
            'total_amount' => (float)$this->getValue($xml, '//cac:LegalMonetaryTotal/cbc:LineExtensionAmount'),
            'payable_amount' => (float)$this->getValue($xml, '//cac:LegalMonetaryTotal/cbc:PayableAmount'),
        ];

                // Extraer los cargos y descuentos (Allowance Charges)
                $allowanceCharges = [];
                foreach ($xml->xpath('//cac:AllowanceCharge') as $charge) {
                    // Verifica si se está extrayendo correctamente cada cargo
                    $chargeIndicator = $this->getValue($charge, 'cbc:ChargeIndicator');
                    $reasonCode = $this->getValue($charge, 'cbc:ReasonCode');
                    $amount = (float)$this->getValue($charge, 'cbc:Amount');
                    $baseAmount = (float)$this->getValue($charge, 'cbc:BaseAmount');

                    // Si no se encuentra ninguno de los valores, podemos agregar un log o alguna forma de depuración.
                    if ($chargeIndicator && $amount) {
                        // Asegurarse de que charge_indicator sea 1 o 0
                        if ($chargeIndicator === 'true' || $chargeIndicator === '1') {
                            $chargeIndicator = 1; // Establecer como verdadero
                        } else {
                            $chargeIndicator = 0; // Establecer como falso
                        }

                        // Agregar el cargo al array
                        $allowanceCharges[] = [
                            'charge_indicator' => $chargeIndicator,
                            'reason_code' => $reasonCode,
                            'amount' => $amount,
                            'base_amount' => $baseAmount,
                        ];
                    }
                }




        // Extraer los totales de impuestos (Tax Totals)
        $taxTotals = [];
        foreach ($xml->xpath('//cac:TaxTotal') as $tax) {
            $taxTotals[] = [
                'tax_amount' => (float)$this->getValue($tax, 'cbc:TaxAmount'),
                'taxable_amount' => (float)$this->getValue($tax, 'cbc:TaxableAmount'),
                'tax_name' => $this->getValue($tax, 'cbc:TaxName'),
                'tax_code' => $this->getValue($tax, 'cbc:TaxCode'),
            ];
        }

        // Datos de las líneas
        $lines = [];
        foreach ($xml->xpath('//cac:InvoiceLine') as $line) {
            $lines[] = [
                'line_id' => $this->getValue($line, 'cbc:ID'),
                'description' => $this->getValue($line, 'cac:Item/cbc:Description'),
                'quantity' => (float)$this->getValue($line, 'cbc:InvoicedQuantity'),
                'unit_price' => (float)$this->getValue($line, 'cac:Price/cbc:PriceAmount'),
                'line_extension_amount' => (float)$this->getValue($line, 'cbc:LineExtensionAmount'),
                'tax_amount' => (float)$this->getValue($line, 'cac:TaxTotal/cbc:TaxAmount'),
                'item_id' => $this->getValue($line, 'cac:Item/cac:SellersItemIdentification/cbc:ID'),
            ];
        }

        // Retornar los datos completos
        return [
            'voucher' => $voucher,
            'lines' => $lines,
            'allowance_charges' => $allowanceCharges,  // Aquí se incluyen los cargos y descuentos
            'tax_totals' => $taxTotals,  // Aquí se incluyen los totales de impuestos
        ];
    }

    private function getIssuerDocumentType(SimpleXMLElement $xml)
    {
        // Utilizamos XPath para acceder al atributo 'schemeID' de la etiqueta 'cbc:ID'
        $documentType = $this->getValue($xml, '//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID');

        // Si el atributo no está presente, se devuelve un valor por defecto (o 'Unknown' como fallback)
        return $documentType ? (string)$documentType : 'Unknown';
    }

    private function getReceiverDocumentType(SimpleXMLElement $xml): ?string
    {
        // Supongamos que el tipo de documento del receptor está en el mismo lugar que el emisor,
        // pero adaptado al receptor.
        return $this->getValue($xml, '//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:SchemeID');
    }

    private function getValue($xml, $xpath)
    {
        $result = $xml->xpath($xpath);
        return $result ? (string)$result[0] : null;
    }
}
