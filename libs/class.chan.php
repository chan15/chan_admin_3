<?php
/**
 * 主功能
 */
class chan {
    // 資料庫相關
    var $charset          = 'UTF-8'; // 預設編碼
    var $host             = ''; // Server
    var $db               = ''; // 資料庫名稱
    var $username         = ''; // 帳號
    var $password         = ''; // 密碼
    var $conn             = '';
    var $makeRecordCount  = true;
    var $recordCount      = 0; // recordset count
    var $totalRecordCount = 0; // total recordset count
    var $lastInsertId     = 0; // 資料庫最新的一筆 id
    var $fieldArray       = array(); // 欄位陣列
    var $valueArray       = array(); // 值陣列
    var $sqlError         = ''; // sql 語法錯誤文字
    var $table            = ''; // table
    var $pk               = ''; // primary key
    var $pkValue          = ''; // primary key value

	// Email 相關
    var $emailDebug    = false; // 是否顯示 Email 錯誤，true 顯示 false 不顯示
	var $emailFrom     = '';
	var $emailTo       = '';
	var $emailFromName = '';
	var $emailSubject  = '';
	var $emailContent  = '';

    // 預設參數
    var $meta            = '<meta http-equiv = "Content-Type" content = "text/html; charset = utf-8" />';
    var $thumbDebug      = false; // 是否顯示縮圖錯誤，true 顯示 false 不顯示
    var $loginPage       = 'login.php'; // 預設登入頁面
    var $fileDeleteArray = array(); // image delete array
    
    // Server 驗證參數
    var $captchaSource   = 'images/captcha/'; // captcha 圖片路徑
    var $validateArray     = array(); // Server 驗證欄位
    var $validateMessage = ''; // Server 驗證訊息
    var $validateError   = false; // 是否有誤
    
    // 資料參數
    var $page       = 0; // 現在頁面參數
    var $totalPages = 0; // 總分頁數量

    // 圖片上傳參數
    var $imageUploadRatio   = 1000; // 預設最大寬度
    var $imageUploadAllowed = array('image/*'); // 預設圖片格式
    var $imageUploadSize    = 2097152; // 預設檔案大小 2MB

    // 檔案上傳參數
    var $fileUploadAllowed = array('image/*, application/*, archives/zip'); // 預設檔案格式
    var $fileUploadSize    = 5242880; // 預設檔案大小 5MB
    
    /////////////////////////////////////////////////
    // 環境處理
    /////////////////////////////////////////////////

    /**
     * 啟動 session
     */
    function sessionOn() {
        if (!isset($_SESSION)) session_start();
    }
        
    /////////////////////////////////////////////////
    // 資料處理
    /////////////////////////////////////////////////
    
    /**
     * 執行 SQL 內容
     * $sql - SQL 語法
     */
    function sqlExecute($sql) {
        $ret = true;
        mysql_select_db($this->db, $this->conn);
        if (!mysql_query($sql, $this->conn)) {
            $ret = false;
            $this->sqlError = mysql_error();
        } else {
            $this->lastInsertId = mysql_insert_id();
        }
        return $ret;
    }

    /**
     * 資料庫連線
     */
    function dbConnect() {
        $this->conn = mysql_connect($this->host, $this->username, $this->password) or trigger_error(mysql_error(),E_USER_ERROR);
        mysql_query("SET NAMES 'utf8'");
    }

    /**
     * SQL Injection
     */
    function toSql($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
      if (PHP_VERSION < 6) {
        $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
      }

      $theValue = mysql_real_escape_string($theValue);

      switch ($theType) {
        case "text":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;    

        case "long":
        case "int":
          $theValue = ($theValue != "") ? intval($theValue) : "NULL";
          break;
          
        case "double":
          $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
          break;
          
        case "date":
          $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
          break;
          
        case "defined":
          $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
          break;
      }
      return $theValue;
    }
    
    /**
     * 將table變數存進陣列
     * $field - 欄位
     * $value - 值
     * $type - 型態
     */
    function addField($field, $value, $type = 'text') {
        $this->fieldArray[] = '`'.$field.'`';
        $this->valueArray[] = $this->toSql($value, $type);
    }

    /**
     * 抓取檔案名稱
     * $field - 欄位名稱
     **/
    function getFileName($field) {
        $sql = sprintf("SELECT %s FROM %s WHERE %s = %s",
                $field,
                $this->table,
                $this->pk,
                $this->pkValue);
        $row = $this->myOneRow($sql);
        return $row[$field];
    }

    /**
     * 刪除資料庫檔案功能
     * $path - 路徑
     **/
    function dataFileDelete($path) {
        if (count($this->fileDeleteArray) > 0) {
            if (is_dir($path)) {
                foreach ($this->fileDeleteArray as $fileName) {
                    @unlink($path.$fileName);

                    $fileDelHead = explode('.', $fileName);
                    $thumbDir = $path.'/thumbnails/';
                    $handle = @opendir($thumbDir);
                    while($file = readdir($handle)){
                        if($file != "." && $file != ".."){
                            $fileDel = explode('_', $file);
                            if ($fileDelHead[0] == $fileDel[0]) {
                                unlink($thumbDir.$file);
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        }
    }

    /**
     * 新增資料功能
     */
    function dataInsert() {
        $sqlIns = sprintf("INSERT INTO %s (%s) VALUES(%s)",
            $this->table,
            implode(', ', $this->fieldArray),
            implode(', ', $this->valueArray));
            
        $this->clearFields();
        return $this->sqlExecute($sqlIns);
    }
    
    /**
     * 更新資料功能
     * $where - WHERE 條件
     */
    function dataUpdate($where = '') {
        $sqlStr = array();
        foreach ($this->fieldArray as $k => $v) {
            $sqlStr[] = $v.' = '.$this->valueArray[$k];
        }
        
        if ($where == '') {
            $where = sprintf("%s = %s",
                $this->pk,
                $this->toSql($this->pkValue, 'int'));
        }

        $sqlUpd = sprintf("UPDATE %s SET %s WHERE %s",
            $this->table,
            implode(', ', $sqlStr),
            $where);

        $this->clearFields();
        return $this->sqlExecute($sqlUpd);
    }
    
    /**
     * 刪除資料功能
     * $where - WHERE 條件
     */
    function dataDelete($where = '') {
        if ($where == '') {
            $where = sprintf("%s = %s",
                $this->pk,
                $this->toSql($this->pkValue, 'int'));
        }

        $sqlDel = sprintf("DELETE FROM %s WHERE %s",
            $this->table,
            $where);
        
        $this->clearFields();
        return $this->sqlExecute($sqlDel);
    }
    
    /**
     * 清除欄位變數
     */
    function clearFields() {
        $this->pk = '';
        $this->pkValue = '';
        unset($this->fieldArray);
        unset($this->valueArray);
    }
   
    /////////////////////////////////////////////////
    // 驗證處理
    /////////////////////////////////////////////////
    
    /**
     * 檢查來源網址
     */
     function checkSourceUrl() {
         if (stripos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) == false) exit;
     }

    /**
     * 將驗證欄位加入陣列
     * $name - 驗證名稱
     * $field - 欄位名稱
     * $type - 驗證型態 text, email, number, positive, boolean, file, duplicate
     * $tableField - 若型態為 duplicate，要輸入 table 對應的名稱
     * $limit - 字數限制
     * $method - 傳送類型
     */
    function addValidateField($name, $field, $type = 'text', $tableField = '', $limit = 0, $method = 'POST') {
        array_push($this->validateArray, array(
            'name' => $name,
            'field' => $field,
            'type' => $type,
            'tableField' => '`'.$tableField.'`',
            'limit' => $limit,
            'method' => $method)
        );
    }
    
    /**
     * 驗證欄位功能
     * 先將所需要的欄位使用 addValidateField 加入以後執行此功能
     * 若有缺少內容或者是型態錯誤會將變數 validateError 宣告為 true
     * 並且把錯誤訊息寫入 validateMessage
     */
    function serverValidate() {
        $emailPattern = '/^\w+[\w\+\.\-]*@\w+(?:[\.\-]\w+)*\.\w+$/i'; // Email正規化
        $numberPattern = '/[0-9]/'; // 數字正規化
        $positvePattern = '/^\d+$/'; // 正整數正規化
        $booleanPattern = '/^\d{0,1}+$/'; // 正規化布林值
        
        foreach ($this->validateArray as $v) {
            $value = ($v['type'] == 'file') ? @$_FILES[$v['field']]['name'] : (($v['method'] == 'POST') ? @$_POST[$v['field']] : @$_GET[$v['field']]);
            $name = $v['name'];
            $type = $v['type'];
            $tableField = $v['tableField'];
            $limit = $v['limit'];

            if (trim($value) == '') { // 檢查是否為空值
                $this->validateMessage .= '請填寫'.$name.'<br>';
                $this->validateError = true;
            } else {
                switch ($type) {
                    case 'email': $pattern = $emailPattern; break; // Email格式
                    case 'number': $pattern = $numberPattern; break; // 數字格式
                    case 'positive': $pattern = $positvePattern; break; // 正整數格式
                    case 'boolean': $pattern = $booleanPattern; break; // 布林值格式
                    case 'duplicate':
                        // 檢查有沒有重複
                        if ($this->pk == '') {
                            $sql = sprintf("SELECT * FROM %s WHERE %s = %s",
                                $this->table,
                                $tableField,
                                $this->toSql($value, 'text'));
                        } else {
                            $sql = sprintf("SELECT * FROM %s WHERE %s = %s AND %s != %s",
                                $this->table,
                                $tableField,
                                $this->toSql($value, 'text'),
                                $this->pk,
                                $this->toSql($this->pkValue, 'int'));
                        } 

                        $row = $this->myOneRow($sql);

                        if ($row) {
                            $this->validateMessage .= $name.'重複<br>';
                            $this->validateError = true;
                        }
                        break;
                    default: $pattern = '';
                }
                if (!empty($pattern) && !preg_match($pattern, $value)) { // 檢查正規化
                    $this->validateMessage .= $name.'格式錯誤<br>';
                    $this->validateError = true;
                } else {
                    if ($limit > 0 && mb_strlen($value, $this->charset) > $limit) {
                        $this->validateMessage .= $name.'超過字數<br>';
                        $this->validateError = true;
                    }
                }
            }
        }
        
        return $this->validateError;
    }
    
    /**
     * 呈現驗證錯誤訊息
     */
    function showValidateMsg() {
        if ($this->validateError) {
            echo $this->meta;
            echo $this->validateMessage;
            exit;            
        }
    }
    
    /////////////////////////////////////////////////
    // 資料處理
    /////////////////////////////////////////////////
    
    /**
     * 取出單筆資料
     * $sql - SQ L語法
     */
    function myOneRow($sql) {
        mysql_select_db($this->db, $this->conn);
        $rec = mysql_query($sql, $this->conn) or die(mysql_error());
        $row = mysql_fetch_assoc($rec);
        $num = mysql_num_rows($rec);
        $count = mysql_num_fields($rec);
        $ret = array();
        
        if ($num > 0) {
            $temp = array();
            for ($i = 0; $i < $count; $i++) {
                $name = mysql_field_name($rec, $i);
                $ret[$name] = $row[$name];
            }
        } else {
            $ret = 0;
        }
        
        mysql_free_result($rec);

        return $ret;
    }
    
    /**
     * 將Sql結果存成陣列傳回
     * $sql - SQL 語法
     */
    function myRow($sql) {
        mysql_select_db($this->db, $this->conn);
        $rec = mysql_query($sql, $this->conn) or die(mysql_error());
        $row = mysql_fetch_assoc($rec);
        $this->recordCount = mysql_num_rows($rec);
        if ($this->makeRecordCount) {
            $this->totalRecordCount = $this->recordCount;
        }
        $count = mysql_num_fields($rec);
        
        if ($this->recordCount > 0) {
            $ret = array();
            $temp = array();
            do {
                for ($i = 0; $i < $count; $i++) {
                    $name = mysql_field_name($rec, $i);
                    $temp[$name] = $row[$name];
                }

                array_push($ret, $temp);
            } while($row = mysql_fetch_assoc($rec));
        } else {
            $ret = 0;
        }
        
        mysql_free_result($rec);

        return $ret;
    }
    
    /**
     * 分頁資料及
     * $sql - SQL 語法
     * $max - 一頁最多幾筆資料
     */
    function myRowList($sql, $max = 10) {
        $this->page = isset($_GET['page']) ? $_GET['page'] : 0;
        $startRow = $this->page*$max;
        $row = $this->myRow($sql);
        if (!$row) return 0;
        $this->totalRecordCount = count($row);
        $this->totalPages = ceil($this->totalRecordCount/$max)-1;
        $sqlPages = sprintf("%s LIMIT %d, %d", $sql, $startRow, $max);
        $this->makeRecordCount = false;
        $row = $this->myRow($sqlPages);
        return $row;
    }
   
    /**
     * 組合網頁參數字串
     * $str - 使用的字串
     */
    function combineQueryString($str) {
        $ret = '';
        if (!empty($_SERVER['QUERY_STRING'])) {
          $params = explode("&", $_SERVER['QUERY_STRING']);
          $newParams = array();
          foreach ($params as $param) {
            if (!stristr($param, $str)) {
              array_push($newParams, $param);
            }
          }
          if (count($newParams) != 0) {
            $ret = "&".htmlentities(implode("&", $newParams));
          }
        }
        
        return $ret;
    }
    
    /**
     * 使用預設分頁模組
     * $limit - 最多出現的頁碼數量
     */
    function pager($limit = 5) {
        $sep = '&nbsp;';
        $ret = '';
        // $ret .= $this->pageString('first').$sep;
        $ret .= $this->pageString('prev',' ',"prev").$sep;
        $ret .= $this->pageNumber($limit).$sep;
        $ret .= $this->pageString('next',' ',"next").$sep;
        // $ret .= $this->pageString('last').$sep;
        return $ret;
    } 

    /**
     * bootstrap pager
     * @$limit 每次頁數
     */
    function bootstrapPager($limit = 6) {
        $currentPage = $_SERVER["PHP_SELF"];
        $ret = '';
        $ret .= '<ul class="pagination">';
        $limitLinksEndCount = $limit;
        $temp = (int)($this->page+1);
        $startLink = (int)(max(1, $temp-intval($limitLinksEndCount / 2)));
        $temp = (int)($startLink+$limitLinksEndCount-1);
        $endLink = min($temp, $this->totalPages+1);

        // prev page
        if ($this->page > 0) {
            $ret .= sprintf('<li><a href="%s?page=%d%s">«</a></li>',
                $currentPage,
                max(0, $this->page-1),
                $this->combineQueryString('page'));
        } else {
            $ret .= sprintf('<li class="disabled"><a>«</a></li>',
                $currentPage,
                max(0, $this->page-1),
                $this->combineQueryString('page'));
        }

        if ($endLink != $temp) $startLink = max(1, (int)($endLink-$limitLinksEndCount+1));

        for ($i = $startLink; $i <= $endLink; $i++) {
          $limitPageEndCount = $i-1;
          if ($limitPageEndCount != $this->page) {
          $ret .= sprintf('<li><a href="%s?page=%d%s">%s</a></li>',
              $currentPage,
              $limitPageEndCount,
              $this->combineQueryString('page'),
              $i);
          } else {
              $ret .= '<li class="disabled"><a>' . $i . '</a></li>';
          }
        }

        // next page
        if ($this->page < $this->totalPages) {
            $ret .= sprintf('<li><a href="%s?page=%d%s">»</a></li>',
                $currentPage,
                min($this->totalPages, (int)($this->page+1)),
                $this->combineQueryString('page'));
        } else {
            $ret .= sprintf('<li class="disabled"><a>»</a></li>',
                $currentPage,
                min($this->totalPages, (int)($this->page+1)),
                $this->combineQueryString('page'));
        }

        $ret .= "</ul>";

        return $ret;
    }
    
    /**
     * 上下頁功能
     * $method - 功能 prev: 上一頁, next: 下一頁
     */
    function pageString($method, $string = '', $class = '') {
        $currentPage = $_SERVER["PHP_SELF"];
        $ret = '';
        
        switch ($method) {
            case 'first': // 第一頁
                if ($this->page > 0) {
                    if ($string == '') {
                        $string = '第一頁';
                    }
                    $ret = '<a href="'.sprintf("%s?page=%d%s", $currentPage, 0, $this->combineQueryString('page')).'" class="'.$class.'">'.$string.'</a>';
                }
                break;
            case 'prev': // 上一頁
                if ($this->page > 0) {
                    if ($string == '') {
                        $string = '上一頁';
                    }
                    $ret = '<a href="'.sprintf("%s?page=%d%s", $currentPage, max(0, $this->page-1), $this->combineQueryString('page')).'" class="'.$class.'">'.$string.'</a>';
                }
                break;
            case 'next': // 下一頁
                if ($this->page < $this->totalPages) {
                    if ($string == '') {
                        $string = '下一頁';
                    }
                    $ret = '<a href="'.sprintf("%s?page=%d%s", $currentPage, min($this->totalPages, $this->page+1), $this->combineQueryString('page')).'" class="'.$class.'">'.$string.'</a>';
                }
                break;
            case 'last': // 最後頁
                if ($this->page < $this->totalPages) {
                    if ($string == '') {
                        $string = '最後頁';
                    }
                    $ret = '<a href="'.sprintf("%s?page=%d%s", $currentPage, $this->totalPages, $this->combineQueryString('page')).'" class="'.$class.'">'.$string.'</a>';
                }
                break;
        }
        
        return $ret;
    }
    
    /**
     * 頁碼功能
     * $limit - 最多出現的頁碼數量
     * $set - 分隔符號
     */
    function pageNumber($limit = 5, $sep = '&nbsp;') {
        $ret = '';
        $currentPage = $_SERVER["PHP_SELF"];
        $limitLinksEndCount = $limit;
        $temp = $this->page+1;
        $startLink = max(1,$temp-intval($limitLinksEndCount/2));
        $temp = $startLink+$limitLinksEndCount-1;
        $endLink = min($temp, $this->totalPages+1);
        if($endLink != $temp) $startLink = max(1, $endLink-$limitLinksEndCount+1);

        for ($i = $startLink; $i <= $endLink; $i++) {
          $limitPageEndCount = $i-1;
          if ($limitPageEndCount != $this->page) {
            $ret .= sprintf('<a href="'."%s?page=%d%s", $currentPage, $limitPageEndCount, $this->combineQueryString('page').'">');
            $ret .= "$i</a>";
          } else {
            $ret .= "<strong>$i</strong>";
          }
          if($i != $endLink) $ret .= $sep;
        }
        
        return $ret;
    }
    
    /////////////////////////////////////////////////
    // 頁面處理
    /////////////////////////////////////////////////
    
    /**
     * 登出功能
     * $url - 登出後前往的網址
     */
    function logout($url = 'index.php') {
        $this->sessionOn();
        session_destroy();
        
        if (isset($_COOKIE)) {
            foreach ($_COOKIE as $name => $value) {
                $name = htmlspecialchars($name);
                setcookie($name , "");
            }
        }

        $this->reUrl($url);
    }
    
    /**
     * 登出功能
     * $url - 登出後前往的網址
     */
    function justLogout() {
        $this->sessionOn();
        session_destroy();
        
        if (isset($_COOKIE)) {
            foreach ($_COOKIE as $name => $value) {
                $name = htmlspecialchars($name);
                setcookie($name , "");
            }
        }
    }
    
    /**
     * 將此頁儲存為登入前瀏覽最後頁面
     */
    function lastPage () {
        $this->sessionOn();
        $_SESSION['lastPage'] = $this->retUri();
    }
    
    /**
     * 轉換頁面
     * $url - 網址
     */
    function reUrl($url) {
        header(sprintf("Location: %s", $url));
    }
    
    /**
     * 限制登入等級
     * $level - 等級的陣列，array(1, 2)
     */
    function loginLevel($level = array()) {
        $this->sessionOn();
        $this->loginNeed();
        if(!in_array(@$_SESSION['level'], $level)) {
            $this->reUrl($this->loginPage);
        }
    }
    
    /**
     * 記住登入頁面
     */
    function rememberPage() {
        $this->sessionOn();
        $_SESSION['rememberPage'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    /**
     * 登入限制頁面
     */
    function loginLimit($url = 'index.php') {
        $this->sessionOn();
        if (isset($_SESSION['login'])) $this->reUrl($url);
    }
    
    /**
     * 限制登入頁面
     */
    function loginNeed($url = '') {
        $this->sessionOn();
        $this->lastPage();
        if ($url == '') $url = $this->loginPage;
        if (!isset($_SESSION['mita_email'])) $this->reUrl($url);
    }
    
    /**
     * JavaScript History Go
     */
    function jsHistory($str, $go) {
        echo $this->meta;
        echo '<script>';
        echo 'alert("'.$str.'");';
        echo 'history.go('.$go.');';
        echo '</script>';
        exit;
    }
    
    /**
     * JavaScript重導頁面
     */
    function jsRedirect($str, $url) {
        echo $this->meta;
        echo '<script>';
        echo 'alert("'.$str.'");';
        echo 'window.location = "'.$url.'";';
        echo '</script>';
        exit;
    }
    
    /////////////////////////////////////////////////
    // 其他功能
    /////////////////////////////////////////////////

    /**
     * 製作 Excel
     * $sql - sql 內容
     * $titles - 標題
     * $fields - 欄位
     * $fileName - 檔名
     **/
    function makeExcel($sql = '', $titles = array(), $fields = array(), $fileName = '', $width = 12) {
        $class = dirname(__FILE__).'/PHPExcel.php';
        if ($fileName == '') $fileName = date('YmdHis').rand(1000, 9999);

        if (file_exists($class)) {
            include_once $class;
            $query = $this->myRow($sql);
            $excel = new PHPExcel();
            $excel->setActiveSheetIndex(0);
            $excel->getActiveSheet()->getDefaultColumnDimension()->setWidth($width);
            $type = PHPExcel_Cell_DataType::TYPE_STRING;

            foreach ($titles as $k => $v) {
                $excel->getActiveSheet()->setCellValueByColumnAndRow($k, 1, $v);
            }

            $rowIndex = 2;
            if ($query) {
                foreach ($query as $row) {
                    foreach ($fields as $k => $v) {
                        $excel->getActiveSheet()->getCellByColumnAndRow($k, $rowIndex)->setValueExplicit($row[$v], $type);
                    }
                    $rowIndex++;
                }
            }

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
            header('Cache-Control: max-age=0');
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $writer->save('php://output');
        } else {
            echo 'class is not exist';
        }
    }

    /**
     * 轉換跳脫字元
     */
    function convertEscape($str) {
        return str_replace('/', '\/', str_replace('"', '\"', $str));
    }
    
    /**
     * 將內文轉為列表
     * $val - 內文
     */
    function br2li($val) {
        echo '<li>';
        echo str_replace('<br>', '</li><li>', nl2br($val));
        echo '</li>';
    }
    
    /**
     * 檢查需求的參數
     * $arr - 將參數使用陣列傳入
     * $url - 網址
     */
    function reqVariable($arr, $url = 'index.php') {
        foreach ($arr as $k => $v) {
            if (!isset($_GET[$v]) || empty($_GET[$v])) {
                $this->jsRedirect('請由正常管道進入', $url);
            }
        }
    }
    
    /**
     * 返回是否確認
     * $com1 - 比較字串1
     * $com2 - 比較字串2
     */
    function isChecked($com1 = '', $com2 = '') {
        $com1 = (string)$com1;
        $com2 = (string)$com2;
        if ($com1 == '' || $com2 == '') {
            return '';
        } else {
            return !strcmp($com1, $com2) ? 'checked="checked"' : '';
        }
    }
    
    /**
     * 返回是否選擇
     * $com1 - 比較字串1
     * $com2 - 比較字串2
     */
    function isSelected($com1 = '', $com2 = '') {
        $com1 = (string)$com1;
        $com2 = (string)$com2;
        if ($com1 == '' || $com2 == '') {
            return '';
        } else {
            return !strcmp($com1, $com2) ? 'selected="selected"' : '';
        }
    }
    
    /**
     * 生成一個暫用的 cookie id
     */
    function tempCookieId($day = '') {
        if ($day == '') {
            $day = 7; // 預設7天
        }
        $time = time()+3600*24*$day;
        if (!isset($_COOKIE['tempId'])) setcookie('tempId', uniqid().rand(10000,99999), $time);
    }
    
    /**
     * DateDiff
     * $interval can be:
     * yyyy - Number of full years
     * q - Number of full quarters
     * m - Number of full months
     * y - Difference between day numbers
     * (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
     * d - Number of full days
     * w - Number of full weekdays
     * ww - Number of full weeks
     * h - Number of full hours
     * n - Number of full minutes
     * s - Number of full seconds (default)
     */
    function dateDiff($interval, $datefrom, $dateto, $using_timestamps = false) {
        if (!$using_timestamps) {
            $datefrom = strtotime($datefrom, 0);
            $dateto = strtotime($dateto, 0);
        }

        $difference = $dateto-$datefrom; // Difference in seconds

        switch($interval) {
            case 'yyyy': // Number of full years    
                $years_difference = floor($difference / 31536000);

            if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
                $years_difference--;
            }

            if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
                $years_difference++;
            }

            $datediff = $years_difference;
            break;

        case "q": // Number of full quarters    
            $quarters_difference = floor($difference / 8035200);

            while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                $months_difference++;
            }

            $quarters_difference--;
            $datediff = $quarters_difference;
            break;

        case "m": // Number of full months
            $months_difference = floor($difference / 2678400);

            while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                $months_difference++;
            }

            $months_difference--;
            $datediff = $months_difference;
            break;

        case 'y': // Difference between day numbers
            $datediff = date("z", $dateto)-date("z", $datefrom);
            break;

        case "d": // Number of full days
            $datediff = floor($difference / 86400);
            break;

        case "w": // Number of full weekdays   
            $days_difference = floor($difference / 86400);
            $weeks_difference = floor($days_difference / 7); // Complete weeks
            $first_day = date("w", $datefrom);
            $days_remainder = floor($days_difference % 7);
            $odd_days = $first_day+$days_remainder; // Do we have a Saturday or Sunday in the remainder?

            if ($odd_days > 7) { // Sunday
                $days_remainder--;
            }

            if ($odd_days > 6) { // Saturday
                $days_remainder--;
            }

            $datediff = ($weeks_difference * 5)+$days_remainder;
            break;

        case "ww": // Number of full weeks
            $datediff = floor($difference / 604800);
            break;   

        case "h": // Number of full hours
            $datediff = floor($difference / 3600);
            break;

        case "n": // Number of full minutes   
            $datediff = floor($difference / 60);
            break;

        default: // Number of full seconds (default)
            $datediff = $difference;
            break;
        }
            return $datediff;
    }
    
    /**
     * 建立目錄
     */
    function makeDir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
        }
    }
    
    /**
     * Email寄送功能
     * $fromEmail - 寄件者Email
     * $fromName - 寄件者title
     * $receiverEmail - 收件者Email
     * $subject - 標題
     * $content - 內容
     */
    function email() {
        $class = dirname(__FILE__).'/class.phpmailer.php';
        if (!file_exists($class)) return 'class is not exist';

        require_once($class);
        $mail = new PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->Encoding = "base64";
        $mail->IsHTML(true);
        $mail->FromName = $this->emailFromName;
        $mail->From = $this->emailFrom;
        $mail->Subject = $this->emailSubject;
        $mail->Body = $this->emailContent;
        $mail->AddAddress($this->emailTo);
        if (!$mail->Send() && $this->emailDebug) {
            return $mail->ErrorInfo;
        } else {
            return true;
        }
    }
    
    /**
     * 擷取日期部份
     */
    function dateOnly($date) {
        return date('Y-m-d', strtotime($date));
    }

    /**
     * 截斷字
     * $f - 字串
     * $l - 長度
     * $symbol - 替代符號
     */
    function cutStr($f, $l, $symbol = '') {
        mb_internal_encoding($this->charset);
        $f = trim(strip_tags($f));
        if (mb_strlen($f) > $l) {
            return mb_substr($f, 0, $l).$symbol;
        } else {
            return $f;
        }
    }

    /**
     * 傳回指定的資料
     */
    function retData($fields = array(), $where = '') {
        $fields = implode(', ', preg_replace('/^(.*?)$/', "`$1`", $fields));
        $sql = sprintf("SELECT %s FROM %s WHERE %s",
            $fields,
            '`'.$this->table.'`',
            $where);
        return $this->myOneRow($sql);
    }

    /**
     * 今天日期
     */
    function retNow() {
        return date('Y-m-d H:i:s');
    }

    /**
     * IP
     */
    function retIp() {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     * 目前網址
     * $url - 連結檔名
     */
    function retUri($url = '') {
        if ($url == '') {
            return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        } else {
            return 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . $url;
        }
        
    }
    
    /**
     * 抓取最大排序
     * $sort - 欄位名稱
     * $where - WHERE 條件
     */
    function retMaxSort($sort, $where = '1 = 1') {
        $sql = sprintf("SELECT MAX(%s) as sortMax FROM %s WHERE %s",
            '`'.$sort.'`',
            $this->table,
            $where);
        $row = $this->myOneRow($sql);
        return (!$row) ? 1 : $row['sortMax']+1;
    }
	
    /**
     * 將資料轉成 smarty option 的格式
     * $data - 資料
     * $value - 值
     * $text - 名稱
     * $name - 第一項標題
     */
    function retSmartyOption($data, $value, $text, $name = '請選擇') {
        $arr = array();

        if ($data) {
            $arr[''] = $name;
            foreach ($data as $v) {
                $arr[$v[$value]] = $v[$text];
            }
        }

        return $arr;
    }
    
    /**
     * 秀出錯誤訊息
     * $msg - 訊息內容
     */
    function showMsg($msg = '') {
        return '<div style="border: 1px solid orange; text-align: center; background: #E1FDE3; padding: 4px; font-size: 14px; margin: 2px;">'.$msg.'</div>';
    }

    /////////////////////////////////////////////////
    // 檔案處理
    /////////////////////////////////////////////////

    /**
     * 檔案上傳
     * $path - 上傳路徑
     * $file - 檔案
     **/
    function fileUpload($path = '', $file = '') {
        $class = dirname(__FILE__).'/class.upload.php'; // class路徑
        $langFile = dirname(__FILE__).'/lang/class.upload.zh_TW.php'; // 繁中語言包
        $lang = (file_exists($langFile)) ? 'zh_TW' : '';
        $err ='';
        $imgName = '';

        if (!file_exists($class)) {
            $err = 'class is not exist'; // 檢查class是否存在
        } else {
            include_once $class;
            $fileName = date('YmdHis').rand(1000, 9999);
            $handle = new upload($_FILES[$file], $lang);
            $handle->file_new_name_body = $fileName;
            $handle->file_max_size = $this->fileUploadSize;
            $handle->process($path);

            if (!$handle->processed) $err = $handle->error;
        }

        return array(
            'err'=> $err, // 錯誤訊息
            'file'=> $handle->file_dst_name, // 新檔名
            'extension' => $handle->file_src_name_ext, // 副檔名
            'originName' => $handle->file_src_name, // 舊檔名
            'path' => $path // 路徑
        );
    }

    /////////////////////////////////////////////////
    // 圖片處理
    /////////////////////////////////////////////////
    
    /**
     * 圖片上傳
     * $path - 上傳路徑
     * $img - 檔案
     **/
    function imgUpload($path = '', $img = '') {
        $class = dirname(__FILE__).'/class.upload.php'; // class路徑
        $langFile = dirname(__FILE__).'/lang/class.upload.zh_TW.php'; // 繁中語言包
        $lang = (file_exists($langFile)) ? 'zh_TW' : '';
        $err ='';
        $imgName = '';

        if (!file_exists($class)) {
            $err = 'class is not exist'; // 檢查class是否存在
        } else {
            include_once $class;
            $imgName = date('YmdHis').rand(1000, 9999);
            $handle = new upload($_FILES[$img], $lang);
            $handle->file_new_name_body = $imgName;
            $handle->file_max_size = $this->imageUploadSize;
            $handle->allowed = $this->imageUploadAllowed;
            $handle->jpeg_quality = 100;
            $handle->image_resize = true;
            $handle->image_x = $this->imageUploadRatio; 
            $handle->image_y = $this->imageUploadRatio;
            $handle->image_ratio = true;
            $handle->image_ratio_no_zoom_in = true;
            $handle->process($path);

            if (!$handle->processed) $err = $handle->error;
        }

        return array(
            'err'=> $err, // 錯誤訊息
            'img'=> $handle->file_dst_name, // 圖片名稱
            'originName' => $handle->file_src_name, // 舊檔名
            'path' => $path // 路徑
        );
    }

    /**
     * 建立驗證碼圖片
     */
    function createCaptcha() {
        $this->sessionOn();
        $imgPath = 'images/captcha/'; // 圖片路徑
        $rand = rand(1000, 9999); // 亂數
        $_SESSION['captcha'] = $rand;
        
        $p1 = imagecreatefromgif($this->captchaSource.substr($rand, 0, 1).'.gif');
        $p2 = imagecreatefromgif($this->captchaSource.substr($rand, 1, 1).'.gif');
        $p3 = imagecreatefromgif($this->captchaSource.substr($rand, 2, 1).'.gif');
        $p4 = imagecreatefromgif($this->captchaSource.substr($rand, 3, 1).'.gif');
        $im = imagecreatetruecolor(100, 30);
        
        imagecopymerge($im, $p1, 0, 0, 0, 0, 25, 30, 100);
        imagecopymerge($im, $p2, 25, 0, 0, 0, 25, 30, 100);
        imagecopymerge($im, $p3, 50, 0, 0, 0, 25, 30, 100);
        imagecopymerge($im, $p4, 75, 0, 0, 0, 25, 30, 100);
        
        imagepng($im);
        
        imagedestroy($p1);
        imagedestroy($p2);
        imagedestroy($p3);
        imagedestroy($p4);
        imagedestroy($im);
    }

    /**
     * 利用 class.upload.php 符合視窗
     * $dir - 目錄
     * $img - 圖片名稱
     * $ratio - 比例
     * $noFile - 不存在時的回應
     */
    function fitThumb($dir, $img, $w = 0, $h = 0, $noFile = '檔案不存在', $nameOnly = false) {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir.'thumbnails/'; // 縮圖路徑
        $class = dirname(__FILE__).'/class.upload.php'; // class路徑
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        
        if (!file_exists($class)) return 'class is not exist'; // 檢查class是否存在
        if (!file_exists($dir.$img) || $img == '') return $noFile; // 檢查原始圖片是否存在
        
        // 縮圖檔名
        $thumbBody = sprintf('%s_%sx%s_fit',
            $body,
            $w,
            $h);

        $thumbName = $thumbDir.$thumbBody.'.'.$ext;
        
        if (file_exists($thumbName)) {
            // 如果縮圖存在直接秀出
            if ($nameOnly) {
                return $thumbName;
            } else {
                list($width, $height) = getimagesize($thumbName);
                return sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $width,
                    $height);
            }
        } else {
            // 處理縮圖
            require_once($class);
            $foo = new upload($dir.$img, 'zh_TW');
            $foo->file_new_name_body = $thumbBody;
            $foo->file_overwrite = true;
            $foo->jpeg_quality = 100;
            $foo->image_resize = true;
            $foo->image_ratio_crop = 'T';
            
            if ($w == 0 && $h != 0) {
                $foo->image_y = $h;
                $foo->image_ratio_x = true;
            } elseif ($w != 0 && $h == 0) {
                $foo->image_x = $w;
                $foo->image_ratio_y = true;
            } else {
                $foo->image_x = $w;
                $foo->image_y = $h;
                $foo->image_ratio = true;
            }
            
            $foo->process($thumbDir);
            
            if ($foo->processed) {
                if ($nameOnly) {
                    return $thumbName;
                } else {
                    return sprintf('<img src="%s" width="%s" height="%s">',
                        $thumbName,
                        $foo->image_dst_x,
                        $foo->image_dst_y);
                }
            } else {
                if ($this->thumbDebug) return $foo->error;
            }
        }
    }
    
    /**
     * 利用 class.upload.php 作正方形縮圖
     * $dir - 目錄
     * $img - 圖片名稱
     * $ratio - 比例
     * $noFile - 不存在時的回應
     */
    function squareThumb($dir, $img, $ratio, $noFile = '檔案不存在', $nameOnly = false) {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir.'thumbnails/'; // 縮圖路徑
        $class = dirname(__FILE__).'/class.upload.php'; // class路徑
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        
        if (!file_exists($class)) return 'class is not exist'; // 檢查class是否存在
        if (!file_exists($dir.$img) || $img == '') return $noFile; // 檢查原始圖片是否存在
        
        // 縮圖檔名
        $thumbBody = sprintf('%s_%s_square',
            $body,
            $ratio);
            
        $thumbName = $thumbDir.$thumbBody.'.'.$ext;
        
        if (file_exists($thumbName)) {
            // 如果縮圖存在直接秀出
            if ($nameOnly) {
                return $thumbName;
            } else {
                return sprintf('<img src="%s">', $thumbName);
            }
        } else {
            // 處理縮圖
            require_once($class);
            $foo = new upload($dir.$img, 'zh_TW');
            $foo->file_new_name_body = $thumbBody;
            $foo->file_overwrite = true;
            $foo->jpeg_quality = 100;
            $foo->image_resize = true;
            $foo->image_x = $ratio;
            $foo->image_y = $ratio;
            $foo->image_ratio_crop = 'T';
            $foo->image_ratio = 'true';			
            $foo->process($thumbDir);
            
            if ($foo->processed) {
                if ($nameOnly) {
                    return $thumbName;
                } else {
                    return sprintf('<img src="%s">', $thumbName);
                }
            } else {
                if ($this->thumbDebug) return $foo->error;
            }
        }
    }
    
    /**
     * 利用 class.upload.php 做縮圖
     * $dir - 目錄
     * $img - 圖片名稱
     * $w - 寬度
     * $h - 高度
     * $noFile - 不存在時的回應
     * $nameOnly - 只回傳名稱
     */
    function thumb($dir, $img, $w = 0, $h = 0, $noFile = '檔案不存在', $nameOnly = false) {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir.'thumbnails/'; // 縮圖路徑
        $class = dirname(__FILE__).'/class.upload.php'; // class路徑
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        
        if (!file_exists($class)) return 'class is not exist'; // 檢查class是否存在
        if (!file_exists($dir.$img) || $img == '') return $noFile; // 檢查原始圖片是否存在
        
        // 縮圖檔名
        $thumbBody = sprintf('%s_%sx%s_thumb',
            $body,
            $w,
            $h);

        $thumbName = $thumbDir.$thumbBody.'.'.$ext;
        
        if (file_exists($thumbName)) {
            // 如果縮圖存在直接秀出
            if ($nameOnly) {
                return $thumbName;
            } else {
                list($width, $height) = getimagesize($thumbName);
                return sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $width,
                    $height);
            }
        } else {
            // 處理縮圖
            require_once($class);
            $foo = new upload($dir.$img, 'zh_TW');
            $foo->file_new_name_body = $thumbBody;
            $foo->file_overwrite = true;
            $foo->jpeg_quality = 100;
            $foo->image_resize = true;
            
            if ($w == 0 && $h != 0) {
                $foo->image_y = $h;
                $foo->image_ratio_x = true;
            } elseif ($w != 0 && $h == 0) {
                $foo->image_x = $w;
                $foo->image_ratio_y = true;
            } else {
                $foo->image_x = $w;
                $foo->image_y = $h;
                $foo->image_ratio = true;
            }
            
            $foo->process($thumbDir);
            
            if ($foo->processed) {
                if ($nameOnly) {
                    return $thumbName;
                } else {
                    return sprintf('<img src="%s" width="%s" height="%s">',
                        $thumbName,
                        $foo->image_dst_x,
                        $foo->image_dst_y);
                }
            } else {
                if ($this->thumbDebug) return $foo->error;
            }
        }
    }
    
    /**
     * 擷取正方縮圖
     * $dir - 原始目錄
     * $img - 圖片名稱
     * $size - 最終的大小
     */
    function centerThumb($dir, $img, $size = 50, $noFile = '檔案不存在', $nameOnly = false) {
        $this->makeDir($dir); // 檢查主資料夾
        $thumbDir = $dir.'thumbnails/'; // 縮圖目錄
        $this->makeDir($thumbDir); // 檢查縮圖資料夾
        $realPosition = $dir.$img;
        $ext = strtolower(end(explode('.', $realPosition))); // 副檔名
        if (!file_exists($realPosition) || $img == '') return $noFile; // 檢查原始圖片是否存在

        $thumbBody = sprintf('%s_%sx%s_center',
            reset(explode('.', $img)),
            $w,
            $h);

        $thumbName = $thumbDir.$thumbBody.'.jpg';
        
        if (file_exists($thumbName)) {
            if ($nameOnly) {
                return $thumbName;
            } else {
                return sprintf('<img src="%s">', $thumbName);
            }
        } else {
            // 不存在則生成縮圖            
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    $src = imagecreatefromjpeg($realPosition);
                    break;
                case 'gif':
                    $src = imagecreatefromgif($realPosition);
                    break;
                case 'png':
                    $src = imagecreatefrompng($realPosition);
                    break;
            }
            
            $srcW = imagesx($src); // 原始寬度
            $srcH = imagesy($src); // 原始高度
            
            if ($srcW >= $srcH) {
                // 以高來等比例縮第一次圖
                $newW = intval($srcW / $srcH * $size); // 新寬度
                $newH = $size; // 新高度
            } else {
                // 以寬來等比例縮第一次圖
                $newW = $size; // 新寬度
                $newH = intval($srcH / $srcW * $size); // 新高度
            }
            
            // 縮第一次圖
            $im = imagecreatetruecolor($newW, $newH);
            imagealphablending($im, false); 
            imagesavealpha($im, true); 
            imagecopyresampled($im, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
            
            // 縮需求大小的圖
            $im2 = imagecreatetruecolor($size, $size);
            imagealphablending($im2, false); 
            imagesavealpha($im2, true); 
            $coordX = ($newW-$size)/2;
            $coordY = ($newH-$size)/2;

            imagecopyresampled($im2, $im, 0, 0, $coordX, $coordY, $newW, $newH, $newW, $newH);
            
             //輸出
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($im2, $thumbDir.$newName, 100);
                    break;
                case 'gif':
                    imagegif($im2, $thumbDir.$newName, 100);
                    break;
                case 'png':
                    imagepng($im2, $thumbDir.$newName);
                    break;
            }
            
            imagedestroy($im);
            imagedestroy($im2);

            return '<img src="'.$thumbDir.$newName.'" />';
            return $noFile;
        }
    }
    
    /**
     * 一般縮圖
     * $dir - 原始目錄
     * $file - 檔案名稱
     * $reW - 寬
     * $reH - 高
     * $noFile - 檔案不存在的文案
     */
    function showThumbnail($dir, $file, $reW = 0, $reH = 0, $noFile = '檔案不存在') {
        $realPosition = $dir.$file; // 圖片加路徑
        $thumbDir = $dir.'thumbnails/'; // 縮圖目錄
        
        $this->makeDir($dir); // 檢查主資料夾
        $this->makeDir($thumbDir); // 檢查縮圖資料夾

        if (file_exists($realPosition) && $file != '') {
            $fileName = current(explode('.', $file)); // 檔名
            $ext = strtolower(end(explode('.', $realPosition))); // 副檔名
            $newName = $fileName.'_'.$reW.'x'.$reH.'.'.$ext; //新檔名
            
            // 如果該縮圖存在則直接出圖
            if (file_exists($thumbDir.$newName)) {
                return '<img src="'.$thumbDir.$newName.'" />';
            }
            
            // 不存在則生成縮圖            
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    $src = imagecreatefromjpeg($realPosition);
                    break;
                case 'gif':
                    $src = imagecreatefromgif($realPosition);
                    break;
                case 'png':
                    $src = imagecreatefrompng($realPosition);
                    break;
            }
            
            $srcW = imagesx($src); // 原始寬度
            $srcH = imagesy($src); // 原始高度
            
            if ($reH == 0) {
                // 強制以寬等比例縮放
                if ($srcW > $reW) {
                    $newW = $reW;
                    $newH = intval($srcH/$srcW*$reW);
                } else {
                    $newW = $srcW;
                    $newH = $srcH;
                }
            } elseif ($reW == 0) {
                // 強制以高等比例縮放
                if ($srcH > $reH) {
                    $newH = $reH;
                    $newW = intval(($srcW/$srcH)*$reH);
                } else {
                    $newW = $srcW;
                    $newH = $srcH;
                }
            } else {
                // 偵測寬或高等比例縮放
                if ($srcW > $srcH) {
                    if ($srcW > $reW) {
                        $newW = $reW;
                        $newH = intval($srcH/$srcW*$reW);
                     } else {
                        $newW = $srcW;
                        $newH = $srcH;
                     }
                } else {
                    if ($srcH > $reH) {
                        $newH = $reH;
                        $newW = intval(($srcW/$srcH)*$reH);
                    } else {
                        $newW = $srcW;
                        $newH = $srcH;
                    }
                }
            }
            
            $im = imagecreatetruecolor($newW, $newH);
            imagealphablending($im, false); 
            imagesavealpha($im, true); 
            imagecopyresampled($im, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
            
             //輸出
            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($im, $thumbDir.$newName, 100);
                    break;
                case 'gif':
                    imagegif($im, $thumbDir.$newName, 100);
                    break;
                case 'png':
                    imagepng($im, $thumbDir.$newName);
                    break;
            }
            
            imagedestroy($im);
            
            return '<img src="'.$thumbDir.$newName.'" />';
        } else {
            return $noFile;
        }
    }	
    
    /**
     * 登入區
     */
    function showLoginArea() {
        $this->sessionOn();

        if (isset($_SESSION['IS_LOGIN'])) {
            return 'login_area';
        } else {
            // check login by cookis
            if (isset($_COOKIE['loginName'])) {
                $sqlMem = sprintf("SELECT * FROM tbl_account WHERE email = %s AND passwd = %s",
                   $this->toSql($_COOKIE['loginName'], 'text'),
                   $this->toSql($_COOKIE['loginPassword'], 'text'));
                $rowMem = $this->myOneRow($sqlMem);

                if ($rowMem) {
                    $_SESSION['IS_LOGIN'] = "login_area";
                    foreach ($rowMem as $k => $mem) {
                        $_SESSION["member_".$k] = $mem;
                    }

                    return 'login_area';
                } 
            }
            return 'notlogin';
        }
    }
    
    /**
     * 隨機密碼
     * randomPwd(10);
     */
    function randomPwd($length = 8)
    {
        $result = "";
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        for ($p = 0; $p < $length; $p++)
        {
            $result .= $chars[mt_rand(0, 35)];
        }
        
        return $result;
    }
}
?>
