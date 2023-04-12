<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Class;

use hikashopFileClass;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File as FilesystemFile;
use Systrio\Plugins\Hikabarr\Hikashop\Models\FileModel;

require_once JPATH_ROOT . '/administrator/components/com_hikashop/classes/file.php';

class File
{
    private FileModel $file;
    private string $path_upload = JPATH_SITE . '/images/com_hikashop/upload';
    private string $path_upload_subdirectory = '/dolibarr/';
    
    public function __construct(FileModel $file)
    {
        $this->file = $file;
    }

    public function save(): mixed
    {
        if (!empty($this->get()))
        {
            $this->file->file_id = $this->get()->file_id;
        }

        $hikashopFile = new hikashopFileClass;
        return $hikashopFile->save($this->file);
    }

    public function upload($file): string
    {
        $file_path = $this->path_upload . $this->path_upload_subdirectory . $file->filename;
        file_put_contents($file_path, base64_decode($file->content));

        return $this->path_upload_subdirectory . $file->filename;
    }

    public function delete(): bool|null
    {
        $hikashopFile = new hikashopFileClass;
        return $hikashopFile->delete($this->get()->file_id);
    }

    public function get(): mixed
    {
        if (is_null($this->file->file_id))
        {
            return $this->getByName();
        }
        
        return $this->getById();
    }

    public function getById(): mixed
    {
        $hikashopFile = new hikashopFileClass;
        return $hikashopFile->get($this->file->file_id);
    }

    public function getByName(): mixed
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db
            ->getQuery(true)
            ->select('file_id')
            ->from('#__hikashop_file')
            ->where($db->qn('file_name') . ' = ' . $db->q($this->file->file_name));

        if (!empty($db->setQuery($query)->loadObject()->file_id))
        {
            $this->file->file_id = $db->setQuery($query)->loadObject()->file_id;
            return $this->getById();
        }

        return false;
    }

}