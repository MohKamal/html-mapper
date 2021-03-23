<?php
/**
 * 
 * Default controller in the Showcase
 * 
 */
namespace Showcase\Controllers{

    use \Showcase\Framework\HTTP\Controllers\BaseController;
    use \Showcase\Framework\IO\Storage\Storage;
    use \Showcase\Framework\IO\Debug\Log;
    use \Showcase\Models\Mapper;
    use \Showcase\Models\Section;

    class HomeController extends BaseController{

        /**
         * Return the welcome view
         */
        static function Index(){
            /**
             * Concept:
             * 
             * The mapper is used to to map html elements to your objects
             * Example :
             * Section has title and text | Section [title, text]
             * and we want the h1 element text to be saved to the title property and all the p elements text to be saved to the text property
             * so we tell the mapper : 
             *             1) <h1> ======> Section->title
             *             2) all the <p> ======> Section->text
             * This figure in code goes like this : 
             *             Mapper::html($html)->queries(
             *                         1) ['query' => 'h1', 'property' => 'title', 'object' => $section, 'return' => false],
             *                         2) ['query' => 'p', 'property' => 'text', 'concatenate' => true, 'object' => $section])->first();
             * now the mapper will return an array with one object section with a valid title and a text
             */

            // Get the html string from the file index.htm situeted in storage/app/index.htm
            $html = Storage::folder('app')->get('index.htm');
            // Create new object section from Model Section
            $section = new Section();
            // Get the value of h1 in html and save it into the section title $section->title, first() is used to get the first h1 text
            $section = Mapper::html($html)->queries(
                [
                    ['query' => 'h1', 'property' => 'title', 'object' => $section , 'return' => false],
                    ['query' => 'div[class=MCDropDownBody]', 'property' => 'text', 'object' => $section]
                ]
            )->first();
            Log::var_dump($section); // See storage/logs/

            /**
             * Mapper - docs
             * 
             * always start with the function html
             * example :
             *  Mapper::html("<h1>mm</h1>");
             * 
             * functions : 
             *  queries : set conditions and objects and queries
             *  first : execute the queries and return an array and get first result
             *  get: execute tje queries and return an array with all the results
             * 
             * queries keys : 
             *  object: specify the object to save the html text to
             *  property: the property where to save the data
             *  query: simple_html_dom query, for more check manual => https://simplehtmldom.sourceforge.io/manual.htm
             *  return: if you don't the object to be included in the returned array, set this to false, it's true by default
             *  concatenate: if you want to concatenate all the results in one property, set this to true, it's false by default
             * 
             */
            return self::response()->view('App/welcome');
        }
    }
}