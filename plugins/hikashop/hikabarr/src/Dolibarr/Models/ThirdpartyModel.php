<?php 

namespace Systrio\Plugins\Hikabarr\Dolibarr\Models;

class ThirdpartyModel
{

    public ?int $id;
    public ?string $name;
    public ?string $nameAlias;
    public bool|null $entity;
    public bool|int $activity; // Is in activity or not. (Open / Close in Dolibarr).
    public ?int $typent_id;
    public ?string $address;
    public ?string $zip;
    public ?string $town;
    public ?string $email;
    public ?string $phone;
    public ?string $ref_ext;
    public ?string $code_client;
    public ?int $client;

    public function __construct()
    {
        $this->code_client = -1; //automatically assigned by Dolibarr
        $this->client = 1;
        $this->activity = true; //in activity
        $this->typent_id = 1;
    }

}