<?php

namespace Payum\Be2Bill\Action\HostedFields;

use Payum\Be2Bill\Api;
use Payum\Be2Bill\Request\RenderObtainCardToken;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpResponse;

class RenderTemplateAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    /**
     * @var string
     */
    private $template;

    /**
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = $template;
        $this->apiClass = Api::class;
    }

    /**
     * @param mixed $request
     * @throws RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var RenderObtainCardToken $request  */
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['HFTOKEN']) {
            throw new \LogicException('The token has already been set.');
        }

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);

        /** @var Api $api */
        $api = $this->api;
        $token = $request->getToken();
        $this->gateway->execute($renderTemplate = new RenderTemplate($this->template, [
            'credentials' => $api->getObtainJsTokenCredentials(),
            'actionUrl' => $token ? $token->getTargetUrl() : null,
            'hostedFieldsJsLibUrl' => $api->getHostedFieldsJsLibUrl(),
            'brandDetectorJsLibUrl' => $api->getBrandDetectorJsLibUrl(),
            'token' => $token,
            'amount' => $model['AMOUNT'] / 100,
        ]));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * @param mixed $request
     * @return boolean
     */
    public function supports($request)
    {
        return
            $request instanceof RenderObtainCardToken &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
