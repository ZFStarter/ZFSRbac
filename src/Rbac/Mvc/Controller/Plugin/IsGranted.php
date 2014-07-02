<?php

namespace ZFS\Rbac\Mvc\Controller\Plugin;

use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Router\Exception\RuntimeException;

class IsGranted extends AbstractPlugin
{
    /**
     * @param array|string $permissions
     * @param bool         $recollect
     *
     * @return bool
     * @throws \Zend\Mvc\Router\Exception\RuntimeException
     */
    public function __invoke($permissions, $recollect = false)
    {
        $controller = $this->getController();
        if (!$controller instanceof AbstractController) {
            throw new RuntimeException('Unknown controller class');
        }

        return $controller->getServiceLocator()->get('ZFS\Rbac\Service')->isGranted($permissions, $recollect);
    }
}
