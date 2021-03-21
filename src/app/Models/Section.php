<?php
namespace  Showcase\Models{
    use \Showcase\Framework\Database\Models\BaseModel;
    use \Exception;
    
    class Section extends BaseModel
    {
        /**
         * Init the model
         */
        public function __construct(){
            $this->migration = 'Section';
            BaseModel::__construct();
        }

    }

}