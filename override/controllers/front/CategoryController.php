<?php

class CategoryController extends CategoryControllerCore
{
protected function getTemplateVarCategory()
    {
        $category = $this->objectPresenter->present($this->category);
        $category['image'] = $this->getImage(
            $this->category,
            $this->category->id_image
        );

        if( ImageManager::isRealImage(_PS_CAT_IMG_DIR_.$this->category->id.'-0_arpa.jpg')){
            $category['image']['arpa_image_url'] = _PS_IMG_.'c/'.$this->category->id.'-0_arpa.jpg';
        }

        return $category;
    }

}
?>