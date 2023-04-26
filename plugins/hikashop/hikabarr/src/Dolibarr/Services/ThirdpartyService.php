<?php 

namespace Systrio\Plugins\Hikabarr\Dolibarr\Services;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\RequestApi;
use Systrio\Plugins\Hikabarr\Dolibarr\Models\ThirdpartyModel;

class ThirdpartyService
{
    private Dolibarr $client;
    public ThirdpartyModel $user;

    public function __construct(Dolibarr $client, ThirdpartyModel $user)
    {
        $this->client = $client;
        $this->user = $user;
    }

    public function get()
    {
        $user = $this->client->requestApi->get('thirdparties/email/' . $this->user->email);
        $user = json_decode($user);

        if (!empty($user->error) && ($user->error->code == 404 || $user->error->code == 400))
        {
            if (!empty($this->user->ref_ext) && !is_null($this->user->ref_ext))
            {
                $user = $this->client->requestApi->get('thirdparties', ['sqlfilters' => '(t.ref_ext:=:' . $this->user->ref_ext . ')']);
                $user = json_decode($user);
            }
        }

        return $user;
    }

    public function save()
    {
        $user = $this->get();
        
        if (!is_object($user))
        {
            return false;
        }

        if (!empty($user->error) && $user->error->code == 404)
        {
            return $this->client->requestApi->post('thirdparties', json_encode(get_object_vars($this->user)));
        }

        return $this->update($user);
    }

    public function update(object $user)
    {
        if ($user->email != $this->user->email) return false;
        $this->user->id = $user->id;
        $this->user->entity = $user->entity;

        return $this->client->requestApi->put('thirdparties/' . $user->id, json_encode(get_object_vars($this->user)));
    }

}