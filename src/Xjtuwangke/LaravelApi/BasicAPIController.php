<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14/10/9
 * Time: 23:05
 */

namespace Xjtuwangke\LaravelApi;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;


class BasicAPIController extends \Controller{

    const MethodPost = 'post';
    const MethodGet  = 'get';
    const MethodAny  = 'any';

    static protected $apiMethod = self::MethodPost;

    static protected $apiFilter = null;

    static protected $apiUriPrefix = 'api/';

    protected static $version = '1.0';

    static protected $apiName = 'test';

    protected $errors = [];

    protected $result = null;

    protected $parameters = [];

    protected $response = [];

    protected $timestamp = 0;

    protected $debug = true;

    protected $debug_message = [];

    static public function getAction(){
        return str_replace( '/' , '.' , static::getUriPrefix() . static::getName() . '_' . static::getVersion() );
    }

    static public function getMethod(){
        return static::$apiMethod;
    }

    static public function getUriPrefix(){
        return static::$apiUriPrefix;
    }

    static public function getName(){
        return static::$apiName;
    }

    static public function getVersion(){
        return static::$version;
    }

    static public function registerRoutes(){
        $method = static::getMethod();
        $class = get_called_class();
        Route::$method(  static::getUriPrefix() . static::getVersion() . "/" . static::getName() , [ 'as' => static::getAction()  , 'uses' => "{$class}@index" ] );
    }

    public function getParameters(){
        return $this->parameters;
    }

    public function getResponse(){
        return $this->response;
    }

    public function hasError(){
        return ! empty( $this->errors );
    }

    public function isSuccess(){
        return ! $this->hasError();
    }

    protected function pushError( $code , $msg ){
        $this->errors[] = [ 'code' => $code , 'msg' => $msg ];
        $this->hasError = true;
        return $this;
    }

    protected function lastError(){
        if( empty( $this->errors ) ){
            return [ 'errcode' => 0 , 'errmsg' => '' ];
        }
        else{
            return end( $this->errors );
        }
    }

    protected function getParameter( $name , $default = null ){
        if( array_key_exists( $name , $this->parameters ) ){
            return $this->parameters[ $name ];
        }
        else{
            return $default;
        }
    }

    protected function debugMessage( $msg ){
        $this->debug_message[] = $msg;
        return $this;
    }

    public function index(){
        if( class_exists( '\Debugbar' ) ){
            \Debugbar::disable();
        }
        switch( $this->getMethod() ){
            case static::MethodPost:
                $this->parameters = $_POST;
                break;
            case static::MethodGet:
                $this->parameters = $_GET;
                break;
            default:
                $this->parameters = Input::all();
        }
        $this->handle();
        return $this->response();
    }

    protected function handle(){

    }

    protected function onResponding(){

    }

    protected function response(){
        $response = $this->lastError();
        if( $this->hasError() ){
            $response['result'] = 'fail';
        }
        else{
            $response['result'] = 'success';
        }
        if( $this->debug ){
            $response['debug'] = $this->debug_message;
        }
        $this->response = array_merge( $this->response , $response );
        $this->onResponding();

        $response = Response::json( $this->response , 200 , [] , JSON_UNESCAPED_UNICODE );
        $response->header( 'Content-Type' , 'application/json; charset=utf-8' , true );
        $response->header( 'Cache-Control' , 'no-cache' , true );
        return $response;
    }
}