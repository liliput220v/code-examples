<?php
class Adminka_Price extends Adminka {
    
    /**
     * Список прайсов шин
     *
     * @return boolean
     */
	public function tires_list() {

        $sql = "
            SELECT
                `ID`,
                `Name`,
                `Date`,
                `Season`
            FROM
                `price_tires`";
        $price_list = $this->db->query_assoc($sql);

        if (!empty($price_list)) {
            $this->xml .= get_array_xml('price', $price_list, array('name'));
        }

        return true;
    }

    /**
     * Добавить прайс шин
     *
     * @return boolean
     */
    public function tires_add() {

        if (!isset($_POST['do_post'])) {
            $this->xml .= "
                <price>
                    <name><![CDATA[]]></name>
                    <season>no</season>
                    <template><![CDATA[]]></template>
                </price>";
        } else {
            try {
                Price_tires::add($_POST['name'], $_POST['season'], $_POST['template']);
                redirect('?gm=price&adm=tires_list');
            } catch (Exception_Admin $e) {
                $this->error = $e->getMessage();
                $this->xml .= "
                    <price>
                        <name><![CDATA[{$_POST['name']}]]></name>
                        <season>{$_POST['season']}</season>
                        <template><![CDATA[{$_POST['template']}]]></template>
                    </price>";
            }
        }

        return true;
    }

    /**
     * Редактировать прайс
     *
     * @return boolean
     * @throws Exception_Admin
     */
    public function tires_edit() {

        if (!isset($_GET['id'])) {
            throw new Exception_Admin('Неверно переданы параметры');
        } else {
            $_GET['id'] = intval($_GET['id']);
        }
        Price_tires::exists($_GET['id']);

        if (!isset($_POST['do_post'])) {
            $sql = "
                SELECT
                    `ID`,
                    `Name`,
                    `Template`,
                    `Season`
                FROM
                    `price_tires`
                WHERE
                    `ID` = '{$_GET['id']}'";
            $price = $this->db->query_line($sql);

            $this->xml .= get_array_xml('price', $price, array('name', 'template'), 'one');
        } else {
            try {
                Price_tires::edit(
                    $_GET['id'],
                    $_POST['name'],
                    $_POST['season'],
                    $_POST['template']);
                redirect('?gm=price&adm=tires_list');
            } catch (Exception_Admin $e) {
                $this->error = $e->getMessage();

                $this->xml .= "
                    <price>
                        <id>{$_GET['id']}</id>
                        <name><![CDATA[{$_POST['name']}]]></name>
                        <season>{$_POST['season']}</season>
                        <template><![CDATA[{$_POST['template']}]]></template>
                    </price>";
            }
        }

        return true;
    }

    /**
     * Удалить прайс
     *
     * @return boolean
     * @throws Exception_Admin
     */
    public function tires_delete() {

        if (!isset($_GET['id'])) {
            throw new Exception_Admin('Неверно переданы параметры');
        } else {
            $_GET['id'] = intval($_GET['id']);
        }
        Price_tires::exists($_GET['id']);

		$query = "
			SELECT
                `Name`
			FROM
                `price_tires`
			WHERE
                `ID` = '{$_GET['id']}'";
		$price = $this->db->query_line($query);

		if (!isset($_POST['do_post'])) {
            $this->xml .= "
				<price>
					<id>{$_GET['id']}</id>
					<name><![CDATA[{$price['Name']}]]></name>
				</price>";
        } else {
            Price_tires::delete($_GET['id']);
            redirect("?gm=price&adm=tires_list");
        }

        return true;
    }

    /**
     * Шины - загрузить прайс
     * 
     * @throws Exception_Admin
     */
    public function tires_upload() {

        if (!isset($_GET['id'])) {
            throw new Exception_Admin('Неверно переданы параметры');
        } else {
            $_GET['id'] = intval($_GET['id']);
        }
        Price_tires::exists($_GET['id']);

        if (!isset($_POST['do_post'])) {
            throw new Exception_Admin('Неверно переданы параметры');
        } else {
            try {
                if (!empty($_FILES['price']['tmp_name'])) {
                    $file = $_FILES['price']['tmp_name'];
                } else {
                    throw new Exception_Admin('Файл не загружен');
                }

                Price_tires::upload($_GET['id'], $file);
                redirect("?gm=price&adm=tires_list");
            } catch (Exception_Admin $e) {
                $this->error = $e->getMessage();
            }
        }
    }

    /**
     * Скачать сводный прайс
     */
    public function tires_download() {

        $sql = "
            SELECT
                ps.`Name`,
                ptc.`Season`,
                ptc.`Code`,
                ptc.`Manufacturer`,
                ptc.`Model`,
                ptc.`Width`,
                ptc.`Profile`,
                ptc.`Diameter`,
                ptc.`Truck`,
                ptc.`Index`,
                ptc.`Count`,
                ptc.`Price`
            FROM
                `price_tires_character` AS ptc
                INNER JOIN `price_tires` AS ps
                    ON ps.`ID` = ptc.`Price_ID`";
        $tires = $this->db->query_assoc($sql);

        if (!empty($tires)) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=price.csv');
            $fp = fopen('php://output', 'w');
            $captures = array(
                'Поставщик', 'Сезон', 'Код',
                'Производитель', 'Модель', 'Ширина',
                'Профиль', 'Диаметер', 'Усиленные',
                'Индекс', 'Остаток', 'Цена'
            );

            foreach ($captures as $key => $val) {
                $captures[$key] = iconv("UTF-8", "cp1251", $val);
            }

            fputcsv($fp, $captures, ';', '"');

            foreach ($tires as $row) {
                foreach ($row as $key => $val) {
                    $row[$key] = iconv("UTF-8", "cp1251", $val);
                }
                fputcsv($fp, $row, ';', '"');
            }

            fclose($fp);
            exit();
        } else {
            $this->error = 'Нет данных для выгрузки в прайс.';
            return true;
        }
    }

    /**
     * Диски - список прайсов
     *
     * @return boolean
     */
    public function disk_list() {

        $sql = "
            SELECT
                `ID`,
                `Name`,
                `Date`
            FROM
                `price_disk`";
        $price_list = $this->db->query_assoc($sql);

        if (!empty($price_list)) {
            $this->xml .= get_array_xml('price', $price_list, array('name'));
        }

        return true;
    }

    /**
     * Добавить прайс дисков
     *
     * @return boolean
     */
    public function disk_add() {

        if (!isset($_POST['do_post'])) {
            $this->xml .= "
                <price>
                    <name><![CDATA[]]></name>
                    <template><![CDATA[]]></template>
                </price>";
        } else {
            try {
                Price_disk::add($_POST['name'], $_POST['template']);
                redirect('?gm=price&adm=disk_list');
            } catch (Exception_Admin $e) {
                $this->error = $e->getMessage();
                $this->xml .= "
                    <price>
                        <name><![CDATA[{$_POST['name']}]]></name>
                        <template><![CDATA[{$_POST['template']}]]></template>
                    </price>";
            }
        }

        return true;
    }

    /**
     * Редактировать прайс дисков
     *
     * @return boolean
     * @throws Exception_Admin
     */
    public function disk_edit() {

        if (!isset($_GET['id'])) {
            throw new Exception_Admin('Неверно переданы параметры');
        } else {
            $_GET['id'] = intval($_GET['id']);
        }
        Price_disk::exists($_GET['id']);

        if (!isset($_POST['do_post'])) {
            $sql = "
                SELECT
                    `ID`,
                    `Name`,
                    `Template`
                FROM
                    `price_disk`
                WHERE
                    `ID` = '{$_GET['id']}'";
            $price = $this->db->query_line($sql);

            $this->xml .= get_array_xml('price', $price, array('name', 'template'), 'one');
        } else {
            try {
                Price_disk::edit($_GET['id'], $_POST['name'], $_POST['template']);
                redirect('?gm=price&adm=disk_list');
            } catch (Exception_Admin $e) {
                $this->error = $e->getMessage();

                $this->xml .= "
                    <price>
                        <id>{$_GET['id']}</id>
                        <name><![CDATA[{$_POST['name']}]]></name>
                        <template><![CDATA[{$_POST['template']}]]></template>
                    </price>";
            }
        }

        return true;
    }

    /**
     * Удалить прайс дисков
     *
     * @return boolean
     * @throws Exception_Admin
     */
    public function disk_delete() {

        if (!isset($_GET['id'])) {
            throw new Exception_Admin('Неверно переданы параметры');
        } else {
            $_GET['id'] = intval($_GET['id']);
        }
        Price_disk::exists($_GET['id']);

		$query = "
			SELECT
                `Name`
			FROM
                `price_disk`
			WHERE
                `ID` = '{$_GET['id']}'";
		$price = $this->db->query_line($query);

		if (!isset($_POST['do_post'])) {
            $this->xml .= "
				<price>
					<id>{$_GET['id']}</id>
					<name><![CDATA[{$price['Name']}]]></name>
				</price>";
        } else {
            try {
                Price_disk::delete($_GET['id']);
                redirect("?gm=price&adm=disk_list");
            } catch(Exception_Admin $e) {
                $this->error = $e->getMessage();
                $this->xml .= "
                    <price>
                        <id>{$_GET['id']}</id>
                        <name><![CDATA[{$price['Name']}]]></name>
                    </price>";
            }
        }

        return true;
    }

    /**
     * Диски - загрузить прайс
     * 
     * @throws Exception_Admin
     */
    public function disk_upload() {

        if (!isset($_GET['id'])) {
            throw new Exception_Admin('Неверно переданы параметры');
        } else {
            $_GET['id'] = intval($_GET['id']);
        }
        Price_disk::exists($_GET['id']);

        if (!isset($_POST['do_post'])) {
            throw new Exception_Admin('Неверно переданы параметры');
        } else {
            try {
                if (!empty($_FILES['price']['tmp_name'])) {
                    $file = $_FILES['price']['tmp_name'];
                } else {
                    throw new Exception_Admin('Файл не загружен');
                }

                Price_disk::upload($_GET['id'], $file);
                redirect("?gm=price&adm=disk_list");
            } catch (Exception_Admin $e) {
                $this->error = $e->getMessage();
            }
        }
    }

    /**
     * Скачать сводный прайс
     */
    public function disk_download() {

        $sql = "
            SELECT
                pd.`Name`,
                pdc.`Articul`,
                pdc.`Code`,
                pdc.`Manufacturer`,
                pdc.`Model`,
                pdc.`Diameter`,
                pdc.`Width`,
                pdc.`Holes`,
                pdc.`Dia_Holes`,
                pdc.`Dia_Holes_Add`,
                pdc.`Offset`,
                pdc.`Dia`,
                pdc.`Color`,
                pdc.`Count`,
                pdc.`Price`
            FROM
                `price_disk_character` AS pdc
                INNER JOIN `price_disk` AS pd
                    ON pd.`ID` = pdc.`Price_ID`";
        $disks = $this->db->query_assoc($sql);

        if (!empty($disks)) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=diski_price.csv');
            $fp = fopen('php://output', 'w');
            $captures = array(
                'Поставщик', 'Артикул', 'Код',
                'Производитель', 'Модель', 'Диаметр',
                'Ширина', 'Отверстия', 'Диаметр распределения отверстий', 
                'Дополн. диаметр распред. отверстий', 'Вылет', 'Диаметр ступицы',
                'Цвет', 'Остаток', 'Цена'
            );

            foreach ($captures as $key => $val) {
                $captures[$key] = iconv("UTF-8", "cp1251", $val);
            }

            fputcsv($fp, $captures, ';', '"');

            foreach ($disks as $row) {
                foreach ($row as $key => $val) {
                    $row[$key] = iconv("UTF-8", "cp1251", $val);
                }
                fputcsv($fp, $row, ';', '"');
            }

            fclose($fp);
            exit();
        } else {
            $this->error = 'Нет данных для выгрузки в прайс.';
            return true;
        }
    }
}
?>