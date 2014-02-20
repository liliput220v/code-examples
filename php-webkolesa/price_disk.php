<?php
/**
 * Прайсы - диски
 */
class Price_disk {

	/**
     * Добавить прайс
     * 
     * @global  DB_mysqli   $db
     * @param   string      $name
     * @param   string      $template
     */
	public static function add($name, $template) {

        global $db;
        
        $name = $db->escape($name);
        
        self::unique($name);
        
        $template = $db->escape($template);
        
        $sql = "
            INSERT INTO
                `price_disk`
            SET
                `Name` = '{$name}',
                `Template` = '{$template}'";
        $db->query($sql);
    }
    
    /**
     * Проверить уникальность прайса
     * 
     * @global  DB_mysqli   $db
     * @param   string      $name
     * @param   int         $id
     * @throws  Exception_Admin
     */
    public static function unique($name, $id = 0) {

        global $db;
        
        $name = $db->escape($name);
        $id = intval($id);
        $sql_where = '';

        if ($id) {
            $sql_where = "AND `ID` != '{$id}'";
        }
        
        $sql = "
            SELECT
                COUNT(*) AS cnt
            FROM
                `price_disk`
            WHERE
                `Name` = '{$name}'
                {$sql_where}";
        $count = $db->query_one($sql);
        
        if ($count) {
            throw new Exception_Admin("Прайс с наименованием \"{$name}\" уже существует");
        }
    }
    
    /**
     * Проверить существование прайса
     * 
     * @global  DB_mysqli   $db
     * @param   int         $id
     * @throws  Exception_Admin 
     */
    public static function exists($id) {

        global $db;
        
        $id = intval($id);
        
        $sql = "
            SELECT
                COUNT(*) AS cnt
            FROM
                `price_disk`
            WHERE
                `ID` = '{$id}'";
        $count = $db->query_one($sql);
        
        if (!$count) {
            throw new Exception_Admin("Прайса с id \"{$id}\" не существует");
        }
    }
    
    /**
     * Редактировать прайс
     * 
     * @global  DB_mysqli   $db
     * @param   int         $id
     * @param   string      $name
     * @param   string      $template
     */
    public static function edit($id, $name, $template) {

        global $db;
        
        $id = intval($id);
        
        $name = $db->escape($name);
        
        self::unique($name, $id);
        
        $template = $db->escape($template);
        
        $sql = "
            UPDATE
                `price_disk`
            SET
                `Name` = '{$name}',
                `Template` = '{$template}'
            WHERE
                `ID` = '{$id}'
            LIMIT 1";
        $db->query($sql);
    }
    
    /**
     * Удалить прайс
     * 
     * @global  DB_mysqli   $db
     * @param   int         $id 
     */
    public static function delete($id) {

        global $db;
        
        $id = intval($id);
        self::exists($id);
        
        $sql = "
            DELETE FROM
                `price_disk_character`
            WHERE
                `Price_ID` = '{$id}'";
        $db->query($sql);
        
        $sql = "
            DELETE FROM
                `price_disk`
            WHERE
                `ID` = '{$id}'
            LIMIT 1";
        $db->query($sql);
    }
    
    /**
     * Загрузить прайс
     * 
     * @global  DB_mysqli   $db
     * @param   int         $id
     * @param   string      $file
     * @throws  Exception_Admin 
     */
    public static function upload($id, $file) {

        global $db;
        
        $id = intval($id);
        self::exists($id);
        
        if (empty($file)) {
            throw new Exception_Admin('Файл не загружен');
        }
        
        $template = self::get_template($id);
        
        self::parse($id, $file, $template);
        
        $sql = "
            UPDATE
                `price_disk`
            SET
                `Date` = NOW()
            WHERE
                `ID` = '{$id}'
            LIMIT 1";
        $db->query($sql);
    }
    
    /**
     * Получить шаблон для парсинга
     * 
     * @global  DB_mysqli   $db
     * @param   int         $id
     * @return  string 
     */
    private static function get_template($id) {

        global $db;
        
        $id = intval($id);
        self::exists($id);
        
        $sql = "
            SELECT
                `Template`
            FROM
                `price_disk`
            WHERE
                `ID` = '{$id}'";
        $template = $db->query_one($sql);
        
        return $template;
    }
    
    /**
     * Распарсить файл в соответствии с шаблоном
     * 
     * @global  DB_mysqli   $db
     * @param   int         $id
     * @param   string      $file
     * @param   string      $template
     * @throws  Exception_Admin 
     */
    private static function parse($id, $file, $template) {

        global $db;
        
        if (!is_file($file)) {
            throw new Exception_Admin("Файла и именем \"{$file}\" не существует");
        }
		
        $template = self::parse_template($template);
        
        $content = file_get_contents($file);
		$content = iconv("cp1251", "UTF-8", $content);
		file_put_contents($file, $content);
	
        $csv = array();
		$handle = fopen($file, "r");
		while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
			$csv[] = $data;
		}
		fclose($handle);
		// всегда пустой, поэтому удаляем
        unset($csv[0]);     
        
        $data = array();
        foreach ($csv as $csv_row) {
            $data_row = array(
                'code' => '',
                'articul' =>'',
                'manufacturer' => '',
                'model' => '',
                'width' => '',
                'diameter' => '',
                'holes' => '',
                'diaHoles' => '',
                'diaHolesAdd' => '',
                'offset' => '',
                'dia' => '',
                'color' => '',
                'count' => '',
                'price' => ''
            );
            
            $count_csv = count($csv_row);
            $count_template = count($template);
            
            if ($count_csv == $count_template) {
                for ($i = 0; $i < $count_csv; $i++) {
                    if (preg_match("#^{$template[$i]}$#isu", $csv_row[$i], $matches)) {
                        foreach ($matches as $key => $val) {
                            if (key_exists($key, $data_row)) {
                                $data_row[$key] = trim($val);
                            }
                        }
                    }
                }

                $data[] = $data_row;
            } else {
                throw new Exception_Admin('Количество колонок в прайсе не соответствует шаблону');
            }
        }
        
        if (empty($data)) {
            throw new Exception_Admin('Не удалось извлечь данные из прайса');
        }

        self::save_data($id, $data);
        
    }
    
    /**
     * Распарсить шаблон в регулярки
     * 
     * @param   string      $template
     * @return  array
     * @throws  Exception_Admin
     */
    private static function parse_template($template) {

        $template_reg = array(
            '_' => '.*',
            '{code}' => '(?P<code>.*)',
            '{articul}' => '(?P<articul>.*)',
            '{manufacturer}' => '(?<manufacturer>[&a-zA-ZА-я\-]+[ ]?[a-zA-ZА-я]+)',
            '{model}' => '(?P<model>[0-9a-zA-ZА-я\-]+)',
            '{width}' => '(?P<width>[\d,.]{1,5})',
            '{diameter}' => '(?P<diameter>[\d]{2,3})',
            '{holes}' => '(?P<holes>[\d]{1,2})',
            '{diaHoles}' => '(?P<diaHoles>[\d.,]+)',
            '{diaHolesAdd}' => '[-/]?(?P<diaHolesAdd>([\d.,]{3,5})?)',
            '{offset}' => '(?P<offset>[\d.,+\-]+)',
            '{dia}' => '(?P<dia>[\d.,]*)',
            '{color}' => '(?P<color>[a-zA-ZА-я]*[a-zA-Z. /]*)',
            '{count}' => '>?(более)?(больше)? ?(?P<count>[\d]*)',
            '{price}' => '(?P<price>[\d ]+[.,\d]{0,3})'
        );
        
        $template = explode('|', $template);
        
        if (empty($template)) {
            throw new Exception_Admin('Ошибка разбора шаблона прайса');
        }
        
        foreach ($template as $key => $val) {
            foreach ($template_reg as $slug => $reg) {
                $val = mb_str_replace($slug, $reg, $val);
            }
            $template[$key] = $val;
        }
        
        return $template;
    }
    
    /**
     * Сохранить данные в базу
     * 
     * @global  DB_mysqli   $db
     * @param   int         $price_id
     * @param   array       $data 
     */
    private static function save_data($price_id, $data) {

        global $db;
        
        $price_id = intval($price_id);
        self::exists($price_id);
        
        $sql = "
            DELETE FROM
                `price_disk_character`
            WHERE
                `Price_ID` = '{$price_id}'";
        $db->query($sql);
        
        foreach ($data as $row) {
            //не пишем строки без указанной цены или количества
            if (empty($row['count']) and empty($row['price'])) {
                continue;
            }
            
            $code = $db->escape($row['code']);
            $articul = $db->escape($row['articul']);
            $manufacturer = $db->escape($row['manufacturer']);
            $model = $db->escape($row['model']);
            //заменяем запятые
            $diameter = mb_ereg_replace(",", ".", $row['diameter']);
            $width = mb_ereg_replace(",", ".", $row['width']);
            $dia_holes = mb_ereg_replace(",", ".", $row['diaHoles']);
            $dia_holes_add = mb_ereg_replace(",", ".", $row['diaHolesAdd']);
            $offset = mb_ereg_replace(",", ".", $row['offset']);
            $dia = mb_ereg_replace(",", ".", $row['dia']);
            
            $diameter = floatval($diameter);
            $width = floatval($width);
            $holes = intval($row['holes']);
            $dia_holes = floatval($dia_holes);
            $dia_holes_add = floatval($dia_holes_add);
            $offset = floatval($offset);
            $dia = floatval($dia);
            $color = $db->escape($row['color']);
            $count = $db->escape($row['count']);
            //убираем пробелы
            $price = mb_ereg_replace("[ ]*", "", $row['price']);
            $price = $db->escape($price);
            
            $sql = "
                INSERT INTO
                    `price_disk_character`
                SET
                    `Price_ID` = '{$price_id}',
                    `Code` = '{$code}',
                    `Articul` = '{$articul}',
                    `Manufacturer` = '{$manufacturer}',
                    `Model` = '{$model}',
                    `Diameter` = '{$diameter}',
                    `Width` = '{$width}',
                    `Holes` = '{$holes}',
                    `Dia_holes` = '{$dia_holes}',
                    `Dia_holes_add` = '{$dia_holes_add}',
                    `Offset` = '{$offset}',
                    `Dia` = '{$dia}',
                    `Color` = '{$color}',
                    `Price` = '{$price}',
                    `Count` = '{$count}'";
            $db->query($sql);
        }
    }
}
?>