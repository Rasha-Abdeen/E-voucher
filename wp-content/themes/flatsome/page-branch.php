
<?php


$current_user = wp_get_current_user();
//echo 'Username: ' . $current_user->user_login . '<br />';
//echo 'User email: ' . $current_user->user_email . '<br />';
//echo 'User first name: ' . $current_user->user_firstname . '<br />';
//echo 'User last name: ' . $current_user->user_lastname . '<br />';
//echo 'User display name: ' . $current_user->display_name . '<br />';
//echo 'User ID: ' . $current_user->ID . '<br />';


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

 echo "<table>";
   echo "<th >Branch Name  </th>";
 echo "<th>  Wallet amount  </th>";

 echo  "<tbody>"; 
 if (count($users) > 0  ){
	   require_once(dirname(__FILE__) . '/Wallet.php');

    foreach ($users as $user) {
        $re[] = '<option value="' . $user->user_login . '">'.$user->user_nicename . '</option>';
           echo"<tr>"; 
        echo"<td>". $re[$num]."</td>";
		$userwallet = Wallet::get_balance($user->ID);
	   echo"<td>". $userwallet."</td>";


        echo"</tr>";
		$num = $num+1;
    }
  echo  "</tbody>";
   echo "</table>";
		
  

 }

?>

