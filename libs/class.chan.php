<?php
class chan {
    // Database variable
    public $charset          = 'UTF-8';
    public $host             = '';
    public $db               = '';
    public $username         = '';
    public $password         = '';
    public $conn             = '';
    public $makeRecordCount  = true;
    public $recordCount      = 0;
    public $totalRecordCount = 0;
    public $lastInsertId     = 0;
    public $fieldArray       = array();
    public $valueArray       = array();
    public $sqlErrorMessage  = '';
    public $table            = '';
    public $pk               = '`id`';
    public $pkValue          = '';

    // Email variable
    public $emailDebug    = false;
    public $emailFrom     = '';
    public $emailTo       = '';
    public $emailFromName = '';
    public $emailSubject  = '';
    public $emailContent  = '';

    // Default variable
    public $meta            = '<meta http-equiv = "Content-Type" content = "text/html; charset = utf-8" />';
    public $thumbDebug      = false;
    public $loginPage       = 'login.php';
    public $fileDeleteArray = array();

    // Server validate variable
    public $captchaSource   = 'images/captcha/';
    public $validateArray   = array();
    public $validateMessage = '';
    public $validateError   = false;

    // Data variable
    public $page       = 0;
    public $totalPages = 0;

    // Image variable
    public $imageUploadRatio   = 1000;
    public $imageUploadAllowed = array('image/*');
    public $imageUploadSize    = 2097152;

    // File variable
    public $fileUploadAllowed = array('image/*, application/*, archives/zip');
    public $fileUploadSize    = 5242880;

    // Language variable
    private $_langPrevPage = '上一頁';
    private $_langFirstPage = '第一頁';
    private $_langNextPage = '下一頁';
    private $_langLastPage = '最後頁';
    private $_langInput = '請填寫';
    private $_langDuplicate = '重複';
    private $_langFormatInvalid = '格式錯誤';
    private $_langOverLength = '超過字數';
    private $_langUrlError = '連結方式錯誤';
    private $_langSelect = '請選擇';
    private $_langFileNotExist = '檔案不存在';

    public function __construct() {
        $this->host = DB_HOST;
        $this->db = DB_DB;
        $this->username = DB_USERNAME;
        $this->password = DB_PASSWORD;
    }

    /**
     * Start Session
     */
    function sessionOn() {
        if (!isset($_SESSION)) session_start();
    }

    /**
     * Execute sql
     * @param string $sql SQL statement
     */
    function sqlExecute($sql) {
        mysql_select_db($this->db, $this->conn);

        if (!mysql_query($sql, $this->conn)) {
            $this->sqlErrorMessage = mysql_error();
            return false;
        } else {
            $this->lastInsertId = mysql_insert_id();
        }

        return true;
    }

    /**
     * Connect to database
     */
    function connect() {
        $this->conn = mysql_connect($this->host, $this->username, $this->password) or trigger_error(mysql_error(), E_USER_ERROR);
        mysql_query("SET NAMES 'utf8'");
    }

    /**
     * Prevent Sql Injection
     *
     * @param string $theValue Value
     * @param string $type Sql Type
     * @param string $theDefinedValue value self defined
     * @param string $theNotDefinedValue value if not self defined
     * @return mixed
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
     * Table field
     * @param mixed $field filed
     * @param mixed $value filed value
     * @param string $type field type
     */
    function addField($field, $value, $type = 'text') {
        $this->fieldArray[] = '`' . $field . '`';
        $this->valueArray[] = $this->toSql($value, $type);
    }

    /**
     * Get file name from database
     *
     * @param string $field field name
     * @return string
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
     * Delte file from database
     * @param string $path file path
     **/
    function dataFileDelete($path) {
        if (count($this->fileDeleteArray) > 0) {
            if (is_dir($path)) {
                foreach ($this->fileDeleteArray as $fileName) {
                    @unlink($path . $fileName);
                    $fileDelHead = explode('.', $fileName);
                    $thumbDir = $path . '/thumbnails/';
                    $handle = @opendir($thumbDir);

                    while($file = readdir($handle)){
                        if('.' !== $file && '..' !== $file){
                            $fileDel = explode('_', $file);

                            if ($fileDelHead[0] === $fileDel[0]) {
                                unlink($thumbDir . $file);
                            }
                        }
                    }

                    closedir($handle);
                }
            }
        }
    }

    /**
     * Insert data
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
     * Update data
     *
     * @param string $where defined where condition
     */
    function dataUpdate($where = NULL) {
        $sqlString = array();

        foreach ($this->fieldArray as $k => $v) {
            $sqlString[] = $v . ' = ' . $this->valueArray[$k];
        }

        if (NULL === $where) {
            $where = sprintf("%s = %s",
                $this->pk,
                $this->toSql($this->pkValue, 'int'));
        }

        $sqlUpdate = sprintf("UPDATE %s SET %s WHERE %s",
            $this->table,
            implode(', ', $sqlString),
            $where);

        $this->clearFields();
        return $this->sqlExecute($sqlUpdate);
    }

    /**
     * Delete data
     *
     * @param string $where defined where condition
     */
    function dataDelete($where = NULL) {
        if (NULL === $where) {
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
     * Clear fields
     */
    function clearFields() {
        $this->pk = '';
        $this->pkValue = '';
        unset($this->fieldArray);
        unset($this->valueArray);
    }

    /**
     * Check source url
     */
     function checkSourceUrl() {
         if (false === stripos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) {
            die('Not the same domain');
         }
     }

    /**
     * Add validate field
     * @param string $name validate name
     * @param string $field validate filed name
     * @param string $type validate type (text, email, number, positive, boolean, file, duplicate)
     * @param string $tableField if type equal to duplicate need to type table name
     * @param integer $limit string max length
     * @param string $method form method
     */
    function addValidateField($name, $field, $type = 'text', $tableField = '', $limit = 0, $method = 'POST') {
        array_push($this->validateArray,
            array(
                'name'       => $name,
                'field'      => $field,
                'type'       => $type,
                'tableField' => '`' . $tableField . '`',
                'limit'      => $limit,
                'method'     => $method)
            );
    }

    /**
     * Server validate
     *
     * @return result
     */
    function serverValidate() {
        $emailPattern = '/^\w+[\w\+\.\-]*@\w+(?:[\.\-]\w+)*\.\w+$/i';
        $numberPattern = '/[0-9]/';
        $positvePattern = '/^\d+$/';
        $booleanPattern = '/^\d{0,1}+$/';

        foreach ($this->validateArray as $v) {
            $value = ($v['type'] == 'file') ? @$_FILES[$v['field']]['name'] : (($v['method'] == 'POST') ? @$_POST[$v['field']] : @$_GET[$v['field']]);
            $name = $v['name'];
            $type = $v['type'];
            $tableField = $v['tableField'];
            $limit = $v['limit'];

            if ('' === trim($value)) {
                // Check if empty value
                $this->validateMessage .= $this->_langInput . $name . '<br>';
                $this->validateError = true;
            } else {
                switch ($type) {
                    case 'email': $pattern = $emailPattern; break;
                    case 'number': $pattern = $numberPattern; break;
                    case 'positive': $pattern = $positvePattern; break;
                    case 'boolean': $pattern = $booleanPattern; break;
                    case 'duplicate':
                        if ('' === $this->pkValue) {
                            // Check if duplicate
                            $sql = sprintf("SELECT * FROM `%s` WHERE %s = %s",
                                $this->table,
                                $tableField,
                                $this->toSql($value, 'text'));
                        } else {
                            $sql = sprintf("SELECT * FROM `%s` WHERE `%s` = %s AND %s != %s",
                                $this->table,
                                $tableField,
                                $this->toSql($value, 'text'),
                                $this->pk,
                                $this->toSql($this->pkValue, 'int'));
                        } 

                        $row = $this->myOneRow($sql);

                        if ($row) {
                            $this->validateMessage .= $name . $this->_langDuplicate . '<br>';
                            $this->validateError = true;
                        }

                        break;
                    default: $pattern = '';
                }

                if (!empty($pattern) && !preg_match($pattern, $value)) {
                    $this->validateMessage .= $name . $this->_langFormatInvalid . '<br>';
                    $this->validateError = true;
                } else {
                    if ($limit > 0 && mb_strlen($value, $this->charset) > $limit) {
                        $this->validateMessage .= $name . $this->_langOverLength . '<br>';
                        $this->validateError = true;
                    }
                }
            }
        }

        return $this->validateError;
    }

    /**
     * Show validate error message
     */
    function showValidateMessage() {
        if (true === $this->validateError) {
            echo $this->meta;
            echo $this->validateMessage;
            exit;
        }
    }

    /**
     * Get one data
     *
     * @param string $sql sql statement
     * @return data|NULL
     */
    function myOneRow($sql) {
        mysql_select_db($this->db, $this->conn);
        $rec = mysql_query($sql, $this->conn) or die(mysql_error());
        $row = mysql_fetch_assoc($rec);
        $num = mysql_num_rows($rec);
        $count = mysql_num_fields($rec);
        $result = array();

        if ($num > 0) {
            $temp = array();

            for ($i = 0; $i < $count; $i++) {
                $name = mysql_field_name($rec, $i);
                $result[$name] = $row[$name];
            }
        } else {
            $result = NULL;
        }

        mysql_free_result($rec);
        return $result;
    }

    /**
     * Get data
     *
     * @param string $sql sql statement
     * @return data|NULL
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
            $result = array();
            $temp = array();

            do {
                for ($i = 0; $i < $count; $i++) {
                    $name = mysql_field_name($rec, $i);
                    $temp[$name] = $row[$name];
                }

                array_push($result, $temp);
            } while($row = mysql_fetch_assoc($rec));
        } else {
            $result = NULL;
        }

        mysql_free_result($rec);
        return $result;
    }

    /**
     * Get data by limit
     *
     * @param string $sql sql statement
     * @param integer $max data per page
     * @return data
     */
    function myRowList($sql, $max = 10) {
        $this->page = isset($_GET['page']) ? $_GET['page'] : 0;
        $startRow = $this->page * $max;
        $row = $this->myRow($sql);

        if (NULL === $row) {
            return NULL;
        }

        $this->totalRecordCount = count($row);
        $this->totalPages = ceil($this->totalRecordCount / $max) - 1;
        $sqlPages = sprintf("%s LIMIT %d, %d", $sql, $startRow, $max);
        $this->makeRecordCount = false;
        $row = $this->myRow($sqlPages);
        return $row;
    }

    /**
     * Combine url param
     *
     * @param string $string combine string
     * @return string
     */
    function combineQueryString($string) {
        $result = '';

        if (!empty($_SERVER['QUERY_STRING'])) {
            $params = explode('&', $_SERVER['QUERY_STRING']);
            $newParams = array(); 

            foreach ($params as $param) {
               if (!stristr($param, $string)) {
                   array_push($newParams, $param);
               }
            }

            if (0 !== count($newParams)) {
                $result = '&' . htmlentities(implode('&', $newParams));
            }
        }

        return $result;
    }

    /**
     * Default pager
     *
     * @param integer $limit data per page
     * @return string
     */
    function pager($limit = 5) {
        $sep = '&nbsp;';
        $result = '';
        $result .= $this->pageString('prev', NULL, 'prev') . $sep;
        $result .= $this->pageNumber($limit) . $sep;
        $result .= $this->pageString('next', NULL, 'next') . $sep;
        return $result;
    }

    /**
     * Bootstrap pager
     *
     * @param integer $limit data per page
     * @return string
     */
    function bootstrapPager($limit = 6) {
        $currentPage = $_SERVER["PHP_SELF"];
        $result = '';
        $result .= '<ul class="pagination">';
        $limitLinksEndCount = $limit;
        $temp = intval(($this->page + 1));
        $startLink = intval((max(1, $temp - intval($limitLinksEndCount / 2))));
        $temp = intval(($startLink + $limitLinksEndCount - 1));
        $endLink = min($temp, $this->totalPages + 1);

        // Prev page
        if ($this->page > 0) {
            $result .= sprintf('<li><a href="%s?page=%d%s">«</a></li>',
                $currentPage,
                max(0, intval($this->page - 1)),
                $this->combineQueryString('page'));
        } else {
            $result .= sprintf('<li class="disabled"><a>«</a></li>',
                $currentPage,
                max(0, intval($this->page - 1)),
                $this->combineQueryString('page'));
        }

        if ($endLink !== $temp) $startLink = max(1, intval(($endLink-$limitLinksEndCount + 1)));

        for ($i = $startLink; $i <= $endLink; $i++) {
            $limitPageEndCount = $i - 1;
            if ($this->page !== $limitPageEndCount) {
                $result .= sprintf('<li><a href="%s?page=%d%s">%s</a></li>',
                $currentPage,
                $limitPageEndCount,
                $this->combineQueryString('page'),
                $i);
            } else {
                $result .= '<li class="disabled"><a>' . $i . '</a></li>';
            }
        }

        // Next page
        if ($this->page < $this->totalPages) {
            $result .= sprintf('<li><a href="%s?page=%d%s">»</a></li>',
                $currentPage,
                min($this->totalPages, intval($this->page + 1)),
                $this->combineQueryString('page'));
        } else {
            $result .= sprintf('<li class="disabled"><a>»</a></li>',
                $currentPage,
                min($this->totalPages, intval($this->page + 1)),
                $this->combineQueryString('page'));
        }

        $result .= "</ul>";

        return $result;
    }

    /**
     * Prev or nex page
     *
     * @param string $method prev or next
     * @param string $string display word
     * @param string $class css class name
     * @return string
     */
    function pageString($method, $string = NULL, $class = '') {
        $currentPage = $_SERVER["PHP_SELF"];
        $result = '';

        switch ($method) {
            case 'first':
                if ($this->page > 0) {
                    if (NULL === $string) {
                        $string = $this->_langFirstPage;
                    }
                    $result = '<a href="' . sprintf("%s?page=%d%s",
                        $currentPage,
                        0,
                        $this->combineQueryString('page')) . '" class="' . $class . '">' . $string . '</a>';
                }

                break;
            case 'prev':
                if ($this->page > 0) {
                    if (NULL === $string) {
                        $string = $this->_langPrevPage;
                    }
                    $result = '<a href="' . sprintf("%s?page=%d%s",
                        $currentPage,
                        max(0, $this->page - 1),
                        $this->combineQueryString('page')) . '" class="' . $class . '">' . $string . '</a>';
                }

                break;
            case 'next':
                if ($this->page < $this->totalPages) {
                    if (NULL === $string) {
                        $string = $this->_langNextPage;
                    }

                    $result = '<a href="' . sprintf("%s?page=%d%s",
                        $currentPage,
                        min($this->totalPages, $this->page + 1),
                        $this->combineQueryString('page')) . '" class="' . $class . '">' . $string . '</a>';
                }
                break;
            case 'last':
                if ($this->page < $this->totalPages) {
                    if (NULL === $string) {
                        $string = $this->_langLastPage;
                    }

                    $result = '<a href="' . sprintf("%s?page=%d%s",
                        $currentPage,
                        $this->totalPages,
                        $this->combineQueryString('page')) . '" class="' . $class . '">' . $string . '</a>';
                }
                break;
        }

        return $result;
    }

    /**
     * Page number
     *
     * @param integer $limit data per page
     * @param string $set seperation
     * @return string
     */
    function pageNumber($limit = 5, $sep = '&nbsp;') {
        $result = '';
        $currentPage = $_SERVER["PHP_SELF"];
        $limitLinksEndCount = $limit;
        $temp = intval($this->page + 1);
        $startLink = max(1, $temp - intval($limitLinksEndCount / 2));
        $temp = intval($startLink + $limitLinksEndCount - 1);
        $endLink = min($temp, $this->totalPages + 1);

        if ($endLink !== $temp) {
            $startLink = max(1, $endLink - $limitLinksEndCount + 1);
        }

        for ($i = $startLink; $i <= $endLink; $i++) {
          $limitPageEndCount = intval($i - 1);

          if ($limitPageEndCount !== $this->page) {
            $result .= sprintf('<a href="' . "%s?page=%d%s", $currentPage, $limitPageEndCount, $this->combineQueryString('page') . '">');
            $result .= $i . '</a>';
          } else {
            $result .= '<strong>' . $i. '</strong>';
          }

          if ($i !== $endLink) {
              $result .= $sep;
          }
        }

        return $result;
    }

    /**
     * Logout
     *
     * @param string url redirect page when logout
     */
    function logout($url = 'index.php') {
        $this->sessionOn();
        session_destroy();

        if (isset($_COOKIE)) {
            foreach ($_COOKIE as $name => $value) {
                $name = htmlspecialchars($name);
                setcookie($name , '');
            }
        }

        $this->reUrl($url);
    }

    /**
     * Save page to session as last visied page
     */
    function lastPage () {
        $this->sessionOn();
        $_SESSION['lastPage'] = $this->retUri();
    }

    /**
     * Redirect
     *
     * @param string $url redirect url
     */
    function reUrl($url) {
        header(sprintf('Location: %s', $url));
    }

    /**
     * Login level limitation
     *
     * @param array $level level (array(1, 2, ...))
     */
    function loginLevel($level = array()) {
        $this->sessionOn();
        $this->loginNeed();

        if(!in_array(@$_SESSION['level'], $level)) {
            $this->reUrl($this->loginPage);
        }
    }

    /**
     * Not login check
     * only allow user who is not login
     *
     * @param string $url redirect page
     */
    function loginLimit($url = 'index.php') {
        $this->sessionOn();

        if (isset($_SESSION['login'])) {
            $this->reUrl($url);
        }
    }

    /**
     * Login check
     * only allow user who is login
     *
     * @param string $url login page
     */
    function loginNeed($url = NULL) {
        $this->sessionOn();
        $this->lastPage();

        if (NULL === $url) {
            $url = $this->loginPage;
        }

        if (!isset($_SESSION['login'])) {
            $this->reUrl($url);
        }
    }

    /**
     * Redirect by JavaScript
     *
     * @param string $string alert string
     * @param string $url redirect url
     */
    function jsRedirect($string = NULL, $url = NULL) {
        echo $this->meta;
        echo '<script>';
        echo 'alert("' . $string . '");';
        echo 'window.location = "' . $url . '";';
        echo '</script>';
        exit;
    }

    /**
     * Export data as excel
     *
     * @param string $sql sql statement
     * @param array $titles title
     * @param array $fields field name
     * @param string $fileName file name
     * @param integer $width default excel column width
     **/
    function makeExcel($sql = '', $titles = array(), $fields = array(), $fileName = NULL, $width = 12) {
        $class = dirname(__FILE__) . '/PHPExcel.php';
        if (NULL === $fileName) {
            $fileName = date('YmdHis') . rand(1000, 9999);
        }

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
            die('Excel class is not exist');
        }
    }

    /**
     * Convert escapte string
     *
     * @param string $string string need to be convert
     * @return string
     */
    function convertEscape($string) {
        return str_replace('/', '\/', str_replace('"', '\"', $string));
    }

    /**
     * Required variable check
     *
     * @param array $variables required variables
     * @param string $url redirect url
     */
    function reqVariable($variable = NULL, $url = 'index.php') {
        if ('array' === gettype($variable)) {
            foreach ($variables as $value) {
                if (!isset($_GET[$value]) || empty($_GET[$value])) {
                    $this->jsRedirect($this->_langUrlError, $url);
                    break;
                }
            }
        } else {
            if (!isset($_GET[$variable]) || empty($_GET[$variable])) {
                $this->jsRedirect($this->_langUrlError, $url);
                break;
            }
        }
    }

    /**
     * Temporary cookie id
     *
     * @param integer $day cookie exist day
     */
    function tempCookieId($day = 7) {
        $time = time() + 3600 * 24 * $day;

        if (!isset($_COOKIE['tempId'])) {
            setcookie('tempId', uniqid() . rand(10000, 99999), $time);
        }
    }

    /**
     * DateDiff
     *
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
     * Build directory
     *
     * @param string $directory directory name
     */
    function makeDir($directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0777);
        }
    }

    /**
     * Send email
     */
    function email() {
        $class = dirname(__FILE__) . '/class.phpmailer.php';
        if (!file_exists($class)) {
            return 'Email class is not exist';
        }

        include_once($class);
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
     * Cut date part
     *
     * @param string $date date string
     * @return string
     */
    function dateOnly($date) {
        return date('Y-m-d', strtotime($date));
    }

    /**
     * Cut string
     *
     * @param string $string string
     * @param integer $length string length
     * @param string $symbol replace string
     */
    function cutStr($string, $length, $symbol = '...') {
        mb_internal_encoding($this->charset);
        $string = trim(strip_tags($string));

        if (mb_strlen($string) > $length) {
            return mb_substr($string, 0, $length) . $symbol;
        } else {
            return $string;
        }
    }

    /**
     * Get data with definded fields
     *
     * @param array $fields fields
     * @param string $where where condition
     * @return data
     */
    function retData($fields = array(), $where = '') {
        $fields = implode(', ', preg_replace('/^(.*?)$/', "`$1`", $fields));
        $sql = sprintf("SELECT %s FROM %s WHERE %s",
            $fields,
            '`' . $this->table . '`',
            $where);

        return $this->myOneRow($sql);
    }

    /**
     * Now
     *
     * @return string
     */
    function retNow() {
        return date('Y-m-d H:i:s');
    }

    /**
     * IP
     *
     * @return string
     */
    function retIp() {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Current Uri
     *
     * @param string $url combine url if assigned
     * @return string
     */
    function retUri($url = NULL) {
        if (NULL === $url) {
            return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            return 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . $url;
        }

    }

    /**
     * Get max sort
     *
     * @param string $sort field name
     * @param string $where where condition
     * @return integer
     */
    function retMaxSort($sort, $where = '1 = 1') {
        $sql = sprintf("SELECT MAX(%s) as `maxSort` FROM %s WHERE %s",
            '`' . $sort . '`',
            $this->table,
            $where);
        $row = $this->myOneRow($sql);
        return (NULL === $row) ? 1 : intval($row['maxSort'] + 1);
    }

    /**
     * Convert data to smarty option format
     * $data - 資料
     * $value - 值
     * $text - 名稱
     * $name - 第一項標題
     */
    function retSmartyOption($data = NULL, $value, $text, $select = NULL) {
        $result = array();

        if (NULL !== $data) {
            if (NULL === $select) {
                $select = $this->_langSelect;
            }

            $result[''] = $select;

            foreach ($data as $v) {
                $result[$v[$value]] = $v[$text];
            }
        }

        return $result;
    }

    /**
     * Show message
     *
     * @param string $message message to be show
     */
    function showMsg($message = NULL) {
        if (NULL !== $message) {
            echo '<div style="border: 1px solid orange; text-align: center; background: #E1FDE3; padding: 4px; font-size: 14px; margin: 2px;">' . $message . '</div>';
        }
    }

    /**
     * File upload
     *
     * @param string $path path
     * @param string $file file name
     * @return array
     **/
    function fileUpload($path = '/', $file = '') {
        $class = dirname(__FILE__) . '/class.upload.php';
        $langFile = dirname(__FILE__) . '/lang/class.upload.zh_TW.php';
        $lang = file_exists($langFile) ? 'zh_TW' : '';
        $error ='';
        $imgName = '';

        if (!file_exists($class)) {
            $error = 'Upload class is not exist';
        } else {
            include_once $class;
            $fileName = date('YmdHis') . rand(1000, 9999);
            $handle = new upload($_FILES[$file], $lang);
            $handle->file_new_name_body = $fileName;
            $handle->file_max_size = $this->fileUploadSize;
            $handle->process($path);

            if (!$handle->processed) {
                $error = $handle->error;
            }
        }

        return array(
            'err'        => $error,
            'file'       => $handle->file_dst_name,
            'extension'  => $handle->file_src_name_ext,
            'originName' => $handle->file_src_name,
            'path'       => $path
        );
    }

    /**
     * Image Upload
     *
     * @param string $path path
     * @param string $file file name
     * @return array
     **/
    function imageUpload($path = '/', $img = '') {
        $class = dirname(__FILE__) . '/class.upload.php';
        $langFile = dirname(__FILE__) . '/lang/class.upload.zh_TW.php';
        $lang = file_exists($langFile) ? 'zh_TW' : '';
        $error ='';
        $imgName = '';

        if (!file_exists($class)) {
            $error = 'Upload class is not exist';
        } else {
            include_once $class;
            $imgName = date('YmdHis') . rand(1000, 9999);
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

            if (!$handle->processed) {
                $error = $handle->error;
            }
        }

        return array(
            'err'        => $error,
            'img'        => $handle->file_dst_name,
            'originName' => $handle->file_src_name,
            'path'       => $path
        );
    }

    /**
     * Make fit thumbnail
     *
     * @param string $dir directory
     * @param string $img image name
     * @param integer $width image width
     * @param integer $height image height
     * @param string $noFile message when file not exiest
     * @param string $nameOnly return string when true
     * @return mixed
     */
    function fitThumb($dir, $img, $width = 0, $height = 0, $noFile = '', $nameOnly = false) {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir . 'thumbnails/';
        $class = dirname(__FILE__) . '/class.upload.php';
        $langFile = dirname(__FILE__) . '/lang/class.upload.zh_TW.php';
        $lang = file_exists($langFile) ? 'zh_TW' : '';
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        $thumbBody = sprintf('%s_%sx%s_fit',
            $body,
            $width,
            $height);
        $thumbName = $thumbDir . $thumbBody . '.' . $ext;

        if ('' === $noFile) {
            $noFile = $this->_langFileNotExist;
        }

        if (!file_exists($class)) {
            die('Image class is not exist');
        }

        if (!file_exists($dir . $img) || '' === $img) {
            return $noFile;
        }

        if (file_exists($thumbName)) {
            if (true === $nameOnly) {
                return $thumbName;
            } else {
                list($width, $height) = getimagesize($thumbName);
                return sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $width,
                    $height);
            }
        } else {
            include_once($class);
            $foo = new upload($dir . $img, $lang);
            $foo->file_new_name_body = $thumbBody;
            $foo->file_overwrite = true;
            $foo->jpeg_quality = 100;
            $foo->image_resize = true;
            $foo->image_ratio_crop = 'T';

            if (0 === $width && 0 !== $height) {
                $foo->image_y = $height;
                $foo->image_ratio_x = true;
            } elseif (0 !== $width && 0 === $height) {
                $foo->image_x = $width;
                $foo->image_ratio_y = true;
            } else {
                $foo->image_x = $width;
                $foo->image_y = $height;
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
                if ($this->thumbDebug) {
                    return $foo->error;
                }
            }
        }
    }

    /**
     * Make square thumbnail
     *
     * @param string $dir directory
     * @param string $img image name
     * @param integer $ratio image ratio
     * @param string $noFile message when file not exiest
     * @param string $nameOnly return string when true
     * @return mixed
     */
    function squareThumb($dir, $img, $ratio = 150, $noFile = '', $nameOnly = false) {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir . 'thumbnails/';
        $class = dirname(__FILE__) . '/class.upload.php';
        $langFile = dirname(__FILE__) . '/lang/class.upload.zh_TW.php';
        $lang = file_exists($langFile) ? 'zh_TW' : '';
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        $thumbBody = sprintf('%s_%s_square',
            $body,
            $ratio);
        $thumbName = $thumbDir . $thumbBody . '.' . $ext;

        if ('' === $noFile) {
            $noFile = $this->_langFileNotExist;
        }

        if (!file_exists($class)) {
            die('Image class is not exist');
        }

        if (!file_exists($dir . $img) || '' === $img) {
            return $noFile;
        }

        if (file_exists($thumbName)) {
            if ($nameOnly) {
                return $thumbName;
            } else {
                return sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $ratio,
                    $ratio);
            }
        } else {
            include_once($class);
            $foo = new upload($dir . $img, $lang);
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
                    return sprintf('<img src="%s" width="%s" height="%s">',
                        $thumbName,
                        $ratio,
                        $ratio);
                }
            } else {
                if ($this->thumbDebug) {
                    return $foo->error;
                }
            }
        }
    }

    /**
     * Make thubnail
     *
     * @param string $dir directory
     * @param string $img image name
     * @param integer $width image width
     * @param integer $height image height
     * @param string $noFile message when file not exiest
     * @param string $nameOnly return string when true
     * @return mixed
     */
    function thumb($dir, $img, $width = 0, $height = 0, $noFile = '', $nameOnly = false) {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir . 'thumbnails/';
        $class = dirname(__FILE__) . '/class.upload.php';
        $langFile = dirname(__FILE__) . '/lang/class.upload.zh_TW.php';
        $lang = file_exists($langFile) ? 'zh_TW' : '';
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        $thumbBody = sprintf('%s_%sx%s_thumb',
            $body,
            $width,
            $height);
        $thumbName = $thumbDir . $thumbBody . '.' . $ext;

        if ('' === $noFile) {
            $noFile = $this->_langFileNotExist;
        }

        if (!file_exists($class)) {
            die('Image class is not exist');
        }

        if (!file_exists($dir . $img) || '' === $img) {
            return $noFile;
        }

        if (file_exists($thumbName)) {
            if (true === $nameOnly) {
                return $thumbName;
            } else {
                list($width, $height) = getimagesize($thumbName);
                return sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $width,
                    $height);
            }
        } else {
            include_once($class);
            $foo = new upload($dir . $img, $lang);
            $foo->file_new_name_body = $thumbBody;
            $foo->file_overwrite = true;
            $foo->jpeg_quality = 100;
            $foo->image_resize = true;

            if (0 === $width && 0 !== $height) {
                $foo->image_y = $height;
                $foo->image_ratio_x = true;
            } elseif (0 !== $width && 0 === $height) {
                $foo->image_x = $width;
                $foo->image_ratio_y = true;
            } else {
                $foo->image_x = $width;
                $foo->image_y = $height;
                $foo->image_ratio = true;
            }

            $foo->process($thumbDir);

            if ($foo->processed) {
                if (true === $nameOnly) {
                    return $thumbName;
                } else {
                    return sprintf('<img src="%s" width="%s" height="%s">',
                        $thumbName,
                        $foo->image_dst_x,
                        $foo->image_dst_y);
                }
            } else {
                if ($this->thumbDebug) {
                    return $foo->error;
                }
            }
        }
    }

    /**
     * Random password
     *
     * @param integer length
     * @return string
     */
    function randomPwd($length = 8)
    {
        $result = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[mt_rand(0, 35)];
        }

        return $result;
    }
}
