<?php

namespace ZFS\Rbac\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class IsGranted
 * @package ZFS\Rbac\View\Helper
 * @method PhpRenderer getView()
 */
class IsGranted extends AbstractHelper
{
    /**
     * @param string|array $permissions
     * @param bool         $recollect
     *
     * @return mixed
     */
    public function __invoke($permissions, $recollect = false)
    {
        if ($this->getView() instanceof PhpRenderer) {
            return $this
                ->getView()
                ->getHelperPluginManager()
                ->getServiceLocator()
                ->get('ZFS\Rbac\Service')
                ->isGranted($permissions, $recollect);
        } else {
            return false;
        }
    }
}
