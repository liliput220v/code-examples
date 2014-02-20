<?php
/**
 * Типы дисков
 */
class Wheels_disk_type {

	/**
	 * Добавить
	 *
	 * @param 	string $name
	 * @return 	bool
	 * @ignore 
	 * Этапы:
	 * 	1) Проверка
	 * 	2) Уникальность
	 * 	3) Добавить
	 */
	public static function add($name) {

		global $db;
		
		/*** 1) Проверка ***/
		if (empty($name)) {
			throw new Exception_Admin("Не задано наименование");
		}
		$name = $db->escape($name);
		
		/*** 2) Уникальность ***/
		Wheels_disk_type::unique($name);
		
		/*** 3) Добавить ***/
		$query = "
			INSERT INTO `wheels_disk_type`
			SET
				`Name` = '{$name}'";
		$db->query($query);
		
		return true;
	}
	
	/**
	 * Редактировать
	 *
	 * @param 	int 	$id
	 * @param 	string 	$name
	 * @return 	bool
	 * @ignore 
	 * Этапы:
	 * 	1) Проверка
	 * 	2) Уникальность
	 * 	3) Редактировать
	 */
	public static function edit($id, $name) {

		global $db;
		
		/*** 1) Проверка ***/
		$id = intval($id);
		Wheels_disk_type::is_type($id);
		
		if (empty($name)) {
			throw new Exception_Admin("Не задано наименование");
		}
		$name = $db->escape($name);
		
		/*** 2) Уникальность ***/
		Wheels_disk_type::unique($name, $id);
		
		/*** 3) Редактировать ***/
		$query = "
			UPDATE `wheels_disk_type`
			SET 
				`Name` = '{$name}'
			WHERE `ID` = '{$id}'
			LIMIT 1";
		$db->query($query);
		
		return true;
	}
	
	/**
	 * Удалить
	 *
	 * @param 	int 	$id
	 * @return 	bool
	 * @ignore 
	 * Этапы:
	 * 	1) Проверка
	 * 	2) Проверка зависемостей
	 * 	3) Удалить
	 */
	public static function delete($id) {

		global $db;
		
		/*** 1) Проверка ***/
		$id = intval($id);
		Wheels_disk_type::is_type($id);
		
		/*** 2) Проверка зависемостей ***/
		$query = "
			SELECT COUNT(*) as count
			FROM `wheels_disk`
			WHERE `Type_ID` = '{$id}'";

		$disk_count = $db->query_one($query);
		
		if ($disk_count > 0) {
			throw new Exception_Admin("Невозможно удалить, т.к. есть диски этого типа");
		}
		
		/*** 3) Удалить ***/
		$query = "
			DELETE
			FROM `wheels_disk_type`
			WHERE `ID` = '{$id}'
			LIMIT 1";
		$db->query($query);
		
		return true;
	}
	
	/**
	 * Проверка на сущестование
	 *
	 * @param 	int 	$id
	 * @return 	bool
	 */
	public static function is_type($id) {

		global $db;
		
		$query = "
			SELECT COUNT(*) as count
			FROM `wheels_disk_type`
			WHERE `ID` = '{$id}'";
		$count = $db->query_one($query);

		if ($count < 1) {
			throw new Exception_Admin("Типа диска с номером \"{$id}\" не существует.");
		}
		
		return true;
	}
	
	/**
	 * Уникальность
	 *
	 * @param 	string 	$name
	 * @param 	int 	$id
	 * @return 	bool
	 */
	private static function unique($name, $id=0) {

		global $db;
		
		$query = "
			SELECT COUNT(*) as count
			FROM `wheels_disk_type`
			WHERE `Name` = '{$name}'";
		
		if ($id !== 0) {
			$query .= "AND `ID` != '{$id}'";
		}
		$count = $db->query_one($query);
		
		if (intval($count) > 0) {
			throw new Exception_Admin("Тип диска с именем \"{$name}\" уже существует");
		}
		
		return true;
	}
}
?>