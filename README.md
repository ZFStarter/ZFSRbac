zfs-rbac
================

Обертка на Zf2\Rbac для удобного использования в ZF2/ZFStarter проектах

Подключение
---
Сервис оформлен в модуль, и потому вам остается лишь добавить его имя в список модулей на подключение в ```application.config.php```:
```php
'modules' => array(
        'ZFS\Rbac', // <--
        'Application'
    ),
```

Настройка через события
---

Модуль содержит сервис ```ZFS\Rbac\Rbac```, помощник представления ```isGranted``` и плагин контроллера ```isGranted```.

В процессе работы сервиса, он выбрасывает 2 события: 
- EVENT_GET_CONFIG (ZFS\Rbac\Service\Event\GetConfig)
- EVENT_GET_USER_ROLES (ZFS\Rbac\Service\Event\GetUserRoles)

EVENT_GET_CONFIG ожидает от программной среды конфигурацию ролей и их разрешений. Предоставить ее можно подписавшись на событие:
```php
$this->getEventManager()->getSharedManager()->attach(
    ZFS\Rbac\Rbac::EVENT_MANAGER_IDENTIFIER,
    ZFS\Rbac\Rbac::EVENT_GET_CONFIG,
    function () {
        return array(
            'user' => array(
                'permissions' => array(
                    'login'
                )
            ),
            'users_manager' => array(
                'permissions' => array(
                    'modify_users'
                )
            ),
            'admin' => array(
                'children' => array(
                    'users_manager'
                )
            )
        );
    }
);
```
Обработчик должен вернуть массив из ролей (ключ) и его настройкой (значение). Среди настроек могут быть массив из самих разрешений (ключ permissions) и массив из дочерних ролей (ключ children).


EVENT_GET_USER_ROLES ожидает список ролей текущего пользователя. Предоставить его можно подписавшись на событие:
```php
$this->getEventManager()->getSharedManager()->attach(
    ZFS\Rbac\Rbac::EVENT_MANAGER_IDENTIFIER,
    ZFS\Rbac\Rbac::EVENT_GET_USER_ROLES,
    function () {
        return array('admin');
    }
);
```

Оба события могут обрабатывать несколько обработчиков дополняя массивы предыдущих. Таким образом, каждый модуль может модифицировать конфигурацию удобным для него способом: выбирая данные из БД, из конфигурации проекта или отдельно лежащего файла с настройками.

Примеры использования:
---
- в контроллере:
```php
class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        if (!$this->isGranted('index_action')) {
            return $this->notFoundAction();
        }
        /* ... */
    }
}
```

- в шаблоне представления:
```php
<?php if ($this->isGranted('buy')): ?>
    <a href="/buy">Buy</a>
<?php else: ?>
    <a href="/login">Login to buy</a>
<?php endif; ?>
```

- где угодно, где есть доступ к ```ServiceLocator```:
```php
$this->getServiceLocator()->get('ZFS\Rbac\Rbac')->isGranted('some_permission');
```

Во всех трех примерах метод isGranted принимает первым аргументом строку с именем разрешения или массив имён разрешения, а вторым аргументом булевый флаг, указывающий на надобность повторного вызова события EVENT_GET_USER_ROLES. По умолчанию, стоит в false.


Лицензия
----

MIT
