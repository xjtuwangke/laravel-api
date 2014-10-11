<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14/10/12
 * Time: 03:31
 */

namespace Xjtuwangke\LaravelApi;


trait RequiredParametersTrait {

    protected $require = [];

    protected function check_required(){
        $pass = true;
        foreach( $this->require as $one ){
            if( is_null( $this->getParameter( $one ) ) ){
                $this->debugMessage( '缺少参数:' . $one );
                $pass = false;
            }
        }
        if( false == $pass ){
            $this->pushError( '-1' , '参数不全' );
        }
        return $pass;
    }

}