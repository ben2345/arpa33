<?php
/**
 * Class Category
 */
class Category extends CategoryCore
{
  
    /** @var mixed string or array of Description2 */
    public $description_arpa;
 
    /**
     * Category override constructor.
     *
     * @param null $idCategory
     * @param null $idLang
     * @param null $idShop
     */
    public function __construct($idCategory = null, $idLang = null, $idShop = null)
    {
        self::$definition['fields']['description_arpa'] = array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml');
        parent::__construct($idCategory, $idLang, $idShop);
    }

}
