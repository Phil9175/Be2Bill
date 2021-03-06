<?php

namespace Payum\Be2Bill\Action\HostedFields;

use Payum\Be2Bill\Api;
use Payum\Be2Bill\Request\Api\ExecutePayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Be2Bill\Request\Api\ObtainCartToken;
use Payum\Core\Bridge\Spl\ArrayObject;

class ObtainCartTokenAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param mixed $request
     * @throws RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var ObtainCartToken $request  */
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['HFTOKEN']) {
            throw new \LogicException('The token has already been set.');
        }

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);

        if ($getHttpRequest->method === 'POST' && isset($getHttpRequest->request['hfToken'])) {
            $model['HFTOKEN'] = $getHttpRequest->request['hfToken'];
            $model['CARDFULLNAME'] = $getHttpRequest->request['cardfullname'];
            $model['SELECTEDBRAND'] = $getHttpRequest->request['brand'];

            $keys = [
                'LANGUAGE', 'CLIENTJAVAENABLED', 'CLIENTSCREENCOLORDEPTH',
                'CLIENTSCREENWIDTH', 'CLIENTSCREENHEIGHT', 'TIMEZONE'
            ];

            foreach ($keys as $key) {
                if ($getHttpRequest->request[$key]) {
                    $model[$key] = $getHttpRequest->request[$key];
                }
            }

            /** @var Api $api */
            $api = $this->api;

            if ($api->getIsForce3dSecure()) {
                $model['3DSECUREDISPLAYMODE'] = 'main';
                $model['3DSECURE'] = true;
            }

            $executePayment = new ExecutePayment(
                $request->getToken(),
                $getHttpRequest->request['cardType'],
                $getHttpRequest->request['execCode']
            );
            $executePayment->setModel($model);
            $this->gateway->execute($executePayment);
        }
    }

    /**
     * @param mixed $request
     * @return boolean
     */
    public function supports($request)
    {
        return
            $request instanceof ObtainCartToken &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
