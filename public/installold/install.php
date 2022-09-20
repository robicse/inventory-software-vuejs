<!DOCTYPE html>
<html>
<head>
    <!-- Required meta tags -->
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    <!--Import font from google -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,400i,500,500i,700,700i' rel='stylesheet'>

    <!--Import Google Icon Font-->
    <link href='https://fonts.googleapis.com/icon?family=Material+Icons' rel='stylesheet'>
    <!--Import materialize.css-->
    <link type='text/css' rel='stylesheet' href='../materialize-sass/css/materialize.css' media='screen,projection'/>
    <!--Import install-style.css-->
    <link rel='stylesheet' href='../css/install-style.css'>
    <!--Let browser know website is optimized for mobile-->
    <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
    <title>Install Page</title>

</head>
<div class='row margin-fix'>
    <div class='col s12 m12 l12 xl8 offset-xl2'>
        <div class='card'>
            <div class='card-content'>
                <div class="row">
                    <h5 class='bluish-text center-align'>Gain Booking Installation</h5>
                </div>
                <div id='preInstallPage' class='showContent'>
                    <div class="row margin-fix">
                        <div class="row">
                            <div class="row">
                                <div class="col s12">
                                    <p>1. Please configure your PHP settings to match requirements listed bellow</p>
                                    <table class='bordered responsive-table'>
                                        <thead>
                                        <tr>
                                            <th>PHP settings</th>
                                            <th>Current settings</th>
                                            <th>Required settings</th>
                                            <th class="center-align">Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <?php echo '
                                            <td>PHP version</td>
                                            <td> '.phpversion().' </td>
                                            <td> 7.1.3 +</td>
                                            <td class="center-align"><i class="material-icons green-text">check_box</i></td>
                                        '?>
                                        </tr>
                                        <tr>
                                            <td>PHP version</td>
                                            <td>5.5.17</td>
                                            <td>5.4+</td>
                                            <td class="center-align"><i class="material-icons green-text">check_box</i></td>
                                        </tr>
                                        <tr>
                                            <td>PHP version</td>
                                            <td>5.5.17</td>
                                            <td>5.4+</td>
                                            <td class="center-align"><i class="material-icons red-text">indeterminate_check_box</i></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="row">
                                <div class="col s12">
                                    <p>2. Please make sure the PHP extensions listed bellow are installed</p>
                                    <table class='bordered responsive-table'>
                                        <thead>
                                        <tr>
                                            <th>PHP settings</th>
                                            <th>Current settings</th>
                                            <th>Required settings</th>
                                            <th class="center-align">Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>PHP version</td>
                                            <td>5.5.17</td>
                                            <td>5.4+</td>
                                            <td class="center-align"><i class="material-icons green-text">check_box</i></td>
                                        </tr>
                                        <tr>
                                            <td>PHP version</td>
                                            <td>5.5.17</td>
                                            <td>5.4+</td>
                                            <td class="center-align"><i class="material-icons green-text">check_box</i></td>
                                        </tr>
                                        <tr>
                                            <td>PHP version</td>
                                            <td>5.5.17</td>
                                            <td>5.4+</td>
                                            <td class="center-align"><i class="material-icons red-text">indeterminate_check_box</i></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="row">
                                <div class="col s12">
                                    <p>3. Please make sure you have set the correct permissions on the files list bellow</p>
                                    <table class='bordered responsive-table'>
                                        <thead>
                                        <tr>
                                            <th>PHP settings</th>
                                            <th>Current settings</th>
                                            <th>Required settings</th>
                                            <th class="center-align">Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>PHP version</td>
                                            <td>5.5.17</td>
                                            <td>5.4+</td>
                                            <td class="center-align"><i class="material-icons green-text">check_box</i></td>
                                        </tr>
                                        <tr>
                                            <td>PHP version</td>
                                            <td>5.5.17</td>
                                            <td>5.4+</td>
                                            <td class="center-align"><i class="material-icons green-text">check_box</i></td>
                                        </tr>
                                        <tr>
                                            <td>PHP version</td>
                                            <td>5.5.17</td>
                                            <td>5.4+</td>
                                            <td class="center-align"><i class="material-icons red-text">indeterminate_check_box</i></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <div class='right-align' id='next-btn'>
                                    <button class='btn'>Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div id='installPage' class='hideContent'>
                    <div class='row margin-fix'>
                        <form id="install_form" method="post" action="install.php">
                            <div class='row'>
                                <div class="col s12">
                                    <h6>Database settings</h6>
                                </div>
                                <div class='input-field col s12'>
                                    <input id='host' type='text' name="dbdatabase" class='validate' required>
                                    <label for='host' data-error='Enter host'>Host</label>
                                </div>
                                <div class='input-field col s12'>
                                    <input id='database_name' type='text' name="database" class='validate' required>
                                    <label for='database_name' data-error='Enter database name'>Database name</label>
                                </div>
                                <div class='input-field col s12'>
                                    <input id='database_user' type='text' name="username" class='validate' required>
                                    <label for='database_user' data-error='Enter database user name'>Database user name</label>
                                </div>
                                <div class='input-field col s12'>
                                    <input id='database_password' type='password' name="password" class='validate' required>
                                    <label for='database_password' data-error='Enter database password'>Database password</label>
                                </div>
                            </div>
                            <div class='row'>
                                <div class="col s12">
                                    <h6>Admin information</h6>
                                </div>
                                <div class='input-field col s12'>
                                    <input id='admin_user_name' type='text' name="first_name" class='validate' required>
                                    <label for='admin_user_name' data-error='Enter admin user name'>Admin user name</label>
                                </div>
                                <div class='input-field col s12'>
                                    <input id='login_email' type='email' name="email" class='validate' required>
                                    <label for='login_email' data-error='Enter login email'>Login email</label>
                                </div>
                                <div class='input-field col s12'>
                                    <input id='login_password' type='password' name="password" class='validate' required>
                                    <label for='login_password' data-error='Enter login password'>Login password</label>
                                </div>
                            </div>
                            <div class='row'>
                                <div class="col s12">
                                    <h6>Purchase code</h6>
                                </div>
                                <div class='input-field col s12'>
                                    <input id='purchase_code' type='text' name="purchase_code" class='validate' required>
                                    <label for='purchase_code' data-error='Enter purchase code'>Purchase code</label>
                                </div>
                            </div>
                            <div class='row'>
                                <div class="col s12">
                                    <a href='#' id='back-btn' class='btn'>Back</a>
                                    <button class='btn right' type='submit'>Install</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
/*$myfile = fopen(".../.env", "w") or die("Unable to open file!");
$txt = 'DB_DATABASE='.$post['dbdatabase'];
fwrite($myfile, $txt);

fclose($myfile);
*/?>
<!-- JavaScript -->
<script type='text/javascript' src='../js/jquery-3.3.1.min.js'></script>
<script type='text/javascript' src='../materialize-sass/js/bin/materialize.min.js'></script>
<script>

$('#next-btn').click(function(){
    $('#preInstallPage').removeClass('showContent').addClass('hideContent');
    $('#installPage').removeClass('hideContent').addClass('showContent');
});
    $('#back-btn').click(function(){
        $('#installPage').removeClass('showContent').addClass('hideContent');
        $('#preInstallPage').removeClass('hideContent').addClass('showContent');
    });


</script>

</body>
</html>