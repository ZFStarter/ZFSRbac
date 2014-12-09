<?php

namespace ZFS\Rbac;

use Zend\Config\Config;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Permissions\Rbac\Role;
use Zend\Stdlib\Parameters;

/**
 * Class Rbac
 * @package ZFS\Rbac
 */
class Rbac implements EventManagerAwareInterface
{
    const EVENT_MANAGER_IDENTIFIER = 'ZFS\Rbac\Service\EventManager';
    const EVENT_GET_CONFIG         = 'ZFS\Rbac\Service\Event\GetConfig';
    const EVENT_GET_USER_ROLES     = 'ZFS\Rbac\Service\Event\GetUserRoles';

    /** @var  EventManager */
    protected $eventManager;

    /** @var  Rbac */
    protected $rbac;

    /** @var  array|Parameters|Config */
    protected $config;

    /** @var  array */
    protected $roles = null;

    /**
     * @throws \RuntimeException
     */
    protected function initialize()
    {
        $this->rbac = new \Zend\Permissions\Rbac\Rbac();

        $roles = array();
        $this->getEventManager()->trigger(
            self::EVENT_GET_CONFIG,
            $this,
            array(),
            function ($config) use (&$roles) {
                $config = $config instanceof Parameters ? $config->toArray() : is_array($config) ? $config : array();
                $roles = array_merge($roles, $config);
            }
        );

        /** creating roles with permissions */
        foreach ($roles as $roleName => $roleOptions) {
            $role = new Role($roleName);

            if (isset($roleOptions['permissions']) && is_array($roleOptions['permissions'])) {
                foreach ($roleOptions['permissions'] as $permission) {
                    $role->addPermission($permission);
                }
            }

            $this->rbac->addRole($role);

            unset($role);
            unset($roleName);
            unset($roleOptions);
        }

        /** setting parent roles */
        foreach ($roles as $roleName => $roleOptions) {
            $role = $this->rbac->getRole($roleName);

            if (isset($roleOptions['parent'])) {
                if (!$this->rbac->hasRole($roleOptions['parent'])) {
                    throw new \RuntimeException(
                        'Cannot find role "' . $roleOptions['parent'] . '" as parent for "' . $roleName . '"'
                    );
                }

                $parentRole = $this->rbac->getRole($roleOptions['parent']);
                $role->setParent($parentRole);
            }

            unset($role);
            unset($roleName);
            unset($roleOptions);
        }

        /** setting child roles */
        $roles = array_reverse($roles);
        foreach ($roles as $roleName => $roleOptions) {
            $role = $this->rbac->getRole($roleName);

            if (!empty($roleOptions['children']) && is_array($roleOptions['children'])) {
                foreach ($roleOptions['children'] as $childRoleName) {
                    if (!$this->rbac->hasRole($childRoleName)) {
                        throw new \RuntimeException(
                            'Cannot find role "' . $childRoleName . '" as child for "' . $roleName . '"'
                        );
                    }

                    $childRole = $this->rbac->getRole($childRoleName);
                    $role->addChild($childRole);
                }
            }

            unset($role);
            unset($roleName);
            unset($roleOptions);
        }

        unset($roles);
    }

    /**
     * @param array|string $permissions
     * @param bool         $recollect
     *
     * @return bool
     */
    public function isGranted($permissions, $recollect = false)
    {
        if ($permissions == null) {
            return true;
        }

        if (is_string($permissions)) {
            $permissions = array($permissions);
        }

        if ($recollect || !is_array($this->roles)) {
            $roles = array();
            $this->getEventManager()->trigger(
                self::EVENT_GET_USER_ROLES,
                $this,
                array(),
                function ($config) use (&$roles) {
                    $config = is_array($config) ? $config : array();
                    $roles = array_merge($roles, $config);
                }
            );
            $this->roles = $roles;
        }

        if (is_array($this->roles)) {
            foreach ($this->roles as $role) {
                foreach ($permissions as $permission) {
                    if ($this->getRbac()->hasRole($role)) {
                        if ($this->getRbac()->isGranted($role, $permission)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return \Zend\Permissions\Rbac\Rbac
     */
    protected function getRbac()
    {
        if (!$this->rbac) {
            $this->initialize();
        }

        return $this->rbac;
    }

    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     *
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
        $this->eventManager->setIdentifiers(self::EVENT_MANAGER_IDENTIFIER);
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->setEventManager(new EventManager());
        }

        return $this->eventManager;
    }
}
