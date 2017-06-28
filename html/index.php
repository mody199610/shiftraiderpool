<?php
date_default_timezone_set("UTC");
$date = date("Y-m-d");

$author     = "@LepetitJan";
$title      = "";
$subTitle   = "";
$baseUrl    = "https://pool.seatrips.nl";
$coin       = "SHIFT";

$apiHost            = "http://wallet.shiftnrg.nl:9305";
$publicKey          = "a0434a01e1b2a3ed388752af5d60e5344086c94978407ab85bdb56122239f4c1";
$explorer           = "https://explorer.shiftnrg.nl";

$getDelegateStatus  = file_get_contents("$apiHost/api/delegates/get?publicKey=$publicKey");
$getDelegateStatus  = json_decode($getDelegateStatus, true);
    $rate           = $getDelegateStatus['delegate']['rate'];
    $approval       = $getDelegateStatus['delegate']['approval'];

$database = "../payouts.sqlite3";
$db = new SQLite3($database) or die("[ SQLITE3 ] Unable to open database");
    $getInfo    = $db->query("SELECT balance,rewards FROM stats WHERE balance IS NOT NULL AND date='$date'");
    $getInfo    = $getInfo->fetchArray();
    $getWeight  = $getInfo['balance'];
    $getRewards = $getInfo['rewards'];

    $getDaily   = $db->query("SELECT * FROM stats WHERE balance IS NOT NULL AND balance !=0 AND forged IS NOT NULL AND forged !='' ORDER BY id DESC LIMIT 2");

if(isset($_GET['address'])){
    $address        = $_GET['address'];

    $checkExists    = $db->query("SELECT count(*) as count FROM voters WHERE address='$address'");
    $checkExists    = $checkExists->fetchArray();

    $addressInfo    = $db->query("SELECT * FROM voters WHERE address='$address' ORDER BY id ASC");
}else{
    $address = NULL;
    $checkExists = 0;
    $addressInfo = NULL;
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<head>
    <title><?php echo $title; ?></title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="apple-touch-fullscreen" content="YES" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $title." - ".$subTitle; ?>">
    <meta name="author" content="<?php echo $author; ?>">

    <!-- OmG! -->
    <meta property="og:type" content="article"/>
    <meta property="og:title" content="<?php echo $title; ?>"/>
    <meta property="og:url" content="<?php echo $baseUrl; ?>"/>
    <meta property="og:image" content="<?php echo $baseUrl; ?>/images/logo.png"/>
    <meta property="og:site_name" content="<?php echo $title; ?>"/>
    <meta property="og:description" content="<?php echo $subTitle; ?>" />

<link rel="apple-touch-icon" sizes="57x57" href="favicon/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="favicon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="favicon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="favicon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="favicon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="favicon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="favicon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="favicon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="favicon/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="favicon/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
<link rel="manifest" href="favicon/manifest.json">
<meta name="msapplication-TileColor" content="#CCCCCC">
<meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png">
<meta name="theme-color" content="#CCCCCC">

    <link href='//fonts.googleapis.com/css?family=Lato:300,400,300italic,400italic' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
    <!-- Global CSS -->
    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/bootstrap/css/dataTables.bootstrap.min.css" />
    <!-- Plugins CSS -->
    <link rel="stylesheet" href="assets/plugins/font-awesome/css/font-awesome.css">
    <!-- Theme CSS -->
    <link id="theme-style" rel="stylesheet" href="assets/css/styles.css">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Begin Cookie Consent plugin by Silktide - http://silktide.com/cookieconsent -->
    <script type="text/javascript">
        window.cookieconsent_options = {"message":"This website uses cookies to ensure you get the best experience on our website","dismiss":"Got it!","theme":"dark-bottom"};
    </script>

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/1.0.9/cookieconsent.min.js"></script>
    <!-- End Cookie Consent plugin -->

</head>

<body>
    <!-- ******HEADER****** -->
    <header class="header">
        <div class="container">
            <a href="/">
                <img class="profile-image img-responsive pull-left" src="assets/images/logo.png" alt="<?php echo $title; ?>" />
            </a>
            <div class="profile-content pull-left">
                <h1 class="name"><?php echo $title; ?></h1>
                <h2 class="desc"><?php echo $subTitle; ?></h2>
                <ul class="social list-inline">
                </ul>
            </div><!--//profile-->
        </div><!--//container-->
    </header><!--//header-->

    <div class="container sections-wrapper">
        <div class="row">
            <div class="primary col-md-8 col-sm-12 col-xs-12">

            <?php if($address === NULL){?>
                <section class="about section">
                    <div class="section-inner">
                        <h2 class="heading">Information</h2>
                        <div class="content">
                            <p>
                                This is a <?php echo $coin; ?> Pool set up by @seatrips. You will receive a share of the forged blocks of the pool delegate (seatrips). How much? That depends on the voting weight of the address you use to vote seatrips.
                            </p>

                            <b>Why?</b>
                            <p>
                                <?php echo $coin; ?> has a dPOS code base like Lisk which means that 101 delegates (nodes in the <?php echo $coin; ?> network) are responsible for forging the blocks instead of miners. Only if your delegate is in the top 101, you will forge blocks and therefore earn <?php echo $coin; ?>.
                            </p><p>
                                This pool is set up so people without a delegate can also earn some <?php echo $coin; ?> without having to do the hard work of keeping a node online.
                            </p>

                            <b>How?</b>
                            <p>
                                In order to receive a share of our forged blocks, you only have to vote for delegate "seatrips" once:<br />
                                <a href="https://explorer.shiftnrg.nl/address/2675385658327038858S" target="_blank">https://explorer.shiftnrg.nl/address/2675385658327038858S</a>
                            </p>
                            <p>
                                The payout script runs once every day around midnight (UTC). That script will check the current voters and add them to the database. So it may take a while before you see updated statistics on your address page. Your vote is visible almost immediately on the homepage though!
                            </p>
                            <p>
                               <b>50% of the Forged Shift will be payed to the voters</b>
                            </p>
                            <p>
                                <b>You will be paid when you reach the minimum of 1 <?php echo $coin; ?>!</b>
                            </p>
                        </div><!--//content-->
                    </div><!--//section-inner-->
                </section><!--//section-->

                <section class="latest section">
                    <div class="section-inner">
                        <h2 class="heading">Voters</h2>
                        <div class="content">

                        <?php
                            ob_start();
                            $getVoters      = passthru("curl -s -k -X GET '$apiHost/api/delegates/voters?publicKey=$publicKey'");
                            $getVoters      = ob_get_contents();
                            $getVoters      = json_decode($getVoters, true)['accounts'];
                            ob_end_clean();
                        ?>

                            <p>
                                <table cellspacing="2" cellspacing="0" class="table payments" id="voters">
                                <thead>
                                <tr>
                                    <th>Delegate name</th><th>Address</th><th>Balance</th><th>Weight</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    //krsort($getVoters);

                                    $balance = 0;
                                    foreach($getVoters as $voter){
                                        $balance = ($balance + $voter['balance']);
                                    }

                                    foreach($getVoters as $voter){
                                        echo "<tr>";
                                            if(!empty($voter['username'])){
                                                echo "<td>".$voter['username']."</td>";
                                            }else{
                                                echo "<td>-</td>";
                                            }
                                            echo "<td><a href=\"/?address=".$voter['address']."\">".$voter['address']."</a></td>";
                                            echo "<td>".number_format(($voter['balance'] / 100000000), 2,",",".")."</td>";
                                            echo "<td>".number_format((($voter['balance'] / $balance) * 100), 2,",",".")."%</td>";
                                        echo "</tr>";
                                    }
                                ?>
                                </tbody>
                                </table>
                            </p>

                        </div><!--//content-->
                    </div><!--//section-inner-->
                </section><!--//section-->
            <?php } ?>

            <?php if($address != NULL){ ?>
                <section class="about section">
                    <div class="section-inner">
                        <h2 class="heading"><?php echo $address; ?></h2>
                        <div class="content">
                            <?php
                                if($checkExists['count'] < 1){
                                    echo '<div align="center"><b>Address it not in our database (yet)!</b><br />Payout script runs once a day and will add your adress.</div>';
                                }else{
                            ?>
                            <div id="chart_div" style="width: 100%; height: 500px;"></div>
                            <p>
                                <table cellspacing="2" cellspacing="0" class="table payments" id="payments">
                                <thead>
                                <tr>
                                    <th>Date</th><th>Share</th><th><?php echo $coin; ?></th><th>Paid</th><th>Transaction ID</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    while ($info = $addressInfo->fetchArray(1)) {
                                    echo "<tr>";
                                        echo "<td>".$info['date']."</td>";
                                        echo "<td>".number_format($info['share'], 2)."%</td>";
                                        echo "<td>".number_format(($info['amount'] / 100000000), 2,",",".")." ".$coin."</td>";
                                        echo "<td>".$info['paid']."</td>";
                                        echo '<td><a href="'.$explorer.'/tx/'.$info['transid'].'" target="_blank">'.$info['transid'].'</td>';
                                    echo "</tr>";
                                    }
                                }
                                ?>
                                </tbody>
                                </table>
                            </p>
                        </div><!--//content-->
                    </div><!--//section-inner-->
                </section><!--//section-->
            <?php } ?>

            </div><!--//primary-->
            <div class="secondary col-md-4 col-sm-12 col-xs-12">
                 <aside class="blog aside section">
                    <div class="section-inner">
                        <h2 class="heading">Voter statistics</h2>
                        <p>
                            <form method="get">
                                <input type="text" class="form-control" id="address" name="address" placeholder="Enter your <?php echo $coin; ?> address and hit return" minlength="15" required="" aria-required="true">
                            </form>
                        </p>
                    </div><!--//section-inner-->
                </aside><!--//section-->

                 <aside class="skills aside section">
                    <div class="section-inner">
                        <h2 class="heading">Pool statistics</h2>
                        <div class="content">
                            <div class="skillset">

                                <div class="item">
                                    <h3 class="level-title">Rank<span class="level-label">#<?php echo $rate; ?></span></h3>
                                    <div class="level-bar">
                                        <div class="level-bar-inner" data-level="<?php echo ((51 - $rate) / 51) * 100; ?>%"></div>
                                    </div><!--//level-bar-->
                                </div><!--//item-->

                                <div class="item">
                                    <h3 class="level-title">Approval<span class="level-label"><?php echo $approval; ?>%</span></h3>
                                    <div class="level-bar">
                                        <div class="level-bar-inner" data-level="<?php echo $approval; ?>%">
                                        </div>
                                    </div><!--//level-bar-->
                                </div><!--//item-->

                            </div>
                        </div><!--//content-->
                    </div><!--//section-inner-->
                </aside><!--//section-->

                <aside class="skills aside section">
                    <div class="section-inner">
                        <h2 class="heading">Daily statistics</h2>
                        <div class="content">
                            <div class="skillset">

                            <?php while ($daily = $getDaily->fetchArray()) { ?>

                                <div class="item featured text-center">
                                    <h3 class="title"><a href="javascript:void(0)"><?php echo $daily['date']; ?></a></h3>
                                    <p class="summary">
                                        Total forged: <?php echo number_format(($daily['rewards'] / 100000000), 2,",","."); ?> <?php echo $coin; ?> <br />
                                        Total weight: <?php echo number_format(($daily['balance'] / 100000000), 2,",","."); ?> <?php echo $coin; ?>
                                    </p>
                                </div><!--//item-->

                                <hr class="divider" />

                            <?php } ?>

                            </div>
                        </div><!--//content-->
                    </div><!--//section-inner-->
                </aside><!--//section-->

                <aside class="info aside section">
                    <div class="section-inner">
                        <h2 class="heading sr-only">Information</h2>
                        <div class="content">
                            <ul class="list-unstyled">
                                <li><i class="fa fa-map-marker"></i><span class="sr-only">Location:</span>Amsterdam, NL</li>
                                <li><i class="fa fa-envelope-o"></i><span class="sr-only">Email:</span><a href="mailto:seatrips@shiftnrg.nl">seatrips@shiftnrg.nl</a></li>
                                <li><i class="fa fa-link"></i><span class="sr-only">Website:</span><a href="https://pool.seatrips.nl/" target="_blank">https://pool.seatrips.nl/</a></li>
                            </ul>
                        </div><!--//content-->
                    </div><!--//section-inner-->
                </aside><!--//aside-->

            </div><!--//secondary-->
        </div><!--//row-->
    </div><!--//masonry-->

    <!-- ******FOOTER****** -->
    <footer class="footer">
        <div class="container text-center">
                <small class="copyright">Brought to you with <i class="fa fa-heart"></i> by <a href="https://twitter.com/seatrips" target="_blank"><?php echo $author; ?></a> and <a href="https://twitter.com/seatrips" target="_blank">@seatrips</a></small>
        </div><!--//container-->
    </footer><!--//footer-->

    <!-- Javascript -->
    <script type="text/javascript" src="assets/plugins/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="assets/plugins/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- Data tables -->
    <script type="text/javascript" src="assets/plugins/bootstrap/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="assets/plugins/bootstrap/js/dataTables.bootstrap.min.js"></script>
    <!-- custom js -->
    <script type="text/javascript" src="assets/js/main.js"></script>
    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Date', '<?php echo $coin; ?>'],
          <?php
            if($checkExists['count'] >= 1){
                while ($info = $addressInfo->fetchArray(1)) {
                    echo "['".$info['date']."', ".number_format(round(($info['amount'] / 100000000),0)-0.01,2,'.','')."],";
//number_format(($info['amount'] / 100000000), 2,",",".")."],";
                }
            }
          ?>
        ]);

        var options = {
          title: 'Payments progress',
          hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0}
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
    <!-- Initiate DataTable() -->
    <script>
      $(document).ready(function() {
        $('#voters').DataTable({
                "order": [[ 3, 'desc' ]]
        });
        $('#payments').DataTable({
                "order": [[ 0, 'desc' ]]
        });
      } );
    </script>
</body>
</html>
