<?php

namespace Payum\Be2Bill\Action\SDD;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Be2Bill\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['DESCRIPTION'] = $payment->getDescription();
        $details['AMOUNT'] = $payment->getTotalAmount();
        $details['CLIENTIDENT'] = $payment->getClientId();
        $details['CLIENTEMAIL'] = $payment->getClientEmail();
        $details['CLIENTGENDER'] = $payment->getClientGender();
        $details['ORDERID'] = $payment->getNumber();

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array'
        ;
    }
}