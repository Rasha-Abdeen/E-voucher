<?php

@session_start();
/**
 * Data when reports are downloaded
 */
if ( empty( $_SESSION[ 'serial_numbers_csv' ] ) || empty( $_SESSION[ 'serial_numbers_filename' ] ) ) {
    print 'no export';
} else {
    $filename = $_SESSION[ 'serial_numbers_filename' ];
    $content = $_SESSION[ 'serial_numbers_csv' ];
    header( "Content-disposition: attachment; filename=$filename" );
    header( "Content-type: text/csv" );
    print $content;
}
?>