<?php
namespace  Showcase\Models{
    require_once __DIR__ . '/simple_html_dom.php';

    use \Exception;
    use \Showcase\Framework\IO\Debug\Log;
    
    class Mapper
    {

        protected $_html;
        protected $_list_obj;
        protected $_conditions;

        protected $_one_object;
        protected $_list_of_objects;
        protected $_concatenate;

        /**
         * Init the model
         */
        public function __construct(){
            $this->_list_obj = [];
            $this->_one_object = false;
            $this->_list_of_objects = false;
            $this->_concatenate = false;
        }
        
        public static function html($html) {
            $mapper = new self;
            $mapper->_html = str_get_html($html);
            return $mapper;
        }

        public function queries($queries) {
            $this->_conditions = $queries;
            return $this;
        }

        public function first() {
            $list_objs = [];
            foreach($this->_conditions as $condition) {
                $results = $this->_html->find($condition['query']);
                if (is_array($results)) {
                    $concat = key_exists('concatenate', $condition) ? $condition['concatenate'] : false;
                    if (filter_var($concat, FILTER_VALIDATE_BOOLEAN)) {
                        foreach ($results as $res) {
                            $condition['object']->{$condition['property']} .= $res->plaintext;
                        }
                    } else {
                        $condition['object']->{$condition['property']} = $results[0]->plaintext;
                    }
                }
                else
                    $condition['object']->{$condition['property']} = $results->plaintext;

                $return = key_exists('return', $condition) ? $condition['return'] : true;
                if(filter_var($return, FILTER_VALIDATE_BOOLEAN))
                    $list_objs[] = $condition['object'];
            }

            return $list_objs;
        }

        public function get() {
            $list_objs = [];
            foreach($this->_conditions as $condition) {
                $class = $condition['class'];
                $obj = new $class;
                $results = $this->_html->find($condition['query']);
                if (is_array($results)) {
                    foreach ($results as $res) {
                        $obj->{$condition['property']} .= $res->plaintext;
                    }
                }
                else
                    $obj->{$condition['property']} = $results->plaintext;

                $return = key_exists('return', $condition) ? $condition['return'] : true;
                if(filter_var($return, FILTER_VALIDATE_BOOLEAN))
                    $list_objs[] = $obj;
            }

            return $list_objs;
        }

    }

}