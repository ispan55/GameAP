<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/

/**
 * Модель работы с модулями
 *
 * Производит установку модулей и ...
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.7
 */
 
class Modules extends CI_Model {
	
	var $modules_data 	= array();	// Массив со всеми данными модулей
	var $modules_list 	= array();	// Массив с именами модулей
	var $menu 			= array();	// Меню
	
	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        
        /* Получение списка модулей */
        //~ $this->get_cache_modules_data();
    }
    
    /**
     * Новый модуль
     * 
     * Example:
     * 
     * $new_module = array(
     * 						'name'	 		=> 'My Module',
     * 						'file' 			=> 'module',
     * 						'version' 		=> '1.0 beta',
     * 						'developer' 	=> 'ET-NiK',
     * 						'site' 			=> 'http://hldm.org',
     * 						'information' 	=> 'This is my module',
     * );
     * 
     * $this->modules->add_module($new_module);
    */
    function add_module($data)
    {
		if ($this->db->insert('modules', $data)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
     * Список модулей
     * 
    */
    function get_modules_data($where = FALSE, $limit = 10000)
    {
		$this->db->order_by('name', 'asc'); 
		
		if(is_array($where)){
			$query = $this->db->get_where('modules', $where, $limit);
		}else{
			$query = $this->db->get('modules');
		}

		if($query->num_rows > 0) {
			
			$this->modules_data = $query->result_array();
			
			return $this->modules_data;
			
		}else{
			return NULL;
		}
	}
	
	function get_modules_list() {
		
		//~ if(empty($this->modules_data)) {
			//~ $this->get_cache_modules_data();
		//~ }
		
		if (!empty($this->modules_data)) {
			foreach ($this->modules_data as $module) {
				$this->modules_list[] = str_replace(' ', '_', strtolower($module['name']));	
			}
		}
		
		return $this->modules_list;
	}
	
	
	/**
     * Список модулей из кеша
     * 
    */
	function get_cache_modules_data()
    {
		
		/* Получение модулей из кеша */
		if (file_exists(APPPATH . 'cache/modules_list.json') && is_readable(APPPATH . 'cache/modules_list.json')) {
			
			$modules_list = json_decode(file_get_contents(APPPATH .'cache/modules_list.json'), TRUE);
			
			if ($modules_list) {
				$this->modules_data = $modules_list;
				$this->get_modules_list();
				return $modules_list;
			}
		}
		
		/* Получением модулей из базы*/
		$modules_list = $this->get_modules_data();
		
		if (is_writable(APPPATH . 'cache')) {
			/* Сохраняем в кеш */
			file_put_contents(APPPATH . 'cache/modules_list.json', json_encode($modules_list));
		}
		
		$this->modules_data = $modules_list;
		$this->get_modules_list();
		return $modules_list;
	}
	
	/**
     * Получение меню из модулей
     * 
    */
	//~ function get_menu_modules()
    //~ {
		//~ if (empty($this->modules_list)) {
			//~ /* Получение списка модулей */
			//~ $this->get_cache_modules_list();
		//~ }
		//~ 
		//~ $i = -1;
		//~ foreach ($this->modules_list as $module) {
			//~ $i++;
			//~ $this->menu[][$i];
			//~ 
		//~ }
		//~ 
		//~ 
	//~ }
}
