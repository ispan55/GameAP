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
 
// ------------------------------------------------------------------------

/**
 * Логи
 *
 * Модель для работы с логами серверов
 *
 * @package		Game AdminPanel
 * @category	Models
 * @category	Models
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.3.3 [13.04.2013]
 */

class Logs extends Servers {
	
	var $log_files = array(); // Логи
	var $time;
	
	var $err_desc = FALSE; // Описание ошибки
	var $errors = FALSE;   // Краткое имя ошибки
	
	//$this->servers->server_data;
	
	public function __construct()
    {
        parent::__construct();
        
        $this->time = time();
    }
	
	function set_server_data($server_data)
    {
		$this->server_data = $server_data;
	}
	
	
	// ----------------------------------------------------------------
    /**
     * Сортировка массива по возрастанию
    */
	function log_sort_asc($a, $b) 
	{
		if ($a['file_time'] === $b['file_time']) return 0;
		return $a['file_time'] > $b['file_time'] ? 1 : -1;
	}
	
	// ----------------------------------------------------------------
    /**
     * Сортировка массива по убыванию
    */
	function log_sort_desc($a, $b) 
	{
		if ($a['file_time'] === $b['file_time']) return 0;
		return $a['file_time'] < $b['file_time'] ? 1 : -1;
	}
	
	
	// ----------------------------------------------------------------
    /**
     * Фильтрация логов
     * Фильтрует логи по убиванию/возрастанию, по заданному пределу
     * количества логов, по времени.
     * 
     * @param string
     * @param int
     * @param int
     * @return array
    */
	function filter_logs($sort = 'DESC', $limit = 10, $allow_time = 0){
		
		$allow_time = (int)$allow_time;

		/* Сортировка */
		if($sort == 'DESC'){
			// Сортировка по убыванию
			uasort($this->log_files, array('Logs','log_sort_desc'));
		}else{
			// Сортировка по возрастанию
			uasort($this->log_files, array('Logs','log_sort_asc'));
		}
		
		$log_files = array();
		$num = 0;
		foreach ($this->log_files as $array) {
			$num++;
			
			/* */
			if($allow_time != 0 && $array['file_time'] < $this->time - $allow_time){
				continue;
			}
			
			/* Предел количества логов достигнут */
			if($num > $limit){
				break;
			}
			
			$log_files[] = $array;
		}
		
		$this->log_files = $log_files;
		
		return $this->log_files;
	}

	// ----------------------------------------------------------------
	
    /**
     * Листинг логов
     * 
     * @param string - имя файла
     * @param string - тип логов
     * @param int - лимит
     * @param int - время
    */
	function list_server_log($file_name = '', $file_ext = 'log',  $dir = FALSE, $limit = 100, $allow_time = 0) {
		
		$this->load->helper('path');
		$this->load->helper('date');
		$this->load->helper('file');
		
		//print_r($dir);
		
		// Заменяем некоторые символы для безопасности
		$dir = str_replace('..', '', $dir);
		$dir = str_replace('//', '/', $dir);
		$file_name = str_replace('..', '', $file_name);
		$file_name = str_replace('/', '', $file_name);
		$file_name = str_replace('\\', '', $file_name);
		
		/* Определение имени файла для glob */
		if (preg_match('/^([0-9a-zA-Z\-\_]{1,32})$/si', $file_name)) {  
			$file_name = '*' . $file_name . '*.' . $file_ext; 
		}else{
			$file_name = '*.' . $file_ext;
		}
		
		/* Определение, является сервер локальным или удаленным */
		if(!$this->servers->server_data['ds_id']){
			// Сервер локальный, получаем данные для него
			$dir = $this->servers->server_data['local_path'] . '/' . $this->servers->server_data['dir'] . '/' . $dir;
		}else{
			// Сервер удаленный
			$dir = $this->servers->server_data['ftp_path'] . '/' . $this->servers->server_data['dir'] . '/' . $dir;
		}

		if($dir){
			$num = -1;
			$log_files = array();
			
			/* Определение, является сервер локальным или удаленным */
			if(!$this->servers->server_data['ds_id']) {
				// Сервер локальный
				
				/* 
				 * Определение привилегий на директорию
				 * в случае, если у директории отсутствуют права на выполнение, не будет известно некоторых необходимых данных
				 * 
				 * Подробнее обо все, что написано ниже - http://php.net/manual/ru/function.fileperms.php
				*/
				if(!is_dir($dir)) {
					$this->errors = 'Директория не найдена';
					return FALSE;
				}
				
				if(!is_readable($dir)) {
					$this->errors = 'Отсутствуют права на чтение директории';
					return FALSE;
				}

				/*
				$perms = fileperms($dir);
				
				$dir_perms = array('r' => '', 'x' => '');
				$dir_perms['r'] .= (($perms & 0x0100) ? 'r' : '-');
				$dir_perms['r'] .= (($perms & 0x0020) ? 'r' : '-');
				$dir_perms['r'] .= (($perms & 0x0004) ? 'r' : '-');
				
				$dir_perms['x'] .= (($perms & 0x0040) ?(($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
				$dir_perms['x'] .= (($perms & 0x0008) ?(($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
				$dir_perms['x'] .= (($perms & 0x0001) ?(($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

				if($dir_perms['r'] != 'rrr') {
					$this->errors = 'No permission for read';
					//$this->err_desc = 'Отсутствуют права на чтение директории';
					return FALSE;
				} elseif($dir_perms['x'] != 'xxx') {
					$this->errors = 'No permission for execute';
					//$this->err_desc = 'Отсутствуют права на выполнение директории';
					return FALSE;
				}
				*/

				$files_list = $this->get_local_files($this->servers->server_data, $dir . '/' . $file_name, TRUE, TRUE);
			}else{
				// Сервер удаленный
				$files_list = $this->get_remote_files($this->servers->server_data, $dir . '/' . $file_name, TRUE, TRUE);
			}
			
			if($files_list){	
				foreach ($files_list as $file) {
					/* Достижение лимита */
					if($num == $limit){
						break;
					}

					if($allow_time != 0 && $file['file_time'] < $this->time - $allow_time){
						continue;
					}
					
					$num++;
					
					$log_files[$num]['file_time'] = $file['file_time'];
						
					$log_files[$num]['file_name'] = basename($file['file_name']);
					$log_files[$num]['file_path'] = $file['file_name'];
					$log_files[$num]['file_size'] = human_size($file['file_size']);
					$log_files[$num]['file_human_time'] = unix_to_human($log_files[$num]['file_time'], TRUE, 'eu');
				}
			}else{
				/* Файлы не найдены */
				return FALSE;
			}
			
		}else{
			return FALSE;
		}
		
		/* Добавляем в массив */
		$this->log_files = array_merge($this->log_files, $log_files);

		return $log_files;
	}
	
	
	
	// ----------------------------------------------------------------
    /**
     * Получает содержимое лога
     * 
    */
	function get_log($log_path, $file_name)
    {
		
		$this->load->helper('path');

		/* Определение, является сервер локальным или удаленным */
		if($this->servers->server_data['local_server']) {
			// Сервер локальный
			$dir = set_realpath($this->servers->server_data['local_path'] . '/' . $this->servers->server_data['dir'] . '/' . $log_path);
			$file_contents = $this->servers->read_local_file($dir . $file_name);
			
		} else {
			
			// Сервер удаленный
			$dir = set_realpath($this->servers->server_data['ftp_path'] . '/' . $this->servers->server_data['dir'] . '/' . $log_path);
			$file_contents = $this->servers->read_remote_file($dir . $file_name);
		}

		return $file_contents;
	}
}
