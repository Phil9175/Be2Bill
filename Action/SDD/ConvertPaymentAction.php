<?php

namespace Payum\Be2Bill\Action\SDD;

use Payum\Be2Bill\Action\ConvertPaymentAction as CommonConvertPaymentAction;
use Payum\Be2Bill\Model\GenderAwarePaymentInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction extends CommonConvertPaymentAction
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        parent::execute($request);
        /** @var PayumPaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($request->getResult());

        if ($payment instanceof GenderAwarePaymentInterface) {
            $details['CLIENTGENDER'] = $payment->getClientGender();
        }

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PayumPaymentInterface &&
            $request->getTo() === 'array'
        ;
    }
}
