<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Models;

class FileModel
{
    
    public int|null $file_id = null;
    public string $file_name;
    public string $file_path;
    public string $file_type;
    public int $file_ref_id; // id of product
    public string $fullname_dolibarr; // url of image product in dolibarr

    public function __construct()
    {
        $this->file_type = 'product';
    }

}