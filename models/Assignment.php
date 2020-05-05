<?php

namespace mdm\admin\models;

use mdm\admin\components\Configs;
use mdm\admin\components\Helper;
use Yii;

/**
 * Description of Assignment
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.5
 */
class Assignment extends \mdm\admin\BaseObject
{
    /**
     * @var integer User id
     */
    public $id;
    /**
     * @var \yii\web\IdentityInterface User
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function __construct($id, $user = null, $config = [])
    {
        $this->id = $id;
        $this->user = $user;
        parent::__construct($config);
    }

    /**
     * Grands a roles from a user.
     * @param array $items
     * @return integer number of successful grand
     */
    public function assign($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        foreach ($items as $name) {
            try {
                $item = $manager->getRole($name);
                $item = $item ?: $manager->getPermission($name);
                $manager->assign($item, $this->id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        return $success;
    }

    /**
     * Revokes a roles from a user.
     * @param array $items
     * @return integer number of successful revoke
     */
    public function revoke($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        foreach ($items as $name) {
            try {
                $item = $manager->getRole($name);
                $item = $item ?: $manager->getPermission($name);
                $manager->revoke($item, $this->id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        return $success;
    }

    /**
     * Get all available and assigned roles/permission
     * @return array
     */
    public function getItems()
    {
        $manager = Configs::authManager();
        $available = [];
        foreach ($manager->getRoles() as $item) {
            $avaliable[$item->name] = [
                'type' => 'role',
                'desc' => $item->description,
            ];
        }

        foreach ($manager->getPermissions() as $item) {
            if ($item->name[0] != '/') {
                $avaliable[$item->name] = [
                    'type' => 'permission',
                    'desc' => $item->description,
                ];
            }
        }

        $assigned = [];
        foreach ($manager->getAssignments($this->id) as $item) {
            $assigned[$item->roleName] = $available[$item->roleName];
            unset($available[$item->roleName]);
        }

        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($this->user) {
            return $this->user->$name;
        }
    }
}
