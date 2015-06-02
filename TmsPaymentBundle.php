<?php

/**
 * @author:  TESSI Marketing <contact@tessi.fr>
 */

namespace Tms\Bundle\PaymentBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tms\Bundle\PaymentBundle\DependencyInjection\Compiler\BackendCompilerPath;

class TmsPaymentBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BackendCompilerPath());
    }
}
