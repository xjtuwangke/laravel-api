<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14/12/22
 * Time: 19:34
 */

namespace Xjtuwangke\LaravelApi;


class BasicAPICacher {


    protected function config( BasicAPIController $api ){
        $default = array(
            'remember' => false ,
            'minutes' => 10 ,
            'flush' => false,
            'remember_tags' => array(
                'api' ,
                $api->getVersion() ,
                $api->getName() ,
            ),
            'flush_tags' => array(
                'api' ,
                $api->getVersion() ,
                $api->getName() ,
            ),
        );
        $config = array_merge( $default , $api->cacheConfig() );
        return $config;
    }

    public function prepare( BasicAPIController $api ){
        $config = $this->config( $api );
        if( false == $config['remember'] ){
            return array( null , null , 0 );
        }
        else{
            $parameters = $api->getParameters();
            if( isset( $parameters['timestamp'] ) ){
                unset($parameters['timestamp'] );
            }
            if( isset( $parameters['v'] ) ){
                unset( $parameters['v'] );
            }
            $hash = sha1( serialize( $config['remember_tags'] ) ) .sha1( serialize( $parameters ) );
            return array( \Cache::tags( $config['remember_tags'] ) , $hash , $config['minutes']);
        }
    }

    public function read( BasicAPIController $api ){
        list( $cache , $hash ) = $this->prepare( $api );
        if( $cache ){
            if( $cache->has( $hash ) ){
                return $cache->get( $hash );
            }
            else{
                return null;
            }
        }
        else{
            return null;
        }
    }

    public function save( BasicAPIController $api ){
        list( $cache , $hash , $minutes ) = $this->prepare( $api );
        if( $cache ){
            $cache->put( $hash , $api->getResponse() , $minutes );
        }
    }

    public function flush( BasicAPIController $api ){
        $config = $this->config( $api );
        if( $config['flush'] ){
            \Cache::tags( $config['flush_tags'] )->flush();
        }
    }

}