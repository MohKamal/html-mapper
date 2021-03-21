<?php
namespace  Showcase\Database\Migrations {
    use \Showcase\Framework\Database\Config\Table;
    use \Showcase\Framework\Database\Config\Column;

    class Section extends Table{

        /**
         * Migration details
         * @return array of columns
         */
        function handle(){
            $this->name = 'sections';
            $this->column(
                Column::factory()->name('id')->autoIncrement()->primary()
            );
            $this->column(
                Column::factory()->name('title')->string()
            );
            $this->column(
                Column::factory()->name('text')->string()->nullable()
            );
            $this->timespan();
        }
    }
}