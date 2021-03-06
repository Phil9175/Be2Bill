<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\SDD\ConvertPaymentAction;
use Payum\Be2Bill\Action\SDD\CaptureAction;
use Payum\Be2Bill\Action\SDD\ExecutePaymentAction;
use Payum\Be2Bill\Action\SDD\ObtainSDDAction;
use Payum\Be2Bill\Action\SDD\RecurringPaymentAction;
use Payum\Be2Bill\Action\SDD\RenderTemplateAction;
use Payum\Be2Bill\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class Be2BillSDDGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'dalenys_sdd',
            'payum.factory_title' => 'Dalenys SDD',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.execute_payment' => new ExecutePaymentAction(),
            'payum.action.recurring_payment' => new RecurringPaymentAction(),
            'payum.action.obtain_sdd_data' => new ObtainSDDAction(),
            'payum.action.render_obtain_sdd_data' => function (ArrayObject $config) {
                return new RenderTemplateAction($config['payum.template.obtain_sdd_data']);
            },
            'payum.template.obtain_sdd_data' => '@PayumBe2Bill/Action/SDD/obtain_sdd_data.html.twig',
        ]);

        $paths = $config['payum.paths'];
        $paths['PayumBe2Bill'] = realpath(__DIR__ . '/Resources/views');
        $config->offsetSet('payum.paths', $paths);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                // Merchant name
                'sdd_identifier' => '',
                //Public APIKEYID
                'apikeyid' => '',
                // APIKEY
                'password' => '',
                'sdd_secret' => '',
                'sandbox' => true,
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['identifier', 'password'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api(
                    [
                        'sdd_identifier' => $config['sdd_identifier'],
                        'apikeyid' => $config['apikeyid'],
                        'password' => $config['password'],
                        'sdd_secret' => $config['sdd_secret'],
                        'sandbox' => $config['sandbox'],
                    ],
                    $config['payum.http_client'],
                    $config['httplug.message_factory']
                );
            };
        }
    }
}
