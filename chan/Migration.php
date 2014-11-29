<?php namespace Chan;

class Migration extends \Chan\Chan
{
    // Migration variable
    private $_columns = array();
    private $_columnName = '';
    private $_indexes = array();
    public $column = '';
    public $migrated = true;
    public $migrationName = '';
    public $timestamp = false;
    public $engine = 'innoDB';

    /**
     * Build increment column
     *
     * @param string $name column name
     */
    public function increments($name)
    {
        $this->_columnName = $name;
        $this->_columns[$name] = sprintf("`%s` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT",
            $name);

        return $this;
    }

    /**
     * Build string column
     *
     * @param string $name column name
     * @param integer $length length
     */
    public function string($name, $length = 255)
    {
        if ('' !== $this->column) {
            $length = (0 === intval($name)) ? 255 : intval($name);
            $this->column .= sprintf(" VARCHAR(%s) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL",
                intval($length));
        } else {
            $this->_columnName = $name;
            $this->_columns[$name] = sprintf("`%s` VARCHAR(%s) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL",
                $name,
                $length);
        }

        return $this;
    }

    /**
     * Build text column
     *
     * @param string $name column name
     */
    public function text($name = null)
    {
        if ('' !== $this->column) {
            $this->column .= " TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL";
        } else {
            $this->_columnName = $name;
            $this->_columns[$name] = sprintf("`%s` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL",
                $name);
        }

        return $this;
    }

    /**
     * Build text column
     *
     * @param string $name column name
     * @param array $value value
     */
    public function enum($name, $value = array())
    {
        if ('' !== $this->column) {
            $this->column .= sprintf(" ENUM(%s) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL",
                "'" . implode("','", $name) . "'");
        } else {
            $this->_columnName = $name;
            $this->_columns[$name] = sprintf("`%s` ENUM(%s) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL",
                $name,
                "'" . implode("','", $value) . "'");
        }

        return $this;
    }

    /**
     * Build integer column
     *
     * @param string $name column name
     * @param integer $length length
     */
    public function integer($name, $length = 0)
    {
        if ('' !== $this->column) {
            if (0 === intval($name)) {
                $this->column .= ' INT UNSIGNED NOT NULL';
            } else {
                $this->column .= sprintf(" INT(%s) UNSIGNED NOT NULL",
                    intval($name));
            }
        } else {
            $this->_columnName = $name;

            if (0 === intval($length)) {
                $this->_columns[$name] = sprintf("`%s` INT UNSIGNED NOT NULL",
                    $name);
            } else {
                $this->_columns[$name] = sprintf("`%s` INT(%s) UNSIGNED NOT NULL",
                    $name,
                    $length);
            }
        }

        return $this;
    }

    /**
     * Build tiny integer column
     *
     * @param string $name column name
     * @param integer $length length
     */
    public function tinyint($name, $length = 0)
    {
        if ('' !== $this->column) {
            if (0 === intval($name)) {
                $this->column .= " TINYINT UNSIGNED NOT NULL";
            } else {
                $this->column .= sprintf(" TINYINT(%s) UNSIGNED NOT NULL",
                    intval($name));
            }
        } else {
            $this->_columnName = $name;

            if (0 === intval($length)) {
                $this->_columns[$name] = sprintf("`%s` TINYINT UNSIGNED NOT NULL",
                    $name);
            } else {
                $this->_columns[$name] = sprintf("`%s` TINYINT(%s) UNSIGNED NOT NULL",
                    $name,
                    $length);
            }
        }

        return $this;
    }

    /**
     * Build boolean column
     *
     * @param string $name column name
     */
    public function boolean($name)
    {
        if ('' !== $this->column) {
            $this->column .= ' TINYINT(1) UNSIGNED NOT NULL';
        } else {
            $this->_columnName = $name;
            $this->_columns[$name] = sprintf("`%s` TINYINT(1) UNSIGNED NOT NULL",
                $name);
        }

        return $this;
    }

    /**
     * Build datetime column
     *
     * @param string $name column name
     */
    public function datetime($name)
    {
        if ('' !== $this->column) {
            $this->column .= ' DATETIME NOT NULL';
        } else {
            $this->_columnName = $name;
            $this->_columns[$name] = sprintf("`%s` DATETIME NOT NULL",
                $name);
        }

        return $this;
    }

    /**
     * Build date column
     *
     * @param string $name field name
     */
    public function date($name)
    {
        if ('' !== $this->column) {
            $this->column .= ' DATE NOT NULL';
        } else {
            $this->_columnName = $name;
            $this->_columns[$name] = sprintf("`%s` DATE NOT NULL",
                $name);
        }

        return $this;
    }

    /**
     * Build timestamp column
     *
     * @param string $name field name
     */
    public function timestamp($name)
    {
        if ('' !== $this->column) {
            $this->column .= ' TIMESTAMP NOT NULL';
        } else {
            $this->_columnName = $name;
            $this->_columns[$name] = sprintf("`%s` TIMESTAMP NOT NULL",
                $name);
        }

        return $this;
    }

    /**
     * Index column
     *
     * @param string $name field name
     */
    public function index($name)
    {
        $this->_indexes['index_' . $name] = sprintf("INDEX(`%s`)",
            $name);
    }

    /**
     * Make column signed
     *
     */
    public function signed()
    {
        if ('' !== $this->column) {
            $this->column = str_replace('UNSIGNED', 'SIGNED', $this->column);
        } else {
            $this->_columns[$this->_columnName] = str_replace('UNSIGNED', 'SIGNED', $this->_columns[$this->_columnName]);
        }

        return $this;
    }

    /**
     * Make column null
     *
     */
    public function nullable()
    {
        if ('' !== $this->column) {
            $this->column = str_replace('NOT NULL', 'NULL', $this->column);
        } else {
            $this->_columns[$this->_columnName] = str_replace('NOT NULL', 'NULL', $this->_columns[$this->_columnName]);
        }

        return $this;
    }

    /**
     * Set default value
     *
     * @param string $default default value
     */
    public function defaultValue($default = null)
    {
        if (null !== $default) {
            if ('' !== $this->column) {
                $this->column .= " DEFAULT '" . $default . "'";
            } else {
                $this->_columns[$this->_columnName] = $this->_columns[$this->_columnName] . " DEFAULT '" . $default . "'";
            }
        }

        return $this;
    }

    /**
     * Modify after
     *
     * @param string name column name
     */
    public function after($name)
    {
        $this->column .= sprintf(" AFTER `%s`",
            $name);

        return $this;
    }

    /**
     * Drop column
     *
     * @param string name column name
     */
    public function dropColumn($name)
    {
        $this->column = sprintf("ALTER TABLE `%s` DROP `%s`",
            $this->table,
            $name);
    }

    /**
     * Index column
     *
     * @param string name column name
     */
    public function indexColumn($name)
    {
        $this->column = sprintf("ALTER TABLE `%s` ADD INDEX(`%s`)",
            $this->table,
            $name);
    }

    /**
     * Add column
     *
     * @param string name column name
     */
    public function addColumn($name)
    {
        $this->column = sprintf("ALTER TABLE `%s` ADD `%s`",
            $this->table,
            $name);

        return $this;
    }

    /**
     * Change column
     *
     * @param string name column name
     */
    public function changeColumn($name, $newName)
    {
        $this->column = sprintf("ALTER TABLE `%s` CHANGE `%s` `%s`",
            $this->table,
            $name,
            $newName);

        return $this;
    }

    /**
     * Drop table
     *
     */
    public function dropTable()
    {
        $this->column = sprintf("DROP TABLE `%s`",
            $this->table);
    }

    /**
     * Rename table
     *
     * @param string $name table name
     */

    public function renameTable($name)
    {
        $this->column = sprintf("RENAME TABLE `%s` TO `%s`",
            $this->table,
            $name);
    }

    /**
     * Migrate
     *
     */
    public function migrate()
    {
        if (0 !== count($this->_columns)) {
            // Creating table
            if (true === $this->timestamp) {
                $this->_columns['created_at'] = '`created_at` TIMESTAMP NULL DEFAULT NULL';
                $this->_columns['updated_at'] = '`updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP';
            }

            $result = 'CREATE TABLE `' . $this->table . '` (';
            $result .= implode(',', array_merge($this->_columns, $this->_indexes));
            $result .= ') ENGINE=' . $this->engine . ' CHARACTER SET utf8 COLLATE = utf8_unicode_ci;';

            if (true === $this->sqlExecute($result)) {
                echo $this->table . ' created<br>';
                $this->migrated = true;
                $this->saveToMigarations();
            } else {
                echo 'create ' . $this->table . ' error<br>';
                $this->migrated = false;
            }
        } else {
            if ('' === $this->column) {
                // Executing sql
                $this->saveToMigarations();
            } else {
                // Executing alter table
                if (true === $this->sqlExecute($this->column)) {
                    echo $this->migrationName .  ' finished<br>';
                    $this->migrated = true;
                    $this->saveToMigarations();
                } else {
                    echo $this->migrationName . ' error<br>';
                    $this->migrated = false;
                }
            }
        }

        $this->table = '';
        $this->column = '';
        $this->migrationName = '';
        $this->timestamp = false;
        $this->_columns = array();
        $this->_columnName = '';
        $this->_indexes = array();
    }

    /**
     * Check if migrations exist
     *
     * @return mix
     */
    public function checkMigrations()
    {
        if ('' === $this->migrationName) {
            $sql = "DESCRIBE `migrations`";
            $result = $this->sqlExecute($sql);

            if (false === $result) {
                $this->table = 'migrations';
                $this->increments('id');
                $this->string('name');
                $this->datetime('created_at');
                $this->migrationName = 'create_migrations_table';
                $this->migrate();
            }
        } else {
            $sql = 'SELECT * FROM `migrations` WHERE `name` = ?';
            $this->addValue($this->migrationName);

            return $this->myRow($sql);
        }
    }

    /**
     * Save name to migratsion table
     *
     */
    public function saveToMigarations()
    {
        $this->table = 'migrations';
        $this->addField('name', $this->migrationName);
        $this->addField('created_at', $this->retNow());
        $this->save();
    }
}
