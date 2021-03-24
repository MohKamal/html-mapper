<?php
namespace  Showcase\Models{
    use \Showcase\Framework\Database\Models\BaseModel;
    use \Exception;
    
    class Chapter extends BaseModel
    {
        /**
         * Init the model
         */
        public function __construct(){
            $this->migration = 'Chapter';
            BaseModel::__construct();
        }

    }

}