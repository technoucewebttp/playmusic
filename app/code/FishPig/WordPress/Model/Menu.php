<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
namespace FishPig\WordPress\Model;

use \FishPig\WordPress\Model\Term;

class Menu extends Term
{
	/**
	 *
	**/
	const ENTITY = 'wordpress_menu';

	/**
	 * @const string
	*/
	const CACHE_TAG = 'wordpress_menu';
	
	/**
	 * Event data
	 *
	 * @var string
	 */
	protected $_eventPrefix      = 'wordpress_menu';
	protected $_eventObject      = 'menu';
	
	/**
	 * Cache to stop menu being generated multiple times
	 *
	 * @var array
	**/
	protected $_menuCache	 = null;
	
	/**
	 *
	**/
	public function _construct()
	{
        $this->_init('FishPig\WordPress\Model\ResourceModel\Menu');
		
		return parent::_construct();
	}
	
	/**
	 * Gets a simple array of the menu
	 * For the actual menu item objects, use getMenuTreeObjects
	 *
	 * @return array|false
	**/
	public function getMenuTreeArray()
	{
		if ($tree = $this->getMenuTreeObjects()) {
			$menu = array();
			
			foreach($tree as $node) {
				$menu[] = $this->_getMenuTreeArray($node);
			}
			
			return $menu;
		}
		
		return false;
	}
	
	/**
	 *
	**/
	protected function _getMenuTreeArray($node)
	{
		$data = array(
			'id' => 'wp-' . $node->getId(),
			'label' => $node->getLabel(),
			'url' => $node->getUrl(),
		);
		
		$children = $node->getChildrenItems();

		if (count($children) > 0) {
			$data['children'] = array();
			
			foreach($children as $child) {
				$data['children'][] = $this->_getMenuTreeArray($child);
			}
		}
		
		return $data;
	}
	
	/**
	 *
	**/
	public function getMenuTreeObjects()
	{
		if (null !== $this->_menuCache) {
			return $this->_menuCache;
		}
		
		$this->_menuCache = false;
		
		$items = $this->getMenuItems();
		
		if (count($items) > 0) {
			foreach($items as $item) {
				$this->_menuCache[] = $this->_getMenuTreeObjects($item);
			}
		}
		
		return $this->_menuCache;
	}
	
	/**
	 *
	**/
	protected function _getMenuTreeObjects($item)
	{
		$children = $item->getChildrenItems();

		if (count($children) > 0) {
			foreach($children as $child) {
				$this->_getMenuTreeObjects($child);
			}
		}
		
		return $item;
	}

	/**
	 * Retrieve the root menu items
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Menu_Item_Collection
	 */
	public function getMenuItems()
	{
		return $this->_getObjectResourceModel()
			->addIsViewableFilter()
			->addTermIdFilter($this->getId(), $this->getTaxonomy());
	}

	/**
	 * Retrieve the taxonomy type
	 *
	 * @return string
	 */
	public function getTaxonomy()
	{
		return 'nav_menu';
	}

	/**
	 * Retrieve the object resource model
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Post_Collection
	 */    
    protected function _getObjectResourceModel()
    {
	    return $this->_app->getFactory()->getFactory('Menu\Item')
	    	->create()
	    	->getCollection()
	    	->addParentItemIdFilter(0);
    }
}
