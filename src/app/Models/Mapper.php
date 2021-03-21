<?php
namespace  Showcase\Models{
    require_once __DIR__ . '/simple_html_dom.php';

    use \Showcase\Framework\Database\Models\BaseModel;
    use \Exception;
    use \Showcase\Framework\IO\Debug\Log;
    
    class Mapper extends BaseModel
    {

        protected $_html;
        protected $_obj;
        protected $_list_obj;

        protected $_one_object;
        protected $_list_of_objects;
        protected $_concatenate;

        /**
         * Init the model
         */
        public function __construct(){
            $this->migration = 'Mapper';
            BaseModel::__construct();
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

        public function class($class) {
            $this->class = $class;
            return $this;
        }

        public function proprety($property) {
            $this->property = $property;
            return $this;
        }

        public function query($query) {
            $this->query = $query;
            return $this;
        }

        public function object($obj) {
            $this->_obj = $obj;
            $this->_one_object = true;
            return $this;
        }

        public function arrayOfObjects($array) {
            $this->_list_obj = $array;
            $this->_list_of_objects = true;
            return $this;
        }

        public function concatenate() {
            $this->_concatenate = true;
            return $this;
        }

        public function first() {
            if(is_null($this->_obj))
                $this->_obj = new $this->class;
            $results = $this->_html->find($this->query);
            if(is_array($results))
                $this->_obj->{$this->property} = $results[0]->plaintext;
            else
                $this->_obj->{$this->property} = $results->plaintext;

            return $this->_obj;
        }

        public function get() {
            $list = [];
            $results = $this->_html->find($this->query);
            if(is_null($this->_obj))
                $this->_obj = new $this->class;
            if (!is_array($results)) {
                $this->_obj->{$this->property} = $results[0]->plaintext;
                $list[] = $this->_obj;
            } else {
                $index = 0;
                foreach ($results as $res) {
                    $obj = null;
                    if ($this->_concatenate) {
                        if(!is_null($this->_obj)) {
                            $this->_obj->{$this->property} .= $res->plaintext;
                        }
                    } else if($this->_list_of_objects) {
                        $this->_list_obj[$index]->{$this->property} = $res->plaintext;
                        $index++;
                    } else {
                        $obj = new $this->class;
                        if ($this->_concatenate) {
                            $obj->{$this->property} = $res->plaintext;
                        }
                    }
                    $list[] = $obj;
                }

                if ($this->_concatenate) {
                    if(!is_null($this->_obj))
                        return $this->_obj;
                }
            }

            return $list;
        }

    }

}