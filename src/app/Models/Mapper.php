<?php
namespace  Showcase\Models{
    require_once __DIR__ . '/simple_html_dom.php';

    use \Exception;
    use \Showcase\Framework\IO\Debug\Log;
    
    class Mapper
    {

        protected $_html;
        protected $_conditions;
        protected $references;

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
            $this->references = [];
            $list_objs = [];
            $html_object = $this->_html->find($this->_conditions['query']);
            foreach($html_object as $_html) {
                foreach($this->_conditions['classes'] as $class) {
                    $_class = $class['name'];
                    $queries = $class['options']['queries'];

                    $queries_results = $this->executeMapping($queries, $_html);
                    $multiple = false;
                    if(key_exists('multiple', $class['options'])) {
                        $multiple = filter_var($class['options']['multiple'], FILTER_VALIDATE_BOOLEAN);
                    }

                    if($multiple) {
                        $objs = $this->createObjects($_class, $queries_results, $class['options']);
                        foreach($objs as $obj)
                            $list_objs[$_class][] = $obj;
                    }else{
                        $obj = $this->createObject($_class, $queries_results);
                        $obj = $this->checkReferences($obj, $class['options'], $_class);
                        $list_objs[$_class][] = $obj;
                    }
                }
            }
            return $list_objs;
        }

        private function executeMapping($queries, $html) {
            $queries_results = [];
            for($i=0; $i < count($queries); $i++) {
                $query = $queries[$i];
                if(is_array($query)) {

                    if(key_exists('sub', $query)) {
                        $sub_html = $this->executeQuery($query['query'], $html);
                        foreach($sub_html as $_html) {
                            $sub_queries_results = $this->executeMapping($query['sub'], $_html);
                            foreach($sub_queries_results as $property => $results){
                                $queries_results[$property][] = $results;
                            }
                        }
                    } else {
                        foreach($this->executeQuery($query['query'], $html) as $c) {
                            $queries_results[$query['property']][] = $c;
                        }
                    }
                }
            }

            return $queries_results;
        }

        private function createObject($class, $blocks) {
            $obj = new $class;
            foreach($blocks as $property => $block) {
                foreach($block as $b) {
                    $obj->{$property} .= $b->plaintext;
                }
            }
            return $obj;
        }

        private function createObjects($class, $blocks, $options) {
            $objects = [];
            $properties = array_keys($blocks);

            for($i=0; $i < count($blocks[$properties[0]]); $i+=count($properties)){
                $obj = new $class;
                for($j=0; $j < count($properties); $j++) {
                    $subBlocks = count($blocks[$properties[$j]]);
                    for($k=0; $k < $subBlocks; $k++) {
                        $property = $properties[$j];
                        $obj->{$property} .= $blocks[$property][$k][$i+$j]->plaintext;
                    }
                }
                $obj = $this->checkReferences($obj, $options, $class);
                $objects[] = $obj;
            }
            return $objects;
        }

        private function checkReferences($obj, $options, $_class) {
            if(key_exists('reference_to', $options)) {
                if (filter_var($options['reference_to'], FILTER_VALIDATE_BOOLEAN)) {
                    $this->references = ['name' => $_class, 'object' => $obj];
                }
            }

            if(key_exists('reference_from', $options)) {
                $element = $options['reference_from'];
                /*$reference = array_filter($this->references, function($arr) use ($element) {
                    return $arr['name'] == $element['name'];
                });*/
    
                if($this->references['name'] === $element['name']) {
                    $obj->{$element['property']} = $this->references['object']->{$element['reference_property']};
                }
            }

            return $obj;
        }

        private function executeQuery($query, $_html) {
            if(is_string($query))
                $results = $_html->find($query);
            else {
                $results = $query($_html);
            }
                            
            if(is_array($results)) {
               return $results;
            }

            return $results;
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