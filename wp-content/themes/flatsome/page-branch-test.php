
<?php
if(is_user_logged_in()) {
	            global $wpdb;

$current_user = wp_get_current_user();
$balance = $wpdb->get_var("SELECT amount FROM {$wpdb->prefix}fswcwallet_withdrawal_requests WHERE user_id={$current_user}");
$test=FS_WC_Wallet::encrypt_decrypt('decrypt', $balance);
echo $test ;








$str= $current_user->user_email;

$wp_user_query = new WP_User_Query(
  array(
    'search' => "*{$str}*",
    'search_columns' => array(
    'user_login',
    'user_nicename',
    'user_email',
	'ID',
  ),

) );

 $users = $wp_user_query->get_results(); 
 $num = 0;
 //$curid= $users->ID;
// echo $curid;

 echo "<table class='wp-list-table widefat striped'>";
 echo "<thead>";
   echo "<th >Branch Name  </th>";
 echo "<th>  Wallet amount  </th>";
  echo "</thead>";
 echo "<tbody>";
            echo"<tr>"; 
        echo"<td>". HI."</td>";
        echo"<td>". rasha."</td>";

         echo"</tr>";

  	  // require_once(dirname(__FILE__) . '/Wallet.php');

	   
 if (count($users) >0)
 {
    foreach ($users as $user) {
        $re[] = '<option value="' . $user->user_login . '">'.$user->user_nicename . '</option>';
           echo"<tr>"; 
        echo"<td>". $re[$num]."</td>";
		//$userwallet = Wallet::get_balance($user->ID);
	   //echo"<td>". $userwallet."</td>";
        echo"</tr>";
		$num = $num+1;
    }
	echo "</tbody>";
	echo "<tfoot>";
	echo "</tfoot>";

     echo "</table>";

 }
 

}


?>




