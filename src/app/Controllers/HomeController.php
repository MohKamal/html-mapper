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
             * The mapper is used to to map html options to your objects
             * Example :
             * Section has title and text | Section [title, text]
             * and we want the h1 element text to be saved to the title property and all the p options text to be saved to the text property
             * so we tell the mapper : 
             *             1) <h1> ======> Section->title
             *             2) all the <p> ======> Section->text
             * This figure in code goes like this : 
             *             Mapper::html($html)->queries(
             *                                          'query' => 'main_element_query',
             *                                          'classes' => [
             *                                                        [
             *                                                          'name' => '\Showcase\Models\Section', // object class
             *                                                          'options' => [
             *                                                                  'queries' => [
             *                                                1) get first h4 element ['query' => function($html) { return [$html->find('h1', 0)];}, 'property' => 'title'],
             *                                                2) get first p element ['query' => function($html) { return [$html->find('p', 0)];}, 'property' => 'text']
             *                                                                    ]
             *                                                                   ]
             *                                                           ]
             *                                                          ])->map();
             * now the mapper will return an array with one object section with a valid title and a text
             */

            // Get the html string from the file index.htm situeted in storage/app/index.htm
            $html = Storage::folder('app')->get('index.htm');
            // Get the value of h1 in html and save it into the section title $section->title, first() is used to get the first h1 text
            $list = Mapper::html($html)->queries(
                [
                    'query' => '.first_div',
                    'classes' => [
                                [
                                    'name' => '\Showcase\Models\Section',
                                    'options' => [
                                        'queries' => [
                                            //['query' => 'h4', 'property' => 'title'],
                                            ['query' => function($html) { return [$html->find('h1', 0)];}, 'property' => 'title'],
                                            ['query' => function($html) { return [$html->find('p', 0)];}, 'property' => 'text']
                                        ],
                                        'reference_to' => true
                                    ]
                                ],
                                [
                                    'name' => '\Showcase\Models\Chapter',
                                    'options' => [
                                        'queries' => [
                                            ['query' => '.MCDropDownBody.dropDownBody', 'sub' => 
                                                [
                                                    ['query' => function($html){
                                                            $paragraph = [];
                                                            // Get the starting node
                                                            $startPoint = $html->find('h4', 0);
                                                            if($startPoint != null) {
                                                                // While the current node has a sibling
                                                                while ( $next = $startPoint->next_sibling() ) {
                                                                    // And as long as it's different from the end node => div.second
                                                                    if ( $next->tag == 'h4')
                                                                        break;
                                                                    else{
                                                                        // Print the content
                                                                        $paragraph[] = $next;
                                                                        // And move to the next node
                                                                        $startPoint = $next;
                                                                    }
                                                                }
                                                            }
                                                            return $paragraph;
                                                    }, 'property' => 'text'],
                                                    ['query' => function($html) { return [$html->find('h4', 0)];}, 'property' => 'title']
                                                ]
                                            ]
                                        ],
                                        'reference_from' => [
                                            'name' => '\Showcase\Models\Section',
                                            'reference_property' => 'title',
                                            'property' => 'section_title'
                                        ],
                                        'multiple' => true,
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
             *  map: execute the queries and return an array with all the results
             * 
             * queries keys : 
             *  query: the main query of the mapper, simple_html_dom query, for more check manual => https://simplehtmldom.sourceforge.io/manual.htm
             *      Example: 
             *          <div id="main">
             *              <div class="wrapper">
             *                  <p>some text here</p>
             *              </div>
             *          </div>
             *  Ower main query would be to select the main div and then set the objects queries to specify the properties: 'query' => 'div[id=main]
             *  classes: this key regroup all the objects mappers, with their queries and other options
             * 
             * classes keys:
             *  name: the name of the object, full namespace. Example \Showcase\Models\Model
             *  options: this key regroup the queries for the object
             * 
             * options keys:
             *  reference_to: if you want to use a property value of an object in other object (like foreign key), you have to set this to true:
             *      Example:
             *          Section(title, text) | Chapter (title, text, section_title)
             *              in Section you would do:
             *                [
             *                 'name' => '\Showcase\Models\Section',
             *                  'options' => [
             *                      'queries' => [
             *                        ],
             *                        'reference_to' => true
             *                      ]
             *                 ]
             *  reference_from: this it the other key setter for the reference_to, this tell the mapper to set a property value of the object, to another object property
             *      Example:
             *          'reference_from' => [
             *                    'name' => '\Showcase\Models\Section',
             *                    'reference_property' => 'title',
             *                    'property' => 'section_title'
             *                   ],
             *      name: the name of the object to reference to
             *      reference_property: the other object property ot reference to
             *      property: this object property to set it's value
             * 
             *  multiple: if you want one object, don't set this, or set it to false, but if you want multiple objects, set this true
             *  queries: the queries to map the object properties:
             *      Example:
             *          ['query' => 'h4', 'property' => 'title']
             *      You need to set the query string and property name
             *      The query string can be function
             *          Example:
             *          ['query' => function($html) { return [$html->find('h1', 0)];}, 'property' => 'title']
             *              => Get the first h1 in the main node of the main query
             *  
             */
            return self::response()->view('App/welcome');
        }
    }
}