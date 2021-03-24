<?php
namespace  Showcase\Models{
    require_once __DIR__ . '/simple_html_dom.php';

    use \Exception;
    use \Showcase\Framework\IO\Debug\Log;
    
    class Mapper
    {

        protected $_html;
        protected $_conditions;


        /**
         * Init the model
         */
        public function __construct(){
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

        public function map() {
            $list_objs = [];
            $html_object = $this->_html->find($this->_conditions['query']);
            $references = [];
            foreach($html_object as $_html) {
                foreach($this->_conditions['classes'] as $class) {
                    $_class = $class['name'];
                    $obj = new $_class;
                    foreach($class['elements']['queries'] as $query) {
                        if(is_array($query)) {
                            $results = $_html->find($query['query']);
                            if($results) {
                                $index = 0;
                                foreach($results as $res) {
                                    $concatenate = false;
                                    $exist_for_concatenation = false;
                                    if(key_exists('concatenate', $query)){
                                        $concatenate = filter_var($query['concatenate'], FILTER_VALIDATE_BOOLEAN);
                                    }

                                    if(!$concatenate) {
                                        $obj = new $_class;
                                        $obj->{$query['property']} = $res->plaintext;
                                    } else {
                                        $obj = $this->getObject($list_objs, $_class);
                                        if(is_null($obj))
                                            $obj = new $_class;
                                        else
                                            $exist_for_concatenation = true;
                                        $obj->{$query['property']} .= $res->plaintext;
                                    }

                                    if(key_exists('reference_to', $class['elements']))
                                    {
                                        if (filter_var($class['elements']['reference_to'], FILTER_VALIDATE_BOOLEAN)) {
                                            $references = ['name' => $_class, 'object' => $obj];
                                        }
                                    }

                                    if(key_exists('reference_from', $class['elements'])){
                                        $element = $class['elements']['reference_from'];
                                        /*$reference = array_filter($references, function($arr) use ($element) {
                                            return $arr['name'] == $element['name'];
                                        });*/
                            
                                        if($references['name'] === $element['name']) {
                                            $obj->{$element['property']} = $references['object']->{$element['reference_property']};
                                        }
                                    }
                                    if(!$concatenate)
                                        $list_objs[] = $obj;
                                    else {
                                        if(!$exist_for_concatenation)
                                            $list_objs[] = $obj;
                                    }
                                }
                            }


                        }
                    }
                }
            }
            return $list_objs;
        }

        private function getObject($list, $class) {

            foreach($list as $obj) {
                if(is_a($obj, $class))
                    return $obj;
            }
            
            return null;
        }

    }

}