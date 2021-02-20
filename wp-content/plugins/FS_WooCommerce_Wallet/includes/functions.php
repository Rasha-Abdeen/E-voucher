<?php

if(! defined('ABSPATH')) {
    header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$months = array(
   array('number' => '01', 'text' => __('Jan', 'fsww')),
   array('number' => '02', 'text' => __('Feb', 'fsww')),
   array('number' => '03', 'text' => __('Mar', 'fsww')),
   array('number' => '04', 'text' => __('Apr', 'fsww')),
   array('number' => '05', 'text' => __('May', 'fsww')),
   array('number' => '06', 'text' => __('Jun', 'fsww')),
   array('number' => '07', 'text' => __('Jul', 'fsww')),
   array('number' => '08', 'text' => __('Aug', 'fsww')),
   array('number' => '09', 'text' => __('Sep', 'fsww')),
   array('number' => '10', 'text' => __('Oct', 'fsww')),
   array('number' => '11', 'text' => __('Nov', 'fsww')),
   array('number' => '12', 'text' => __('Dec', 'fsww'))
);

function fswcw_format_date($date, $expiration_date = false){
    
    if($date != '') {
        $date = strtotime($date);
        return __(date('M', $date), 'fsww') . date(' d, Y H:i:s', $date);
    }
    
    return __('None', 'fsww');
}
