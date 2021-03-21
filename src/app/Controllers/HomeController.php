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
            $html = Storage::folder('app')->get('index.htm');
            $section = new Section();
            $section = Mapper::html($html)->object($section)->proprety("title")->query("h1")->first();
            $section = Mapper::html($html)->object($section)->proprety("text")->concatenate()->query("p")->get();
            return self::response()->view('App/welcome');
        }
    }
}