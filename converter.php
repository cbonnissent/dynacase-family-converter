<?php

class FamCSVExport {

    const ONLY_FAM = "only_fam";
    const ONLY_PARAM = "only_param";

    private static $_attr_schema = array(
        "id"=> 1,
        "parent"=> 2,
        "label"=> 3,
        "intitle"=> 4,
        "inabstract"=> 5,
        "type"=> 6,
        "order"=> 7,
        "visibility"=> 8,
        "need"=> 9,
        "link"=> 10,
        "phpfile"=> 11,
        "phpfunc"=> 12,
        "elink"=> 13,
        "constraint"=> 14,
        "options"=> 15,
        "comment"=> 16,
        );

    private static $csv_fam_schema = array(
        "father",
        "title",
        "id",
        "classname",
        "logicalname");

    private static $csv_first_line_schema = array(
        "//",
        "Father",
        "Title",
        "Id",
        "Class",
        "Logical Name"
        );

    private $currentFam = null;

    public function __construct__(FamDefinition $currentFamDef) {
        $this->currentFam = $currentFamDef;
        return $this;
    }

    public function export($path, $option) {
        if (!empty($option) && 
            ($option != FamCSVExport::ONLY_FAM || $option != FamCSVExport::ONLY_PARAM)) {
            throw new ExportToCSVException("Option need to be {FamCSVExport::ONLY_FAM} or {FamCSVExport::ONLY_PARAM}", 1);
        }

        $file = fopen($path, "w");
        if ($file === false) {
            throw new ExportToCSVException("Unable to open $path", 1);
        }
        $addALine = function($line) use ($file) {
            fwrite($file, implode($line, ";")."\n");
        };
        $addALine(self::$csv_first_line_schema);

        $line = array();
        
        if (empty($option) || $option === FamCSVExport::ONLY_FAM) {

        }
    }

}


class FamDefinition {

    private static $_struct_attr = array(
            "frame",
            "tab",
            "array"
        );

    public $autoNumRules = array(
            "tab" => 1000,
            "frame" => 100,
            "default" => 10
        );

    public $defaultValuesRules = array(
        'global' => array(
            "visibility" => "W",
            "intitle" => "N",
            "inabstract" => "N",
        'htmltext'=> array(
            "options" => 'toolbar=Basic|toolbarexpand=n',
            ),
        'enum'=> array(
            "options" => 'bmenu=no|eunset=yes|system=yes',
            ),
        'array'=> array(
            "options" => 'vlabel=up',
            ),
        'longtext'=> array(
            "options" => 'editheight=4em',
            ),
        )
    );

    public $maxNum = 0;
    public $currentStructAttribute = array("id" => null, "type" => null);

    public $logicalName = null;
    public $familyProperties = array();

    public $attributes = array();
    public $parameters = array();

    /**
     * Constructor
     * @param  string $familyName Logical name of the current family
     * @param  Array  $properties array of optionnal definition
     * @return Object
     */
    public function __construct__($logicalName, $properties) {

        if (!empty($properties) && !is_array($properties)) {
            throw new FamilyException("properties needs to be an array", 1);
        }

        $this->logicalName = $logicalName;
        $this->familyProperties = $properties;

        return $this;
    }
    /**
     * Add an attribute to the stack
     * @param string $attributeName Logical name of the attribute
     * @param string $label         Label of the attribute
     * @param string $type          Type of the attribute
     * @param array $properties     Optional properties of the attribute
     * @param array $options        Handling option (mode => modattr|param, forceNum => int )
     */
    public function addAttribute($attributeName, $label, $type, $properties = null, $options = null) {
        $attribute = array(
            "id" => $attributeName,
            "label" => $label,
            "type" => $type
        );
        if (in_array($type, self::$_struct_attr)) {
            if ($type === "frame") {
                if ($currentStructAttribute["type"] === "tab") {
                    $attribute["parent"] = $currentStructAttribute["id"];
                }
            } elseif ($type === "array") {
                $attribute["parent"] = $currentStructAttribute["id"];
            }
            $currentStructAttribute["id"] = $attributeName;
            $currentStructAttribute["type"] = $type;
        } else {
            $attribute["parent"] = $currentStructAttribute["id"];
        }

        if (!empty($properties)) {
            $attribute = array_merge($attribute, $properties);
        }
        if(isset($options["forceNum"])) {
            $attribute["order"] = computeCurrentNumber($type, $options["forceNum"]);
        }
        if(isset($options["mode"])) {
            $attribute[$options["mode"]] = true;
        }
        if (isset($this->defaultValuesRules["global"])) {
            foreach ($this->defaultValuesRules["global"] as $key => $value) {
                $attribute[$key] = isset($attribute[$key]) ? $attribute[$key] : $value;
            }
        }
        if (isset($this->defaultValuesRules[$type])) {
            foreach ($this->defaultValuesRules[$type] as $key => $value) {
                $attribute[$key] = isset($attribute[$key]) ? $attribute[$key] : $value;
            }
        }
        return $this;
    }


    private function computeCurrentNumber($type, $number = null) {
        if ($number !== null) {
            $this->maxNum = intval($number);
        } else if (isset($this->autoNumRules[$type])) {
            $this->maxNum += $this->autoNumRules[$type];
        } else {
            $this->maxNum += isset($this->autoNumRules["default"]) ? $this->autoNumRules["default"] : 0;
        }
        return $this->maxNum;
    }

}


    /*public $attr_schema = array(
        "id"=> 1,
        "parent"=> 2,
        "label"=> 3,
        "intitle"=> 4,
        "inabstract"=> 5,
        "type"=> 6,
        "order"=> 7,
        "visibility"=> 8,
        "need"=> 9,
        "link"=> 10,
        "phpfile"=> 11,
        "phpfunc"=> 12,
        "elink"=> 13,
        "constraint"=> 14,
        "options"=> 15,
        "comment"=> 16,
        );

    const NOT_ATTR_SCHEMA = array("modattr", "children","attr", "default");

const CSV_FAM_SCHEMA = array(
    "father" => array(
        "column" => 1,
        "row" => 1),
    "title" => array(
        "column" => 2,
        "row" => 1),
    "id" => array(
        "column" => 3,
        "row" => 1),
    "classname" => array(
        "column" => 4,
        "row" => 1),
    "logicalname" => array(
        "column" => 5,
        "row" => 1),
        );

const CSV_FIRST_LINE_SCHEMA = array(
    "//" => 0,
    "Father" => 1,
    "Title" => 2,
    "Id" => 3,
    "Classe" => 4,
    "Logical Name" => 5);

 public $autonumRules = array(
    'tab' => 1000,
    'frame' => 100,
    'all' => 10
    );

public $defaultValuesRules = 

array(
    'global' => array(
        "visibility" => "W",
        "intitle" => "N",
        "inabstract" => "N",
    'htmltext'=> array(
        "options" => 'toolbar=Basic|toolbarexpand=n',
        ),
    'enum'=> array(
        "options" => 'bmenu=no|eunset=yes|system=yes',
        ),
    'array'=> array(
        "options" => 'vlabel=up',
        ),
    'longtext'=> array(
        "options" => 'editheight=4em',
        ),
        )*/

class FamilyException extends Exception {}
class ExportToCSVException extends Exception {}