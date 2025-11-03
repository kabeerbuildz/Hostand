<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'property_id',
        'unit_id',
        'invoice_month',
        'end_date',
        'status',
        'notes',
        'parent_id',
    ];

    public static $status = [
        'open' => 'Open',
        'paid' => 'Paid',
        'partial_paid' => 'Partial Paid',
    ];

    public function properties()
    {
        return $this->hasOne('App\Models\Property', 'id', 'property_id');
    }

    public function units()
    {
        return $this->hasOne(PropertyUnit::class, 'id', 'unit_id');
    }

    public function tenants()
    {
        return Tenant::where('unit',$this->unit_id)->first();
    }

    public function types()
    {
        return $this->hasMany('App\Models\InvoiceItem', 'invoice_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\InvoicePayment', 'invoice_id', 'id');
    }

    public function getInvoiceSubTotalAmount()
    {
        $invoiceSubTotal = 0;
        foreach ($this->types as $type) {
            $invoiceSubTotal += $type->amount;
        }
        return $invoiceSubTotal;
    }

    public function getInvoiceDueAmount()
    {
        $invoiceDue = 0;
        foreach ($this->payments as $payment) {
            $invoiceDue += $payment->amount;
        }
        return $this->getInvoiceSubTotalAmount() - $invoiceDue;
    }

    public static function statusChange($invoice_id, $status)
    {
        $invoice = Invoice::find($invoice_id);
        $invoice->status = $status;
        $invoice->save();
        return $invoice;
    }

    public static function addPayment($data)
    {
        $payment = new InvoicePayment();
        $payment->invoice_id = $data['invoice_id'];
        $payment->transaction_id =$data['transaction_id'];
        $payment->payment_type = $data['payment_type'];
        $payment->amount = $data['amount'];
        $payment->payment_date = date('Y-m-d');
        $payment->receipt = !empty($data['receipt']) ? $data['receipt'] : '';
        $payment->notes = $data['notes'];;
        $payment->parent_id = parentId();
        $payment->save();
        $invoice = Invoice::find($data['invoice_id']);
        if ($invoice->getInvoiceDueAmount() <= 0) {
            $status = 'paid';
        } else {
            $status = 'partial_paid';
        }
        Invoice::statusChange($invoice->id, $status);
    }
}
