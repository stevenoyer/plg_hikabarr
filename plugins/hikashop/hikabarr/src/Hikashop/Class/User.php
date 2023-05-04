<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Class;

use hikashopUserClass;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Systrio\Plugins\Hikabarr\Hikashop\Models\UserModel;

require_once JPATH_ROOT . '/administrator/components/com_hikashop/classes/user.php';

class User
{
    private UserModel $user;
    private DatabaseDriver $db;

    public function __construct(UserModel $user)
    {
        $this->user = $user;
        $this->db = Factory::getContainer()->get('DatabaseDriver');
    }

    /**
     * Fonction qui récupère les données utilisateur Hikashop par :
     * L'ID hikashop ou CMS Joomla
     * L'e-mail
     * Le nom d'utilisateur
     */
    public function get(): mixed
    {
        // Si l'id est null, on récupère par l'e-mail
        if (is_null($this->user->id))
        {
            if (!empty($this->getByEmail()))
            {
                return $this->getByEmail();
            }
        }

        // Si l'e-mail est null, on récupère par le nom d'utilisateur
        if (is_null($this->user->email) || is_null($this->user->user_email))
        {
            if (!empty($this->getByUsername()))
            {
                return $this->getByUsername();
            }
        }

        // Si le nom d'utilisateur est null, on récupère par l'e-mail
        if (is_null($this->user->username))
        {
            if (!empty($this->getByEmail()))
            {
                return $this->getByEmail();
            }
        }
        
        // Sinon on retourne par l'ID Hikashop ou Joomla
        return $this->getById();
    }

    /**
     * Fonction qui récèpère l'utilisateur par ID de l'utilisateur Joomla ou celui Hikashop
     */
    public function getById(): mixed
    {
        $hikashopUser = new hikashopUserClass;
        $user = $hikashopUser->get($this->user->id);

        if (empty($user) || is_null($user))
        {
            $user = $hikashopUser->get($this->user->id, 'user_cms_id');
        }

        return $user;
    }

    /**
     * Fonction qui récèpère l'utilisateur par le nom d'utilisateur
     */
    public function getByUsername(): mixed
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db 
            ->getQuery(true)
            ->select('hika_u.user_id, hika_u.user_cms_id, hika_u.user_email, u.name, u.username')
            ->from('#__hikashop_user AS hika_u')
            ->join('LEFT', '#__users AS u ON u.id = hika_u.user_cms_id')
            ->where($this->db->qn('u.username') . ' = ' . $this->db->q($this->user->username));

        if (!empty($this->db->setQuery($query)->loadObject()))
        {
            return $this->db->setQuery($query)->loadObject();
        }

        return false;
    }

    /**
     * Fonction qui récèpère l'utilisateur par l'e-mail
     */
    public function getByEmail(): mixed
    {
        $query = $this->db 
            ->getQuery(true)
            ->select('hika_u.user_id, hika_u.user_cms_id, hika_u.user_email, u.name, u.username')
            ->from('#__hikashop_user AS hika_u')
            ->join('LEFT', '#__users AS u ON u.id = hika_u.user_cms_id')
            ->where(
                $this->db->qn('u.email') . ' = ' .$this->db->q($this->user->email) 
                . ' || ' 
                . $this->db->qn('hika_u.user_email') . ' = ' . $this->db->q($this->user->user_email) 
            );

        return $this->db->setQuery($query)->loadObject();
    }

    /**
     * Fonction qui récupère toutes les commandes d'un utilisateur
     */
    public function getOrders(?string $type = 'all'): array
    {
        $query = $this->db
            ->getQuery(true)
            ->select('*')
            ->from('#__hikashop_order')
            ->where($this->db->qn('order_user_id') . ' = ' . $this->db->q($this->get()->user_id));

        if ($type !== 'all')
        {
            $query->where($this->db->qn('order_status') . ' = ' . $this->db->q($type));
        }

        return $this->db->setQuery($query)->loadObjectList();
    }

}