<?php
namespace Payum\Be2Bill\Action;

use Payum\Action\ActionInterface;
use Payum\Request\StatusRequestInterface;
use Payum\Domain\InstructionAggregateInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Be2Bill\PaymentInstruction;
use Payum\Be2Bill\Api;

class StatusAction implements ActionInterface
{
    public function execute($request)
    {
        /** @var $request \Payum\Request\StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        /** @var $instruction PaymentInstruction */
        $instruction = $request->getModel()->getInstruction();
        if (null === $instruction->getExeccode()) {
            $request->markNew();
            
            return;
        }
        if (Api::EXECCODE_SUCCESSFUL === $instruction->getExeccode()) {
            $request->markSuccess();

            return;
        }

        $request->markFailed();
    }

    public function supports($request)
    {
        return
            $request instanceof StatusRequestInterface &&
            $request->getModel() instanceof InstructionAggregateInterface &&
            $request->getModel()->getInstruction() instanceof PaymentInstruction
        ;
    }
}