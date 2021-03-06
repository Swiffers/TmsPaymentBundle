<?php

namespace Tms\Bundle\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tms\Bundle\PaymentBundle\Model\Payment;

/**
 * Payment controller.
 *
 * @Route("/payment")
 */
class PaymentController extends Controller
{
    /**
     * Auto response
     *
     * @Route("/{backend_alias}/autoresponse", name="tms_payment_order_autoresponse")
     * @Method({"GET", "POST"})
     */
    public function autoResponseAction(Request $request, $backend_alias)
    {
        $this->container->get('tms_payment.logger')->info(
            sprintf('[%s] Payment auto response', $backend_alias),
            array(
                'order_id'  => $request->get('order_id'),
                'callbacks' => $request->get('callbacks'),
                'post_data' => $request->request->all(),
            )
        );

        $paymentBackend = $this->container->get('tms_payment.backend_registry')
            ->getBackend($backend_alias)
        ;

        $orderId = $request->get('order_id');
        if (null === $orderId) {
            throw new HttpException(400, 'order_id parameter is missing');
        }

        $callbacks = $request->get('callbacks');
        if (null === $callbacks) {
            throw new HttpException(400, 'Callbacks parameter is missing');
        }
        $callbacks = is_array($callbacks) ? $callbacks : json_decode(base64_decode($callbacks), true);

        $order = $this
            ->container
            ->get('tms_rest_client.hypermedia.crawler')
            ->go('order')
            ->findOne('/orders', $orderId, array(), true)
            ->getData()
        ;

        if (empty($order['payment'])) {
            throw new \LogicException('The payment must exist in the order');
        }

        $payment = new Payment($order['payment']);
        $response = new Response();

        try {
            if ($paymentBackend->doPayment($request, $payment)) {
                foreach ($callbacks as $callback => $parameters) {
                    if (null === $parameters || '' == $parameters) {
                        $parameters = array();
                    }

                    $this
                        ->container
                        ->get('tms_payment.callback_registry')
                        ->getCallback($callback)
                        ->execute($order, $payment, $parameters)
                    ;
                }
            }
        } catch (\Exception $e) {
            $this->container->get('logger')->error(
                $e->getMessage(),
                array(
                    'backend' => $backend_alias,
                    'request' => $request,
                )
            );
            $response
                ->setStatusCode(500, $e->getMessage())
                ->setContent($e->getMessage())
            ;

            return $response;
        }

        return $response;
    }
}
