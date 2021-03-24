<?php
namespace  Showcase\Database\Migrations {
    use \Showcase\Framework\Database\Config\Table;
    use \Showcase\Framework\Database\Config\Column;

    class Chapter extends Table{

        /**
         * Migration details
         * @return array of columns
         */
        function handle(){
            $this->name = 'chapters';
            $this->column(
                Column::factory()->name('id')->autoIncrement()->primary()
            );
            $this->column(
                Column::factory()->name('text')->string()
            );
            $this->column(
                Column::factory()->name('section_title')->string()
            );
            $this->timespan();
        }
    }
}