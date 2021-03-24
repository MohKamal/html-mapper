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
            // Get the value of h1 in html and save it into the section title $section->title, first() is used to get the first h1 text
            $list = Mapper::html($html)->queries(
                [
                    'query' => '.MCDropDownBody.dropDownBody',
                    'classes' => [
                                [
                                    'name' => '\Showcase\Models\Section',
                                    'elements' => [
                                        'queries' => [
                                            ['query' => 'h4', 'property' => 'title']
                                        ],
                                        'reference_to' => true
                                    ]
                                ],
                                [
                                    'name' => '\Showcase\Models\Chapter',
                                    'elements' => [
                                        'queries' => [
                                            ['query' => 'p', 'property' => 'text']
                                        ],
                                        'reference_from' => [
                                            'name' => '\Showcase\Models\Section',
                                            'reference_property' => 'title',
                                            'property' => 'section_title'
                                        ]
                                    ]
                                ]
                    ]
                ]
            )->map();
            Log::var_dump($list); // See storage/logs/

            /**
             * Mapper - docs
             * 
             * always start with the function html
             * example :
             *  Mapper::html("<h1>mm</h1>");
             * 
             * functions : 
             *  queries : set conditions and objects and queries
             *  map : execute the queries and return an array
             * 
             * queries keys : 
             *  classes: define all the objects classes and mapping details
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