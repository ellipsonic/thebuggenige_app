<?php

    namespace b2db;

    /**
     * Row class
     *
     * @author Daniel Andre Eikeland <zegenie@zegeniestudios.net>
     * @version 2.0
     * @license http://www.opensource.org/licenses/mozilla1.1.php Mozilla Public License 1.1 (MPL 1.1)
     * @package b2db
     * @subpackage core
     */

    /**
     * Row class
     *
     * @package b2db
     * @subpackage core
     */
    class Row implements \ArrayAccess
    {

        protected $_fields = array();

        /**
         * Statement
         *
         * @var Statement
         */
        protected $_statement = null;

        protected $id_col = null;

        /**
         * Constructor
         *
         * @param Statement $statement
         */
        public function __construct($row, $statement)
        {
            foreach ($row as $key => $val) {
                $this->_fields[$key] = $val;
            }
            $this->_statement = $statement;
        }

        public function getJoinedTables()
        {
            return $this->_statement->getCriteria()->getForeignTables();
        }

        protected function _getColumnName($column, $foreign_key = null)
        {
            if ($foreign_key !== null) {
                foreach ($this->_statement->getCriteria()->getForeignTables() as $aft) {
                    if ($aft['original_column'] == $foreign_key) {
                        $column = $aft['jointable']->getB2DBAlias() . '.' . $this->_statement->getCriteria()->getColumnName($column);
                        break;
                    }
                }
            } else
                $column = $this->_statement->getCriteria()->getSelectionColumn($column);

            return $column;
        }

        public function get($column, $foreign_key = null)
        {
            if ($this->_statement == null)
                throw new Exception('Statement did not execute, cannot return unknown value for column ' . $column);

            $column = $this->_getColumnName($column, $foreign_key);

            if (isset($this->_fields[$this->_statement->getCriteria()->getSelectionAlias($column)]))
                return $this->_fields[$this->_statement->getCriteria()->getSelectionAlias($column)];
            else
                return null;
        }

        /**
         * Return the associated Criteria
         *
         * @return Criteria
         */
        public function getCriteria()
        {
            return $this->_statement->getCriteria();
        }

        public function offsetExists($offset)
        {
            $column = $this->_getColumnName($offset);
            return (bool) array_key_exists($this->_statement->getCriteria()->getSelectionAlias($column), $this->_fields);
        }

        public function offsetGet($offset)
        {
            return $this->get($offset);
        }

        public function offsetSet($offset, $value)
        {
            throw new \Exception('Not supported');
        }

        public function offsetUnset($offset)
        {
            throw new \Exception('Not supported');
        }

        public function getID()
        {
            if ($this->id_col === null)
                $this->id_col = $this->_statement->getCriteria()->getTable()->getIdColumn();

            return $this->get($this->id_col);
        }

    }
