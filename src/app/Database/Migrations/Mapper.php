<?php
namespace  Showcase\Database\Migrations {
    use \Showcase\Framework\Database\Config\Table;
    use \Showcase\Framework\Database\Config\Column;

    class Mapper extends Table{

        /**
         * Migration details
         * @return array of columns
         */
        function handle(){
            $this->name = 'mappers';
            $this->column(
                Column::factory()->name('id')->autoIncrement()->primary()
            );
            $this->column(
                Column::factory()->name('class')->string()
            );
            $this->column(
                Column::factory()->name('property')->string()
            );
            $this->column(
                Column::factory()->name('query')->string()->nullable()
            );
            $this->timespan();
        }
    }
}