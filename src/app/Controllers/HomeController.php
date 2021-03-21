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
            // Get the html string from the file index.htm situeted in storage/app/index.htm
            $html = Storage::folder('app')->get('index.htm');
            // Create new object section from Model Section
            $section = new Section();
            // Get the value of h1 in html and save it into the section title $section->title, first() is used to get the first h1 text
            $section = Mapper::html($html)->object($section)->proprety("title")->query("h1")->first();
            // Get the value of every p element in the page, and save it to text $section->text and concetenate all the p texts to on preperties, get() get all p elements not only one
            $section = Mapper::html($html)->object($section)->proprety("text")->concatenate()->query("p")->get();
            /**
             * Mapper - docs
             * 
             * always start with the function html
             * example :
             *  Mapper::html("<h1>mm</h1>");
             * 
             * functions : 
             *  object : set an existing object to the mapper
             *  arrayOfObjects : set an array of objects to the mapper, its like the object function but with a list of objects
             *  class : give the mapper a class path to init object from it, example : Mapper::html($html)->class("\Showcase\Models\Section")
             *  property : the property where the value gonna be saved, example : Mapper::html($html)->class("\Showcase\Models\Section")->property("title")
             *  query : a simple_html_dom query to get the text of an element, for more details see : https://simplehtmldom.sourceforge.io/manual.htm
             *  concatenate : if you want to get multiple elements text and save it into one property of one object, use this function, example : Mapper::html($html)->class("\Showcase\Models\Section")->property("title")->concatenate()
             *  first : get the first html element
             *  get : an array of objects for the array of elements found
             * 
             */
            return self::response()->view('App/welcome');
        }
    }
}