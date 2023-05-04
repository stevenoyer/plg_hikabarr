<?php 

namespace Systrio\Plugins\Hikabarr\Hikashop\Class;

use hikashopCategoryClass;
use Joomla\CMS\Factory;
use Systrio\Plugins\Hikabarr\Hikashop\Models\CategoryModel;

require_once JPATH_ROOT . '/administrator/components/com_hikashop/classes/category.php';

class Category
{
    private CategoryModel $category;
    
    public function __construct(CategoryModel $category)
    {
        $this->category = $category;
    }

    public function save(): mixed
    {
        if (!empty($this->get()))
        {
            $this->category->category_id = $this->get()->category_id;
        }

        $hikashopCategory = new hikashopCategoryClass;
        return $hikashopCategory->save($this->category);
    }

    public function delete(): bool|null
    {
        $hikashopCategory = new hikashopCategoryClass;
        return $hikashopCategory->delete($this->get()->category_id);
    }

    public function get(): mixed
    {
        if (is_null($this->category->category_id))
        {
            return $this->getByName();
        }
        
        return $this->getById();
    }

    public function getById(): mixed
    {
        $hikashopCategory = new hikashopCategoryClass;
        return $hikashopCategory->get($this->category->category_id);
    }

    public function getByName(): mixed
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db
            ->getQuery(true)
            ->select('category_id')
            ->from('#__hikashop_category')
            ->where($db->qn('category_name') . ' = ' . $db->q($this->category->category_name));

        if (!empty($db->setQuery($query)->loadObject()->category_id))
        {
            $this->category->category_id = $db->setQuery($query)->loadObject()->category_id;
            return $this->getById();
        }

        return false;
    }

}