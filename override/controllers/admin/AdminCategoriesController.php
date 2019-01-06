<?php
/**
 * @property Category $object
 */
class AdminCategoriesController extends AdminCategoriesControllerCore{



 

    public function renderForm()
    {
 
        $obj = $this->loadObject(true);

        $id_category = Tools::getValue('id_category');

        $arpa_image = ImageManager::thumbnail(_PS_CAT_IMG_DIR_.'/'.$obj->id.'_arpa.jpg', $this->table.'_'.(int)$obj->id.'_arpa.'.$this->imageType, 350, $this->imageType, true);

        $arpa_images = [];
        for ($i = 0; $i < 3; $i++) {
            if (file_exists(_PS_CAT_IMG_DIR_.(int)$obj->id.'-'.$i.'_arpa.jpg')) {
                $arpa_images[$i]['type'] = HelperImageUploader::TYPE_IMAGE;
                $arpa_images[$i]['image'] = ImageManager::thumbnail(_PS_CAT_IMG_DIR_.(int)$obj->id.'-'.$i.'_arpa.jpg', $this->context->controller->table.'_'.(int)$obj->id.'-'.$i.'_arpa.jpg', 100, 'jpg', true, true);
                $arpa_images[$i]['delete_url'] = Context::getContext()->link->getAdminLink('AdminCategories').'&ajax=1&action=deleteArpaImage&deleteArpaImage='.$i.'&id_category='.(int)$obj->id;
            }
        }

        $this->fields_form_override = array(
            array(
                'type' => 'textarea',
                'label' => $this->l('Description arpa'),
                'name' => 'description_arpa',
                'lang' => true,
                'autoload_rte' => true,
                'hint' => $this->l('Invalid characters:').' <>;=#{}',
            ),
            array(
                'type' => 'file',
                'label' => $this->l('Image arpa'),
                'name' => 'arpa_image',
                'ajax' => true,
                'multiple' => false,
                'max_files' => 1,
                'display_image' => true,
                'files' => $arpa_images,
                'url' => Context::getContext()->link->getAdminLink('AdminCategories').'&ajax=1&id_category='.(int)$obj->id.'&action=uploadArpaImages',
            ),
        );
            
        return parent::renderForm();
    }


    public function ajaxProcessdeleteArpaImage(){

        if (($id_arpa_image = Tools::getValue('deleteArpaImage', false)) !== false) {
      
            if (file_exists(_PS_CAT_IMG_DIR_.(int)Tools::getValue('id_category').'-'.(int)$id_arpa_image.'_arpa.jpg')
                && !unlink(_PS_CAT_IMG_DIR_.(int)Tools::getValue('id_category').'-'.(int)$id_arpa_image.'_arpa.jpg')) {
                $this->context->controller->errors[] = $this->trans('Error while delete', array(), 'Admin.Notifications.Error');
            }

            if (empty($this->context->controller->errors)) {
                Tools::clearSmartyCache();
            }

              header('Content-Type: application/json');
              die(Tools::jsonEncode(array('arpa_image' => 'null')));

        }


    }

    public function ajaxProcessuploadArpaImages()
    {
        $category = new Category((int)Tools::getValue('id_category'));

        if (isset($_FILES['arpa_image'])) {
            $files = scandir(_PS_CAT_IMG_DIR_);
            $assigned_keys = array();
            $allowed_keys  = array(0, 1, 2);

            foreach ($files as $file) {
                $matches = array();

                if (preg_match('/^'.$category->id.'-([0-9])?_arpa.jpg/i', $file, $matches) === 1) {
                    $assigned_keys[] = (int)$matches[1];
                }
            }

            $available_keys = array_diff($allowed_keys, $assigned_keys);
            $helper = new HelperImageUploader('arpa_image');
            $files  = $helper->process();
            $total_errors = array();
 
            if (count($available_keys) < count($files)) {
                $total_errors['name'] = $this->trans('An error occurred while uploading the image:', array(), 'Admin.Catalog.Notification');
                $total_errors['error'] = $this->trans('You cannot upload more files', array(), 'Admin.Notifications.Error');
                die(Tools::jsonEncode(array('arpa_image' => array($total_errors))));
            }

            foreach ($files as &$file) {
                $id = array_shift($available_keys);
                $errors = array();
                if (isset($file['save_path']) && !ImageManager::checkImageMemoryLimit($file['save_path'])) {
                    $errors[] = $this->trans('Due to memory limit restrictions, this image cannot be loaded. Please increase your memory_limit value via your server\'s configuration settings. ', array(), 'Admin.Notifications.Error');
                }
                if (!isset($file['save_path']) || (empty($errors) && !ImageManager::resize($file['save_path'], _PS_CAT_IMG_DIR_
                            .(int)Tools::getValue('id_category').'-'.$id.'_arpa.jpg'))) {
                    $errors[] = $this->trans('An error occurred while uploading the image.', array(), 'Admin.Catalog.Notification');
                }

                if (count($errors)) {
                    $total_errors = array_merge($total_errors, $errors);
                }

                if (isset($file['save_path']) && is_file($file['save_path'])) {
                    unlink($file['save_path']);
                }
                if (isset($file['save_path'])) {
                    unset($file['save_path']);
                }

                if (isset($file['tmp_name'])) {
                    unset($file['tmp_name']);
                }

                $file['image'] = ImageManager::thumbnail(_PS_CAT_IMG_DIR_.(int)$category->id.'-'.$id.'_arpa.jpg',
                    $this->context->controller->table.'_'.(int)$category->id.'-'.$id.'_arpa.jpg', 100, 'jpg', true, true);
                $file['delete_url'] = Context::getContext()->link->getAdminLink('AdminCategories').'&ajax=1&deleteArpaImage='
                    .$id.'&id_category='.(int)$category->id.'&action=deleteArpaImage';
            }

            if (count($total_errors)) {
                $this->context->controller->errors = array_merge($this->context->controller->errors, $total_errors);
            } else {
                Tools::clearSmartyCache();
            }

            die(Tools::jsonEncode(array('arpa_image' => $files)));
        }
    }

    public function postProcess()
    {
        parent::postProcess();

    }


}
