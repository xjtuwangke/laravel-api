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

    use RequiredParametersTrait;

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

    protected $debug = false;

    protected $debug_message = [];

    protected $cache = null;

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

    public function cacheConfig(){
        return array(
            'remember' => false ,
            'minutes' => 10 ,
            'flush' => false,
            'tags' => array(
                'api' ,
                static::$version ,
                static::$apiName ,
                ) ,
        );
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
        if( $this->check() ){
            $this->beforeHandle();
            if( ! $this->cache ){
                $this->cache = new BasicAPICacher();
            }
            if( $cache = $this->cache->read( $this ) ){
                $this->response = $cache;
                $this->debugMessage( '缓存命中' );
            }
            else{
                $this->handle();
                $this->cache->save( $this );
            }
            $this->afterHandle();
        }
        else{
            $this->debugMessage( '验证check()函数没有通过' );
        }
        return $this->response();
    }

    protected function skipCheck(){
        if( in_array( \App::environment() , [ 'local' , 'test' , 'beta' ] ) ){
            if( $this->getParameter( 'debug' ) == 1 ){
                $this->debug = true;
                $this->debugMessage( '跳过verify' );
                return true;
            }
        }
    }

    protected function check(){
        return $this->check_required();
    }

    protected function beforeHandle(){
    }

    protected function handle(){

    }

    protected function afterHandle(){

    }

    protected function onResponding(){
        $message = static::getAction();
        $context = array(
            'input' => $this->parameters ,
            'response' => $this->response ,
            'errors'  => $this->errors ,
            'header' => \Request::header() ,
        );
        if( $this->hasError() ){
            APILogger::warning( $message , $context );
        }
        else{
            APILogger::access( $message , $context );
        }
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
        else{
            $response['debug'] = array();
        }
        $this->response = array_merge( $this->response , $response );
        $this->onResponding();

        $response = Response::json( $this->response , 200 , [] , JSON_UNESCAPED_UNICODE );
        $response->header( 'Content-Type' , 'application/json; charset=utf-8' , true );
        $response->header( 'Cache-Control' , 'no-cache' , true );
        return $response;
    }
}