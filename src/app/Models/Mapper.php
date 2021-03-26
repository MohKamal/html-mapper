<?php
namespace  Showcase\Models{
    require_once __DIR__ . '/simple_html_dom.php';

    use \Exception;
    use \Showcase\Framework\IO\Debug\Log;
    
    class Mapper
    {

        protected $_html; // current html page as string
        protected $_conditions; // current queries to map with
        protected $references; // current reference_to objects

        /**
         * Init the model
         */
        public function __construct(){
        }
        
        /**
         * Init the html to map
         * 
         * @param string $html page
         * 
         * @return SimpleHtmlDom
         */
        public static function html($html) {
            // init new mapper object
            $mapper = new self;
            // init new SimpleHtmlDom object
            $mapper->_html = str_get_html($html);
            // return mapper
            return $mapper;
        }

        /**
         * Set the array of queries to use
         * 
         * @param array $queries
         * 
         * @return \Showcase\Models\Mapper
         */
        public function queries($queries) {
            $this->_conditions = $queries;
            return $this;
        }

        /**
         * Map the queries and html
         * 
         * @return array of mapped objects
         */
        public function map() {
            // save last object to reference to
            $this->references = []; 
            // list of objects to return
            $list_objs = []; 
            // execute the main query and get the list of the returned nodes
            $html_object = $this->_html->find($this->_conditions['query']);
            // loop on the returned nodes
            foreach($html_object as $_html) {
                // loop on the given classes
                foreach($this->_conditions['classes'] as $class) {
                    // get the class name
                    $_class = $class['name'];
                    // get the given queries for this class
                    $queries = $class['options']['queries'];
                    // execute the mapping the get the nodes
                    $queries_results = $this->executeMapping($queries, $_html);
                    // Check if the user want one object, or multiple objects
                    $multiple = false;
                    if(key_exists('multiple', $class['options'])) {
                        $multiple = filter_var($class['options']['multiple'], FILTER_VALIDATE_BOOLEAN);
                    }

                    if($multiple) {
                        // if user want multiple objects, call this functions
                        $objs = $this->createObjects($_class, $queries_results, $class['options']);
                        // sort the returne objects
                        foreach($objs as $obj)
                            $list_objs[$_class][] = $obj;
                    }else{
                        // if the user want one object call this function
                        $obj = $this->createObject($_class, $queries_results);
                        // Check if the object need references
                        $obj = $this->checkReferences($obj, $class['options'], $_class);
                        // Sort object on the list
                        $list_objs[$_class][] = $obj;
                    }
                }
            }
            // return the list of objects
            return $list_objs;
        }

        /**
         * Execute queries and return array of nodes
         * 
         * @param array $queries to execute
         * @param SimpleHtmlDom object
         * 
         * @return array of nodes
         */
        private function executeMapping($queries, $html) {
            // List of nodes to return
            $queries_results = [];
            // loop into the queries
            for($i=0; $i < count($queries); $i++) {
                // get the current query
                $query = $queries[$i];
                // check if the query is an array, it's need to be one
                if(is_array($query)) {
                    // check if this query, is composed, with a sub queries
                    if(key_exists('sub', $query)) {
                        // if this has sub queries, execute the main query and the nodes
                        $sub_html = $this->executeQuery($query['query'], $html);
                        // index for sorting the results into groups
                        $index = 0;
                        // loop into the returned nodes from the main query
                        foreach($sub_html as $_html) {
                            // map the queries with the current node
                            $sub_queries_results = $this->executeMapping($query['sub'], $_html);
                            // loop into the returned nodes, and sort them to the returning array
                            foreach($sub_queries_results as $property => $results){
                                foreach($results as $res) {
                                    $queries_results[$index][$property][] = $res;
                                }
                            }
                            // move forward
                            $index++;
                        }
                    } else {
                        // if this query don't have chilren
                        // get the results nodes from execution
                        $results = $this->executeQuery($query['query'], $html);
                        // loop the results and sort them
                        foreach($results as $node) {
                            $queries_results[$query['property']][] = $node;
                        }
                    }
                }
            }
            // return the nodes
            return $queries_results;
        }

        /**
         * To create one object, with concatenated results
         * 
         * @param string $class name to init the object
         * @param array of SimpleHtmlDom nodes
         * 
         * @return object of the $class
         */
        private function createObject($class, $blocks) {
            // init the object
            $obj = new $class;
            // loop into the nodes
            foreach($blocks as $property => $node) {
                foreach($node as $n)
                    $obj->{$property} .= $n->plaintext; // set object properties with the values
            }
            // return the object
            return $obj;
        }

        /**
         * This function create an array objects
         * using the group sorting
         * in the executeMapping function
         * 
         * @param string $class name to init objects
         * @param array of SimpleHtmlDom nodes
         * @param array $options of the query to check references
         * 
         * @return array of objects of the $class
         */
        private function createObjects($class, $blocks, $options) {
            // array of objects to return
            $objects = [];
            // get the properties names of the objects (like $object->text)
            $properties = array_keys($blocks[0]);
            // loop into the nodes
            for($i=0; $i < count($blocks); $i++) {
                // init an object
                $obj = new $class;
                // loop into the properties
                for($j=0; $j < count($properties); $j++) {
                    // get the name of current property
                    $property = $properties[$j];
                    // if the nodes is not null, continue
                    if(!is_null($blocks[$i])) {
                        // if the property name exist in the node, continue
                        if(key_exists($property, $blocks[$i])) {
                            // get the nodes of the current property
                            $nodes = $blocks[$i][$property];
                            // loop into the nodes
                            for($k=0; $k<count($nodes); $k++){
                                // if the nodes is not null, continue
                                if(!is_null($nodes[$k]))
                                    $obj->{$property} .= $nodes[$k]->plaintext; // set the value of the property to the object
                            }
                        }
                    }
                }
                // check reference for this object
                $obj = $this->checkReferences($obj, $options, $class);
                // add object to the array
                $objects[] = $obj;
            }
            // return objects
            return $objects;
        }

        /**
         * Check the objects refeneces, like foreign keys
         * 
         * @param object to use it in the reference
         * @param array $options of the query
         * @param string $class name
         * 
         * @return object same object in the param
         */
        private function checkReferences($obj, $options, $class) {
            // check if the query has reference_to this object
            if(key_exists('reference_to', $options)) {
                // if the reference_to is set to true
                if (filter_var($options['reference_to'], FILTER_VALIDATE_BOOLEAN)) {
                    // add this object to the refeneces array of this mapper
                    $this->references = ['name' => $class, 'object' => $obj];
                }
            }

            // if this query has reference_from in the options
            if(key_exists('reference_from', $options)) {
                // get the details of the other object
                $element = $options['reference_from'];
                /*$reference = array_filter($this->references, function($arr) use ($element) {
                    return $arr['name'] == $element['name'];
                });*/
                // if the object we need to reference to it exist in the reference array
                if($this->references['name'] === $element['name']) {
                    // set this object property value with the reference_to object value
                    $obj->{$element['property']} = $this->references['object']->{$element['reference_property']};
                }
            }
            // return the object
            return $obj;
        }

        /**
         * Execute the query
         * 
         * @param mixte it can be a simple string or a function
         * @param object SimpleHtmlDom object
         */
        private function executeQuery($query, $html) {
            // if the query is string
            if(is_string($query))
                $results = $html->find($query); // execute the string
            else {
                // if the query is a function, execute the function
                $results = $query($html);
            }

            // if the results are array
            if(is_array($results)) {
                // return them
               return $results;
            }

            // if not, add the results to an array and return it
            return [$results];
        }

    }

}