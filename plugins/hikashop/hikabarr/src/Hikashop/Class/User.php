<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Class;

use hikashopUserClass;
use Joomla\CMS\Factory;
use Systrio\Plugins\Hikabarr\Hikashop\Models\UserModel;

class User
{
    private UserModel $user;

    public function __construct(UserModel $user)
    {
        $this->user = $user;
    }

    public function get(): mixed
    {
        if (is_null($this->user->id))
        {
            if (!empty($this->getByEmail()))
            {
                return $this->getByEmail();
            }
        }

        if (is_null($this->user->email) || is_null($this->user->user_email))
        {
            if (!empty($this->getByUsername()))
            {
                return $this->getByUsername();
            }
        }

        if (is_null($this->user->username))
        {
            if (!empty($this->getByEmail()))
            {
                return $this->getByEmail();
            }
        }
        
        return $this->getById();
    }

    public function getById(): mixed
    {
        $hikashopuser = new hikashopUserClass;
        return $hikashopuser->get($this->user->id);
    }

    public function getByUsername(): mixed
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db 
            ->getQuery(true)
            ->select('hika_u.user_id, hika_u.user_cms_id, hika_u.user_email, u.name, u.username')
            ->from('#__hikashop_user AS hika_u')
            ->join('LEFT', '#__users AS u ON u.id = hika_u.user_cms_id')
            ->where($db->qn('u.username') . ' = ' . $db->q($this->user->username));

        if (!empty($db->setQuery($query)->loadObject()))
        {
            return $db->setQuery($query)->loadObject();
        }

        return false;
    }

    public function getByEmail(): mixed
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db 
            ->getQuery(true)
            ->select('hika_u.user_id, hika_u.user_cms_id, hika_u.user_email, u.name, u.username')
            ->from('#__hikashop_user AS hika_u')
            ->join('LEFT', '#__users AS u ON u.id = hika_u.user_cms_id')
            ->where(
                $db->qn('u.email') . ' = ' . $db->q($this->user->email) 
                . ' || ' 
                . $db->qn('hika_u.user_email') . ' = ' . $db->q($this->user->user_email) 
            );

        return $db->setQuery($query)->loadObject();
    }

}