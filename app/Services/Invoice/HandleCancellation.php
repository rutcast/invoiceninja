<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Services\Invoice;

use App\Events\Invoice\InvoiceWasCancelled;
use App\Events\Payment\PaymentWasCreated;
use App\Factory\CreditFactory;
use App\Factory\InvoiceItemFactory;
use App\Factory\PaymentFactory;
use App\Helpers\Invoice\InvoiceSum;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Paymentable;
use App\Services\AbstractService;
use App\Services\Client\ClientService;
use App\Services\Payment\PaymentService;
use App\Utils\Traits\GeneratesCounter;

class HandleCancellation extends AbstractService
{
    use GeneratesCounter;

    private $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function run()
    {
        /* Check again!! */
        if (!$this->invoice->invoiceCancellable($this->invoice)) {
            return $this->invoice;
        }

        $adjustment = $this->invoice->balance*-1;

        $this->backupCancellation($adjustment);

        //set invoice balance to 0
        $this->invoice->ledger()->updateInvoiceBalance($adjustment, "Invoice cancellation");

        $this->invoice->balance = 0;
        $this->invoice = $this->invoice->service()->setStatus(Invoice::STATUS_CANCELLED)->save();

        //adjust client balance
        $this->invoice->client->service()->updateBalance($adjustment)->save();
    
        event(new InvoiceWasCancelled($this->invoice));
        

        return $this->invoice;
    }

    public function reverse()
    {

        $cancellation = $this->invoice->backup->cancellation;

        $adjustment = $cancellation->adjustment*-1;

        $this->invoice->ledger()->updateInvoiceBalance($adjustment, "Invoice cancellation REVERSAL");

        /* Reverse the invoice status and balance */ 
        $this->invoice->balance += $adjustment;
        $this->invoice->status_id = $cancellation->status_id;

        $this->invoice->client->service()->updateBalance($adjustment)->save();

        /* Pop the cancellation out of the backup*/
        $backup = $this->invoice->backup;
        unset($backup->cancellation);
        $this->invoice->backup = $backup;
        $this->invoice->save();

        return $this->invoice;

    }

    /**
     * Backup the cancellation in case we ever need to reverse it.
     * 
     * @param  float $adjustment  The amount the balance has been reduced by to cancel the invoice
     * @return void             
     */
    private function backupCancellation($adjustment)
    {

        if(!is_object($this->invoice->backup)){
            $backup = new \stdClass;
            $this->invoice->backup = $backup;
        }

        $cancellation = new \stdClass;
        $cancellation->adjustment = $adjustment;
        $cancellation->status_id = $this->invoice->status_id;

        $invoice_backup = $this->invoice->backup;
        $invoice_backup->cancellation = $cancellation;

        $this->invoice->backup = $invoice_backup;
        $this->invoice->save();

    }
}