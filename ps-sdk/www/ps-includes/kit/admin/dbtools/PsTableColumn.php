<?php

class PsTableColumn extends BaseDataStore {
    /*
     * Тип поля
     */

    const TYPE_BIT = 'BIT';
    const TYPE_INT = 'INT';
    const TYPE_INT_DATE = 'INT_DATE';
    const TYPE_CHAR = 'CHAR';
    const TYPE_STRING = 'STRING';
    const TYPE_STRING_DATE = 'STRING_DATE';
    const TYPE_TEXT = 'TEXT';

    /** Тип колонки */
    private $coltype;

    public function __construct(array $data) {
        /*
         * Инициализируем поля
         */
        parent::__construct($data);

        /*
         * Определим тип поля ($coltype)
         */
        switch ($this->getDataType()) {
            //ЧИСЛОВЫЕ
            case 'tinyint':
                if (starts_with($this->getName(), 'b_')) {
                    $this->coltype = self::TYPE_BIT;
                    break;
                }

            case 'int':
                if (starts_with($this->getName(), 'dt_')) {
                    $this->coltype = self::TYPE_INT_DATE;
                    break;
                }
                $this->coltype = self::TYPE_INT;
                break;

            //ТЕКСТОВЫЕ
            case 'char':
                if ($this->getCharMaxlen() == 1) {
                    $this->coltype = self::TYPE_CHAR;
                    break;
                }
            case 'varchar':
                if ($this->getCharMaxlen() <= 255) {
                    if (starts_with($this->getName(), 'dt_')) {
                        $this->coltype = self::TYPE_STRING_DATE;
                    } else {
                        $this->coltype = self::TYPE_STRING;
                    }
                    break;
                }
            case 'text':
                $this->coltype = self::TYPE_TEXT;
                break;

            default:
                check_condition(false, "Неизвестный тип данных для столбца {$this->getName()}: {$this->getDataType()}.");
                break;
        }
    }

    /**
     * Параметры первичного ключа
     */
    public function isPk() {
        return $this->IS_PK == 1;
    }

    public function isFk() {
        return $this->IS_FK == 1;
    }

    public function getParentTableName() {
        return $this->isFk() ? $this->REFERENCED_TABLE_NAME : null;
    }

    public function getParentColName() {
        return $this->isFk() ? $this->REFERENCED_COLUMN_NAME : null;
    }

    /** Is auto increment */
    public function isAi() {
        return contains_substring(strtolower($this->EXTRA), 'auto_increment');
    }

    /**
     * Параметры колонки
     */
    public function getTableName() {
        return $this->TABLE_NAME;
    }

    public function getName() {
        return $this->COLUMN_NAME;
    }

    public function isNullable() {
        return strtoupper($this->IS_NULLABLE) == 'YES';
    }

    /**
     * Признак необходимости заполнения параметра при редактировании
     */
    private function isMandatory() {
        return !$this->isNullable() || $this->isPkEquivalent();
    }

    public function getDefault() {
        return $this->COLUMN_DEFAULT;
    }

    private function getDataType() {
        return strtolower(trim($this->DATA_TYPE));
    }

    private function getCharMaxlen() {
        return 1 * $this->CHARACTER_MAXIMUM_LENGTH;
    }

    public function getComment() {
        return trim($this->COLUMN_COMMENT);
    }

    public function getType() {
        return $this->coltype;
    }

    public function getParentTableSelectOptions() {
        return $this->isFk() ? TableExporter::inst()->getTable($this->getParentTableName())->getSelectOptions() : null;
    }

    /**
     * Возвращает признак - содержится ли в данном столбце идентификатор переданного фолдинга
     */
    public function isHoldFoldingIdent(FoldedResources $folding) {
        return $folding->getTableColumnIdent() == $this->getName();
    }

    /**
     * Возвращает признак - содержится ли в данном столбце идентификатор какого-либо фолдинга, хранимого в данной таблице
     */
    private function isHoldAnyFoldingIdent() {
        foreach (FoldedResourcesManager::inst()->getTableFoldings($this->getTableName()) as $folding) {
            if ($this->isHoldFoldingIdent($folding)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Признак - может ли колонка иметь значение, ограниченное руками?
     */
    public function isCanBeManuallyRestricted() {
        return !$this->isAi() && !$this->isFk() && !in_array($this->getType(), array(self::TYPE_BIT, self::TYPE_TEXT));
    }

    /**
     * Допустимые значения, введённые руками
     */
    private $manuallyAllowed;

    public function getColumnAllowedValuesManual() {
        if (!is_array($this->manuallyAllowed)) {
            $this->manuallyAllowed = array();
            if (!$this->isCanBeManuallyRestricted()) {
                return $this->manuallyAllowed;
            }

            /* @var $folded FoldedResources */
            foreach (FoldedResourcesManager::inst()->getTableFoldings($this->getTableName()) as $folded) {
                if ($folded->getTableColumnStype() == $this->getName()) {
                    $this->manuallyAllowed[] = array('value' => $folded->getFoldingSubType(), 'content' => $folded->getEntityName());
                }
            };
        }
        return $this->manuallyAllowed;
    }

    /**
     * Получение списка допустимых значений для комбо-бокса
     */
    private $allowed;

    private function getColumnAllowedValues() {
        if (!is_array($this->allowed)) {
            $this->allowed = array();
            switch ($this->getType()) {
                case self::TYPE_BIT:
                    $this->allowed[0] = array('value' => 0, 'content' => 'Нет');
                    $this->allowed[1] = array('value' => 1, 'content' => 'Да');
                    break;
                case self::TYPE_INT:
                    if ($this->isFk()) {
                        $this->allowed = $this->getParentTableSelectOptions();
                        break;
                    }
                default:
                    $this->allowed = $this->getColumnAllowedValuesManual();
                    break;
            }
        }
        return $this->allowed;
    }

    public function hasColumnAllowedValues() {
        return count($this->getColumnAllowedValues()) > 0;
    }

    /**
     * Сохраняет значение для вставки его в запрос
     */
    public function safe4insert($val) {
        if ($this->isFk()) {
            if (is_string($val) && contains_substring($val, 'select')) {
                return ensure_ends_with(ensure_starts_with($val, '('), ')');
            }
            return is_numeric($val) ? 1 * $val : null;
        }

        switch ($this->getType()) {
            case self::TYPE_BIT:
            case self::TYPE_INT:
            case self::TYPE_INT_DATE:
                return is_numeric($val) ? 1 * $val : 'null';

            case self::TYPE_CHAR:
            case self::TYPE_STRING:
            case self::TYPE_STRING_DATE:
            case self::TYPE_TEXT:
                //MySQL различает пустую строку и null. Если столбец nullable и значение пустое - вставим null
                //Обязательно нужно выполнить mysql_real_escape_string, чтобы безопасно вставить значение
                return $this->isNullable() && !$val ? 'null' : "'" . mysql_real_escape_string($val) . "'";
        }
    }

    /**
     * Типы редактирования столбца.
     */

    const ET_HIDDEN = 'HIDDEN';
    const ET_EDITABLE = 'ENABLED';
    const ET_READONLY = 'DISABLED';
    const ET_EXCLUDED = 'EXCLUDED';

    private function checkEditType($action, $type) {
        switch ($action) {
            case PS_ACTION_CREATE:
                if ($this->isPk()) {
                    if ($this->isAi()) {
                        return $type == self::ET_EXCLUDED;
                    } else {
                        return $type == self::ET_EDITABLE;
                    }
                } else {
                    return $type == self::ET_EDITABLE;
                }
                break;

            case PS_ACTION_EDIT:
                if ($this->isPk()) {
                    return in_array($type, array(self::ET_HIDDEN, self::ET_READONLY));
                } else {
                    return $type == self::ET_EDITABLE;
                }

            case PS_ACTION_DELETE:
                if ($this->isPk()) {
                    return in_array($type, array(self::ET_HIDDEN, self::ET_READONLY));
                } else {
                    return $type == self::ET_READONLY;
                }
        }
        raise_error("Не удалось определить тип редактирования для столбца {$this->getTableName()}.{$this->getName()}.");
    }

    /**
     * Проверяет, нужно ли включать столбец в запросы на создание/изменение записи.
     * Столбец будет использован, если в процессе сохранения формы он передаётся на сервер (то есть либо hidden, лтбо editable).
     */
    public function isUseOn($action) {
        return $this->checkEditType($action, PsTableColumn::ET_EDITABLE) || $this->checkEditType($action, PsTableColumn::ET_HIDDEN);
    }

    /**
     * Метод возвращает признак - выгружать ли данное поле при экспорте.
     */
    public function isTake4Export() {
        return !$this->isAi() && ($this->isUseOn(PS_ACTION_CREATE) || $this->isUseOn(PS_ACTION_EDIT));
    }

    /**
     * Метод проверяет - нужно ли показывать столбец в таблице.
     * Отображаем только в том случае, если поле видимо на форме при редактировании
     */
    public function isVisibleInTable() {
        return $this->isUseOn(PS_ACTION_CREATE) || $this->isUseOn(PS_ACTION_EDIT);
    }

}

?>
