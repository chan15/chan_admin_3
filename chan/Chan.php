<?php namespace Chan;

use PDO;
use upload;
use PHPExcel;

class Chan
{
    // Database variable
    public $charset          = 'UTF-8';
    public $host             = '';
    public $db               = '';
    public $username         = '';
    public $password         = '';
    public $makeRecordCount  = true;
    public $recordCount      = 0;
    public $totalRecordCount = 0;
    public $fieldArray       = array();
    public $valueArray       = array();
    public $sqlErrorMessage  = '';
    public $table            = '';
    public $pk               = '';
    public $pkValue          = '';
    private $dbh             = null;
    private $dbhRead         = null;
    private $dbhWrite        = null;
    private $_paramType = array(
        'bool' => PDO::PARAM_BOOL,
        'null' => PDO::PARAM_NULL,
        'int'  => PDO::PARAM_INT,
        'str'  => PDO::PARAM_STR,
        'date' => PDO::PARAM_STR,
    );

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
    public $imageLang          = 'zh_TW';

    // File variable
    public $fileUploadAllowed = array('image/*, application/*, archives/zip');
    public $fileUploadSize    = 5242880;
    public $fileMimeCheck     = true;

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

    public function __construct($mode = 'single')
    {
        // Open connection
        $config = include dirname(__DIR__) . '/config/database.php';
        $config = $config[$mode];
        $dsnRead = null;
        $dsnWrite = null;
        $dsn = null;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        if (is_array($config['host']) === true) {
            $dsnRead = 'mysql:host=' . $config['host']['read'] . ';dbname=' . $config['database'] . ';charset=utf8';
            $dsnWrite = 'mysql:host=' . $config['host']['write'] . ';dbname=' . $config['database'] . ';charset=utf8';
        } else {
            $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['database'] . ';charset=utf8';
        }

        try {
            if ($dsn === null) {
                $this->dbhRead = new PDO($dsnRead, $config['username'], $config['password'], $options);
                $this->dbhWrite = new PDO($dsnWrite, $config['username'], $config['password'], $options);
            } else {
                $this->dbh = new PDO($dsn, $config['username'], $config['password'], $options);
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function __destruct()
    {
        // Close connection
        $this->dbh = null;
        $this->dbhRead = null;
        $this->dbhWrite = null;
    }

    /**
     * Start Session
     */
    public function sessionOn()
    {
        if (false === isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * PDO begin transaction
     *
     * @return PDO object
     */
    public function beginTransaction()
    {
        if (null !== $this->dbh) {
            // Single database
            return $this->dbh->beginTransaction();
        } else {
            // Double database
            return $this->dbhWrite->beginTransaction();
        }
    }

    /**
     * PDO commit transaction
     *
     * @return PDO object
     */
    public function commitTransaction()
    {
        if (null !== $this->dbh) {
            // Single database
            return $this->dbh->commit();
        } else {
            // Double database
            return $this->dbhWrite->commit();
        }
    }

    /**
     * PDO rollback transaction
     *
     * @return PDO object
     */
    public function rollBackTransaction()
    {
        if (null !== $this->dbh) {
            // Single database
            return $this->dbh->rollBack();
        } else {
            // Double database
            return $this->dbhWrite->rollBack();
        }
    }

    /**
     * Execute sql
     * @param string $sql SQL statement
     *
     * @return boolean
     */
    public function sqlExecute($sql = null)
    {
        $result = $this->prepare($sql);

        if (false === $result->execute()) {
            $errorMessage = $this->dbh->errorInfo();
            $this->sqlErrorMessage = $errorMessage[2];

            return false;
        }

        $this->clearFields();

        return true;
    }

    /**
     * Last insert id
     *
     * @return integer
     */
    public function lastInsertId()
    {
        if (null === $this->dbh) {
            return $this->dbhWrite->lastInsertId();
        } else {
            return $this->dbh->lastInsertId();
        }
    }

    /**
     * Add table field
     *
     * @param mixed $field field
     * @param mixed $value field value
     * @param string $type field type
     */
    public function addField($field, $value, $type = 'str')
    {
        $this->fieldArray[] = '`' . $field . '`';
        $this->valueArray[] = array('type' => $type, 'value' => $value);
    }

    /**
     * Bind PDO value
     *
     * $param string $value
     * $param string $type (bool|null|int|str)
     * @return void
     */
    public function addValue($value, $type = 'str')
    {
        $this->valueArray[] = array('type' => $type, 'value' => $value);
    }

    /**
     * Get file name from database
     *
     * @param string $field field name
     * @return string
     **/
    public function getFileName($field)
    {
        $sql = sprintf("SELECT `%s` FROM `%s` WHERE `%s` = %s",
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
    public function dataFileDelete($path)
    {
        if (count($this->fileDeleteArray) > 0) {
            if (is_dir($path)) {
                foreach ($this->fileDeleteArray as $fileName) {
                    @unlink($path . $fileName);
                    $fileDelHead = explode('.', $fileName);
                    $thumbDir = $path . '/thumbnails/';
                    $handle = @opendir($thumbDir);

                    while ($file = readdir($handle)) {
                        if ('.' !== $file && '..' !== $file) {
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
     *
     * @return boolean
     */
    public function dataInsert()
    {
        $sql = sprintf("INSERT INTO `%s` (%s) VALUES(%s)",
            $this->table,
            implode(', ', $this->fieldArray),
            implode(', ', array_fill(0, count($this->fieldArray), '?')));
        $result = $this->prepare($sql);

        if (count($this->valueArray) > 0) {
            $index = 1;

            foreach ($this->valueArray as $item) {
                $result->bindValue($index, $item['value'], $this->_paramType[$item['type']]);
                $index++;
            }
        }

        $this->clearFields();

        if (false === $result->execute()) {
            $errorMessage = $result->errorInfo();
            die($errorMessage[2]);
        }

        return true;
    }

    /**
     * Update data
     *
     * @param string $where defined where condition
     * @return boolean
     */
    public function dataUpdate($where = null)
    {
        $sqlString = array();
        $index = 1;

        foreach ($this->fieldArray as $k => $v) {
            $sqlString[] = $v . ' = ?';
        }

        if (null === $where) {
            $condition = sprintf("`%s` = ?",
                $this->pk);
        } else {
            $condition = $where;
        }

        $sql = sprintf("UPDATE `%s` SET %s WHERE %s",
            $this->table,
            implode(', ', $sqlString),
            $condition);
        $result = $this->prepare($sql);

        if (count($this->valueArray) > 0) {
            foreach ($this->valueArray as $item) {
                $result->bindValue($index, $item['value'], $this->_paramType[$item['type']]);
                $index++;
            }
        }

        if (null === $where) {
            $result->bindValue($index, $this->pkValue, $this->_paramType['int']);
        }

        $this->pk = '';
        $this->pkValue = '';
        $this->clearFields();

        if (false === $result->execute()) {
            $errorMessage = $result->errorInfo();
            die($errorMessage[2]);
        }

        return true;
    }

    /**
     * Insert or update data
     *
     * @param string $where defined where condiion
     * @return boolean
     */
    public function save($where = null)
    {
        if ('' === $this->pkValue) {
            return $this->dataInsert();
        } else {
            return $this->dataUpdate($where);
        }
    }

    /**
     * Delete data
     *
     * @param string $where defined where condition
     * @return boolean
     */
    public function delete($where = null)
    {
        $index = 1;

        if (null === $where) {
            $sql = sprintf("DELETE FROM `%s` WHERE `%s` = ?",
                $this->table,
                $this->pk);
        } else {
            $sql = sprintf("DELETE FROM `%s` WHERE %s",
                $this->table,
                $where);
        }

        $result = $this->prepare($sql);

        if (count($this->valueArray) > 0) {
            foreach ($this->valueArray as $item) {
                $result->bindValue($index, $item['value'], $this->_paramType[$item['type']]);
                $index++;
            }
        }

        if (null === $where) {
            $result->bindValue($index, $this->pkValue, $this->_paramType['int']);
        }

        $this->clearFields();

        if (false === $result->execute()) {
            $errorMessage = $result->errorInfo();
            die($errorMessage[2]);
        }

        return true;
    }

    /**
     * Clear fields
     */
    public function clearFields()
    {
        $this->fieldArray = array();
        $this->valueArray = array();
    }

    /**
     * Check source url
     */
    public function checkSourceUrl()
    {
         if (false === stripos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) {
            die('Not the same domain');
         }
     }

    /**
     * Add validate field
     * @param string $name validate name
     * @param string $field validate field name
     * @param string $type validate type (text, email, number, positive, boolean, file, duplicate)
     * @param string $tableField if type equal to duplicate need to type table name
     * @param integer $limit string max length
     * @param string $method form method
     */
    public function addValidateField($name, $field, $type = 'text', $tableField = '', $limit = 0, $method = 'post')
    {
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
    public function serverValidate()
    {
        $emailPattern = '/^\w+[\w\+\.\-]*@\w+(?:[\.\-]\w+)*\.\w+$/i';
        $numberPattern = '/[0-9]/';
        $positvePattern = '/^\d+$/';
        $booleanPattern = '/^\d{0,1}+$/';

        foreach ($this->validateArray as $v) {
            $value = ($v['type'] === 'file') ? $_FILES[$v['field']]['name'] : (($v['method'] === 'post') ? $_POST[$v['field']] : $_GET[$v['field']]);
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
                            $sql = sprintf("SELECT * FROM `%s` WHERE %s = ?",
                                $this->table,
                                $tableField);
                            $this->addValue($value);
                        } else {
                            $sql = sprintf("SELECT * FROM `%s` WHERE %s = ? AND `%s` != ?",
                                $this->table,
                                $tableField,
                                $this->pk);
                            $this->addValue($value);
                            $this->addValue($this->pkValue, 'int');
                        }

                        $row = $this->myRow($sql);

                        if (null !== $row) {
                            $this->validateMessage .= $name . $this->_langDuplicate . '<br>';
                            $this->validateError = true;
                        }

                        break;
                    default:
                        $pattern = '';
                        break;
                }

                if (false === empty($pattern) && !preg_match($pattern, $value)) {
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
    public function showValidateMessage()
    {
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
    public function myOneRow($sql)
    {
        $result = $this->myRow($sql);

        if (null !== $result) {
            $result = current($result);
        }

        return $result;
    }

    /**
     * PDO prepare
     *
     * @param string $sql
     * @return PDO object
     */
    public function prepare($sql)
    {
        $result = null;

        if (null !== $this->dbh) {
            // Single database
            $result = $this->dbh->prepare($sql);
        } else {
            // Double Database
            if (preg_match('/^select /i', $sql) > 0) {
                $result = $this->dbhRead->prepare($sql);
            } else {
                $result = $this->dbhWrite->prepare($sql);
            }
        }

        return $result;
    }

    /**
     * Get data
     *
     * @param string $sql sql statement
     * @return data|null
     */
    public function myRow($sql = null)
    {
        $result = $this->prepare($sql);
        $index = 1;

        if (count($this->valueArray) > 0) {
            foreach ($this->valueArray as $item) {
                $result->bindValue($index, $item['value'], $this->_paramType[$item['type']]);
                $index++;
            }
        }

        if (false === $result->execute()) {
            $errorMessage = $result->errorInfo();
            die($errorMessage[2]);
        }

        $results = $result->fetchAll(PDO::FETCH_ASSOC);
        $this->recordCount = count($results);

        if (true === $this->makeRecordCount) {
            $this->totalRecordCount = $this->recordCount;
        }

        $this->clearFields();

        if (0 === $this->recordCount) {
            return null;
        }

        return $results;
    }

    /**
     * Get data by limit
     *
     * @param string $sql sql statement
     * @param integer $max data per page
     * @return data
     */
    public function myRowList($sql, $max = 10)
    {
        $this->page = isset($_GET['page']) ? intval($_GET['page']) : 0;
        $startRow = $this->page * $max;
        $tempValue = $this->valueArray;
        $row = $this->myRow($sql);

        if (null === $row) {
            return null;
        }

        $this->totalRecordCount = count($row);
        $this->totalPages = ceil($this->totalRecordCount / $max) - 1;
        $this->valueArray = $tempValue;
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
    public function combineQueryString($string)
    {
        $result = '';

        if (false === empty($_SERVER['QUERY_STRING'])) {
            $params = explode('&', $_SERVER['QUERY_STRING']);
            $newParams = array();

            foreach ($params as $param) {
               if (false === stristr($param, $string)) {
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
    public function pager($limit = 5)
    {
        $sep = '&nbsp;';
        $result = '';
        $result .= $this->pageString('prev', null, 'prev') . $sep;
        $result .= $this->pageNumber($limit) . $sep;
        $result .= $this->pageString('next', null, 'next') . $sep;

        return $result;
    }

    /**
     * Bootstrap pager
     *
     * @param integer $limit data per page
     * @return string
     */
    public function bootstrapPager($limit = 6)
    {
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

        if ($endLink !== $temp) {
            $startLink = max(1, intval(($endLink-$limitLinksEndCount + 1)));
        }

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
    public function pageString($method, $string = null, $class = '')
    {
        $currentPage = $_SERVER["PHP_SELF"];
        $result = '';

        switch ($method) {
            case 'first':
                if ($this->page > 0) {
                    if (null === $string) {
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
                    if (null === $string) {
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
                    if (null === $string) {
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
                    if (null === $string) {
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
    public function pageNumber($limit = 5, $sep = '&nbsp;')
    {
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
                $result .= '<strong>' . $i . '</strong>';
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
    public function logout($url = 'index.php')
    {
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
    public function lastPage ()
    {
        $this->sessionOn();
        $_SESSION['lastPage'] = $this->retUri();
    }

    /**
     * Redirect
     *
     * @param string $url redirect url
     */
    public function reUrl($url)
    {
        header(sprintf('Location: %s', $url));
    }

    /**
     * Login level limitation
     *
     * @param array $level level (array(1, 2, ...))
     */
    public function loginLevel($level = array())
    {
        $this->sessionOn();
        $this->loginNeed();

        if (false === in_array(@$_SESSION['level'], $level)) {
            $this->reUrl($this->loginPage);
        }
    }

    /**
     * Not login check
     * only allow user who is not login
     *
     * @param string $url redirect page
     */
    public function loginLimit($url = 'index.php')
    {
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
    public function loginNeed($url = null)
    {
        $this->sessionOn();
        $this->lastPage();

        if (null === $url) {
            $url = $this->loginPage;
        }

        if (false === isset($_SESSION['login'])) {
            $this->reUrl($url);
        }
    }

    /**
     * Redirect by JavaScript
     *
     * @param string $string alert string
     * @param string $url redirect url
     */
    public function jsRedirect($string = null, $url = null)
    {
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
    public function makeExcel($sql = '', $titles = array(), $fields = array(), $fileName = null, $width = 12)
    {
        if (null === $fileName) {
            $fileName = date('YmdHis') . rand(1000, 9999);
        }

        $result = $this->myRow($sql);
        $excel = new PHPExcel;
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->getDefaultColumnDimension()->setWidth($width);
        $type = PHPExcel_Cell_DataType::TYPE_STRING;

        foreach ($titles as $k => $v) {
            $excel->getActiveSheet()->setCellValueByColumnAndRow($k, 1, $v);
        }

        $rowIndex = 2;

        if (null !== $result) {
            foreach ($result as $row) {
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
    }

    /**
     * Convert escapte string
     *
     * @param string $string string need to be convert
     * @return string
     */
    public function convertEscape($string)
    {
        return str_replace('/', '\/', str_replace('"', '\"', $string));
    }

    /**
     * Required variable check
     *
     * @param array $variables required variables
     * @param string $url redirect url
     */
    public function reqVariable($variable = null, $url = 'index.php')
    {
        if ('array' === gettype($variable)) {
            foreach ($variables as $value) {
                if (false === isset($_GET[$value]) || empty($_GET[$value])) {
                    $this->jsRedirect($this->_langUrlError, $url);
                    break;
                }
            }
        } else {
            if (false === isset($_GET[$variable]) || empty($_GET[$variable])) {
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
    public function tempCookieId($day = 7)
    {
        $time = time() + 3600 * 24 * $day;

        if (false === isset($_COOKIE['tempId'])) {
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
    public function dateDiff($interval, $datefrom, $dateto, $using_timestamps = false)
    {
        if (false === $using_timestamps) {
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
    public function makeDir($directory)
    {
        if (false === is_dir($directory)) {
            mkdir($directory, 0777);
        }
    }

    /**
     * Send email
     */
    public function sendMail()
    {
        $transport = Swift_MailTransport::newInstance();
        $mailer = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance($this->emailSubject)
          ->setFrom(array($this->emailFrom => $this->emailFromName))
          ->setTo($this->emailTo)
          ->setBody($this->emailContent);
        $result = $mailer->send($message);

        return $result;
    }

    /**
     * Cut date part
     *
     * @param string $date date string
     * @return string
     */
    public function dateOnly($date)
    {
        return date('Y-m-d', strtotime($date));
    }

    /**
     * Cut string
     *
     * @param string $string string
     * @param integer $length string length
     * @param string $symbol replace string
     */
    public function cutStr($string, $length, $symbol = '...')
    {
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
    public function retData($fields = array(), $where = '')
    {
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
    public function retNow()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * IP
     *
     * @return string
     */
    public function retIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Current uri
     *
     * @param string $url combine url if assigned
     * @return string
     */
    public function retUri($url = null)
    {
        if (null === $url) {
            return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            return $this->retUriPath() . $url;
        }
    }

    /**
     * Full url directory path
     *
     * @return string
     */
    public function retUriPath()
    {
        $path = pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);

        if ('/' === $path) {
            $path = '';
        }

        return 'http://' . $_SERVER['HTTP_HOST'] . $path . '/';
    }

    /**
     * Get max sort
     *
     * @param string $sort field name
     * @param string $where where condition
     * @return integer
     */
    public function retMaxSort($sort, $where = '1 = 1')
    {
        $sql = sprintf('SELECT MAX(`%s`) as `maxSort` FROM `%s` WHERE %s',
            $sort,
            $this->table,
            $where
        );

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (false === $row) ? 1 : intval($row['maxSort'] + 1);

    }

    /**
     * Convert data to smarty option format
     * $data - 資料
     * $value - 值
     * $text - 名稱
     * $name - 第一項標題
     */
    public function retSmartyOption($data = null, $value, $text, $select = null)
    {
        $result = array();

        if (null !== $data) {
            if (null === $select) {
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
    public function showMsg($message = null)
    {
        if (null !== $message) {
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
    public function fileUpload($path = '/', $file = '')
    {
        $error = '';
        $imgName = '';
        $fileName = date('YmdHis') . rand(1000, 9999);
        $handle = new upload($_FILES[$file], $this->imageLang);
        $handle->file_new_name_body = $fileName;
        $handle->file_max_size = $this->fileUploadSize;
        $handle->mime_check = $this->fileMimeCheck;
        $handle->allowed = $this->fileUploadAllowed;
        $handle->process($path);

        if (false === $handle->processed) {
            $error = $handle->error;
        }

        $handle->clean();

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
    public function imageUpload($path = '/', $img = '')
    {
        $error = '';
        $imgName = '';
        $imgName = date('YmdHis') . rand(1000, 9999);
        $handle = new upload($_FILES[$img], $this->imageLang);
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

        if (false === $handle->processed) {
            $error = $handle->error;
        }

        $handle->clean();

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
    public function fitThumb($dir, $img, $width = 0, $height = 0, $noFile = '', $nameOnly = false)
    {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir . 'thumbnails/';
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        $thumbBody = sprintf('%s_%sx%s_fit',
            $body,
            $width,
            $height);
        $thumbName = $thumbDir . $thumbBody . '.' . $ext;
        $result = null;

        if ('' === $noFile) {
            $noFile = $this->_langFileNotExist;
        }

        if (false === file_exists($dir . $img) || '' === $img) {
            return $noFile;
        }

        if (true === file_exists($thumbName)) {
            if (true === $nameOnly) {
                $result = $thumbName;
            } else {
                list($width, $height) = getimagesize($thumbName);
                $result = sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $width,
                    $height);
            }
        } else {
            $foo = new upload($dir . $img, $this->imageLang);
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

            if (true === $foo->processed) {
                if (true === $nameOnly) {
                    $result = $thumbName;
                } else {
                    $result = sprintf('<img src="%s" width="%s" height="%s">',
                        $thumbName,
                        $foo->image_dst_x,
                        $foo->image_dst_y);
                }
            } else {
                if (true === $this->thumbDebug) {
                    $result = $foo->error;
                }
            }
        }

        return $result;
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
    public function squareThumb($dir, $img, $ratio = 150, $noFile = '', $nameOnly = false)
    {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir . 'thumbnails/';
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        $thumbBody = sprintf('%s_%s_square',
            $body,
            $ratio);
        $thumbName = $thumbDir . $thumbBody . '.' . $ext;
        $result = null;

        if ('' === $noFile) {
            $noFile = $this->_langFileNotExist;
        }

        if (false === file_exists($dir . $img) || '' === $img) {
            return $noFile;
        }

        if (true === file_exists($thumbName)) {
            if (true === $nameOnly) {
                $result = $thumbName;
            } else {
                $result = sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $ratio,
                    $ratio);
            }
        } else {
            $foo = new upload($dir . $img, $this->imageLang);
            $foo->file_new_name_body = $thumbBody;
            $foo->file_overwrite = true;
            $foo->jpeg_quality = 100;
            $foo->image_resize = true;
            $foo->image_x = $ratio;
            $foo->image_y = $ratio;
            $foo->image_ratio_crop = 'T';
            $foo->image_ratio = 'true';
            $foo->process($thumbDir);

            if (true === $foo->processed) {
                if (true === $nameOnly) {
                    $result = $thumbName;
                } else {
                    $result = sprintf('<img src="%s" width="%s" height="%s">',
                        $thumbName,
                        $ratio,
                        $ratio);
                }
            } else {
                if (true === $this->thumbDebug) {
                    $result = $foo->error;
                }
            }
        }

        return $result;
    }

    /**
     * Make thumbnail
     *
     * @param string $dir directory
     * @param string $img image name
     * @param integer $width image width
     * @param integer $height image height
     * @param string $noFile message when file not exiest
     * @param string $nameOnly return string when true
     * @return mixed
     */
    public function thumb($dir, $img, $width = 0, $height = 0, $noFile = '', $nameOnly = false)
    {
        $dir = str_replace(' ', '' , $dir);
        $thumbDir = $dir . 'thumbnails/';
        $body = pathinfo($img, PATHINFO_FILENAME);
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        $thumbBody = sprintf('%s_%sx%s_thumb',
            $body,
            $width,
            $height);
        $thumbName = $thumbDir . $thumbBody . '.' . $ext;
        $result = null;

        if ('' === $noFile) {
            $noFile = $this->_langFileNotExist;
        }

        if (false === file_exists($dir . $img) || '' === $img) {
            return $noFile;
        }

        if (true === file_exists($thumbName)) {
            if (true === $nameOnly) {
                $result = $thumbName;
            } else {
                list($width, $height) = getimagesize($thumbName);
                $result = sprintf('<img src="%s" width="%s" height="%s">',
                    $thumbName,
                    $width,
                    $height);
            }
        } else {
            $foo = new upload($dir . $img, $this->imageLang);
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

            if (true === $foo->processed) {
                if (true === $nameOnly) {
                    $result = $thumbName;
                } else {
                    $result = sprintf('<img src="%s" width="%s" height="%s">',
                        $thumbName,
                        $foo->image_dst_x,
                        $foo->image_dst_y);
                }
            } else {
                if (true === $this->thumbDebug) {
                    $result = $foo->error;
                }
            }
        }

        return $result;
    }

    /**
     * Random password
     *
     * @param integer length
     * @return string
     */
    public function randomPwd($length = 8)
    {
        $result = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[mt_rand(0, 35)];
        }

        return $result;
    }

    /**
     * Get content by cURL
     *
     * @param string $soruce source
     * @param string $type (post|get)
     * @return string
     */
    public function curl($source = null, $type = 'get', $fields = array())
    {
        $result = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $source);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ('post' === strtolower($type)) {
            curl_setopt($ch, CURLOPT_POST, true);

            if (0 !== count($fields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            }
        }

        $result = curl_exec($ch);

        if (false === $result) {
            $result = curl_error($ch);
        }

        curl_close($ch);

        return $result;
    }

    /**
     * Split field as individual array
     *
     * @param array $items array item
     * @param array $fieldName field name
     * @return array
     */
    public function lists($items, $fieldName)
    {
        $result = array_map(function ($item) use ($fieldName) {
            return $item[$fieldName];
        }, $items);

        return $result;
    }

    /**
     * Force download file
     *
     * @param string $fileName file name you want
     * @param string $path absolute path of the file
     */
    public function download($fileName, $path)
    {
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: Binary');
        header('Content-disposition: attachment; filename="' . $fileName . '"');
        echo file_get_contents($path);
    }
}
