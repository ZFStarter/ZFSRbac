<?php
/**
 * Created by PhpStorm.
 * User: Qoma
 * Date: 12/06/14
 * Time: 13:34
 */

namespace ZFS\Rbac;

use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

class Module implements ServiceProviderInterface, ControllerPluginProviderInterface, ViewHelperProviderInterface
{
    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'ZFS\Rbac\Service' => 'ZFS\Rbac\Service'
            ),
        );
    }

    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getControllerPluginConfig()
    {
        return array(
            'invokables' => array(
                'isGranted' => 'ZFS\Rbac\Mvc\Controller\Plugin\IsGranted'
            )
        );
    }

    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'isGranted' => 'ZFS\Rbac\View\Helper\IsGranted'
            )
        );
    }
}
