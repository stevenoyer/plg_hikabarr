<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Models;

class UserModel
{
    
    public int|null $id = null;
    public null|string $name = null;
    public null|string $username = null;
    public null|string $email = null;
    public null|string $user_email = null;

    public function __construct()
    {
        if (is_null($this->email))
        {
            $this->email = $this->user_email;
        }

        if (is_null($this->user_email))
        {
            $this->user_email = $this->email;
        }
    }

}