<?php

namespace App\Listeners;

class MissionSubscriber 
{
    public function DoMission($mission)
    {
        $requests = \Request::all();
        $user = \Auth::user();
        
        $return = NULL;
        eval(helper('clear_php_code', $mission->php_code));            
        return $return;
    }
}
