<?php
// OPTIONS - PLEASE CONFIGURE THESE BEFORE USE!

$yourEmail = "sheko.elanteko@gmail.com"; // the email address you wish to receive these mails through
$yourWebsite = "Shaker Hamdi Website"; // the name of your website
$thanksPage = 'thanks.html'; // URL to 'thanks for sending mail' page; leave empty to keep message on the same page 
$maxPoints = 4; // max points a person can hit before it refuses to submit - recommend 4
$requiredFields = "name,email,message"; // names of the fields you'd like to be required as a minimum, separate each field with a comma


// DO NOT EDIT BELOW HERE
$error_msg = array();
$result = null;

$requiredFields = explode(",", $requiredFields);

function clean($data) {
    $data = trim(stripslashes(strip_tags($data)));
    return $data;
}
function isBot() {
    $bots = array("Indy", "Blaiz", "Java", "libwww-perl", "Python", "OutfoxBot", "User-Agent", "PycURL", "AlphaServer", "T8Abot", "Syntryx", "WinHttp", "WebBandit", "nicebot", "Teoma", "alexa", "froogle", "inktomi", "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory", "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot", "crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz");

    foreach ($bots as $bot)
        if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
            return true;

    if (empty($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] == " ")
        return true;
    
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isBot() !== false)
        $error_msg[] = "No bots please! UA reported as: ".$_SERVER['HTTP_USER_AGENT'];
        
    // lets check a few things - not enough to trigger an error on their own, but worth assigning a spam score.. 
    // score quickly adds up therefore allowing genuine users with 'accidental' score through but cutting out real spam :)
    $points = (int)0;
    
    $badwords = array("adult", "beastial", "bestial", "blowjob", "clit", "cum", "cunilingus", "cunillingus", "cunnilingus", "cunt", "ejaculate", "fag", "felatio", "fellatio", "fuck", "fuk", "fuks", "gangbang", "gangbanged", "gangbangs", "hotsex", "hardcode", "jism", "jiz", "orgasim", "orgasims", "orgasm", "orgasms", "phonesex", "phuk", "phuq", "pussies", "pussy", "spunk", "xxx", "viagra", "phentermine", "tramadol", "adipex", "advai", "alprazolam", "ambien", "ambian", "amoxicillin", "antivert", "blackjack", "backgammon", "texas", "holdem", "poker", "carisoprodol", "ciara", "ciprofloxacin", "debt", "dating", "porn", "link=", "voyeur", "content-type", "bcc:", "cc:", "document.cookie", "onclick", "onload", "javascript");

    foreach ($badwords as $word)
        if (
            strpos(strtolower($_POST['message']), $word) !== false || 
            strpos(strtolower($_POST['name']), $word) !== false
        )
            $points += 2;
    
    if (strpos($_POST['message'], "http://") !== false || strpos($_POST['message'], "www.") !== false)
        $points += 2;
    if (isset($_POST['nojs']))
        $points += 1;
    if (preg_match("/(<.*>)/i", $_POST['message']))
        $points += 2;
    if (strlen($_POST['name']) < 3)
        $points += 1;
    if (strlen($_POST['message']) < 15 || strlen($_POST['message'] > 1500))
        $points += 2;
    if (preg_match("/[bcdfghjklmnpqrstvwxyz]{7,}/i", $_POST['message']))
        $points += 1;
    // end score assignments

    foreach($requiredFields as $field) {
        trim($_POST[$field]);
        
        if (!isset($_POST[$field]) || empty($_POST[$field]) && array_pop($error_msg) != "Please fill in all the required fields and submit again.\r\n")
            $error_msg[] = "Please fill in all the required fields and submit again.";
    }

    if (!empty($_POST['name']) && !preg_match("/^[a-zA-Z-'\s]*$/", stripslashes($_POST['name'])))
        $error_msg[] = "The name field must not contain special characters.\r\n";
    if (!empty($_POST['email']) && !preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', strtolower($_POST['email'])))
        $error_msg[] = "That is not a valid e-mail address.\r\n";
    if (!empty($_POST['url']) && !preg_match('/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', $_POST['url']))
        $error_msg[] = "Invalid website url.\r\n";
    
    if ($error_msg == NULL && $points <= $maxPoints) {
        $subject = "Message from your Website";
        
        $message = "You received this message through your website: \n\n";
        foreach ($_POST as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $subval) {
                    $message .= ucwords($key) . ": " . clean($subval) . "\r\n";
                }
            } else {
                $message .= ucwords($key) . ": " . clean($val) . "\r\n";
            }
        }
        $message .= "\r\n";
        $message .= 'IP: '.$_SERVER['REMOTE_ADDR']."\r\n";
        $message .= 'Browser: '.$_SERVER['HTTP_USER_AGENT']."\r\n";
        $message .= 'Points: '.$points;

        if (strstr($_SERVER['SERVER_SOFTWARE'], "Win")) {
            $headers   = "From: $yourEmail\r\n";
        } else {
            $headers   = "From: $yourWebsite <$yourEmail>\r\n"; 
        }
        $headers  .= "Reply-To: {$_POST['email']}\r\n";

        if (mail($yourEmail,$subject,$message,$headers)) {
            if (!empty($thanksPage)) {
                header("Location: $thanksPage");
                exit;
            } else {
                $result = 'Your mail was successfully sent.';
                $disable = true;
            }
        } else {
            $error_msg[] = 'Your mail could not be sent this time. ['.$points.']';
        }
    } else {
        if (empty($error_msg))
            $error_msg[] = 'Your mail looks too much like spam, and could not be sent this time. ['.$points.']';
    }
}
function get_data($var) {
    if (isset($_POST[$var]))
        echo htmlspecialchars($_POST[$var]);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow">
    <meta name="format-detection" content="telephone=no">
    <meta name="description" content="Shaker Hamdi">
    <title>Shaker Hamdi</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="images/shakerfav.ico" rel="shortcut icon" type="image/x-icon">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <a href="#" class="backToTop"><i class="icon">&#xf106;</i></a>
    <div class="container">
        <div class="globalWrapper">

            <?php
            if (!empty($error_msg)) {
                echo '<p class="error">'. implode("<br />", $error_msg) . '<i class="dismess">x</i></p>';
            }
            if ($result != NULL) {
                echo '<p class="success">'. $result . '<i class="dismess">x</i></p>';;
            }
            ?>

            <header class="mainHeader">
                <h1><a href="index.html">Shaker Hamdi</a></h1>
            </header>
            <!-- logo -->

            <nav class="mainNav">
                <ul>
                    <li><a href="#about">About</a></li>
                    <li><a href="#portfolio">Portfolio</a></li>
                    <li><a href="#contactMe">Contact</a></li>
                </ul>
            </nav>
            <!-- mainNav -->

            <section class="about" id="about">
                <header class="sectionHeader">
                    <h3>About Me</h3>
                    <hr>
                </header>

                <article class="content">
                    <div class="personalImage">
                        <img src="images/myPhoto.jpg" alt="My Photo">
                    </div>
                    <!-- personalImage -->

                    <div class="text">
                        <p>Salam all.<br>My  name is Shaker Hamdi Ahmed. I’m a UI Designer / Developer living in Cairo, Egypt. I have 5 years of experience in UI design and Front-End devleloping. I worked on a variety of projects (Web and Mobile), and for a number of companies both big and small.</p>

                        <div class="socialLinks">
                            <ul>
                                <li><a href="https://www.facebook.com/Shaker.Hamdi" class="facebook" target="_blank"><i class="icon">&#xf09a;</i></a></li>
                                <li><a href="https://plus.google.com/u/0/102143771762786053235/posts" class="google" target="_blank"><i class="icon">&#xf0d5;</i></a></li>
                                <li><a href="https://www.linkedin.com/profile/view?id=AAIAAATMQbcBQl3QIlplY5x58giKH8abEFLCi_o&trk=nav_responsive_tab_profile_pic" class="linkedIn" target="_blank"><i class="icon">&#xf0e1;</i></a></li>
                            </ul>
                        </div>
                        <!-- socialLinks -->
                    </div>
                </article>
                <!-- content -->
            </section>
            <!-- about -->

            <section class="portfolio" id="portfolio">
                <header class="sectionHeader">
                    <h3>My Portfolio</h3>
                    <hr>
                </header>

                <article class="content">
                    <div class="project">
                        <div class="projectThumb">
                            <img src="images/hepca/projectThumb.jpg" alt="project Thumb">
                        </div>

                        <div class="projectDesc">
                            <h2>HEPCA</h2>
                            <p>HEPCA (Hurghada Environmental Protection and Conservation Association) is an ongoing project I'm working on right now for my current company MMD (<a href="http://moselaymd.com/" target="_blank">Moselay Media Development</a>). I'm responsible for the UI Design and UI Development of the project, and the project is currently a work on progress ...</p>
                            <a href="hepca.html" class="more"><span class="text">Details</span><span class="bg"></span></a>
                            <!-- <a href="#projectDetails1" class="more" data-effect="mfp-3d-unfold"><span class="text">Details</span><span class="bg"></span></a> -->
                        </div>

                        <!-- <div id="projectDetails1" class="projectDetails mfp-with-anim mfp-hide">
                            <div class="imageSlider">
                                <ul class="owlCarousel">
                                    <li><img src="images/hepca/01.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/02.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/03.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/04.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/05.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/06.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/07.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/08.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/09.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/10.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/11.jpg" alt="Screenshot"></li>
                                    <li><img src="images/hepca/12.jpg" alt="Screenshot"></li>
                                </ul>
                            </div>
                        </div> -->
                    </div>
                    <!-- project -->

                    <div class="project">
                        <div class="projectThumb">
                            <img src="images/akhbar/projectThumb.jpg" alt="project Thumb">
                        </div>

                        <div class="projectDesc">
                            <h2>Akhbar El Youm</h2>
                            <p>Akhbar El Youm is a major project I did for my current company MMD (<a href="http://moselaymd.com/" target="_blank">Moselay Media Development</a>). I was responsible for the UI Development for both the desktop and the mobile version of the website. The project is alwyas updating, so you might see some changes in the future or perhaps a complete re-design.</p>
                            <a href="http://akhbarelyom.com/" class="more" target="_blank"><span class="text">Details</span><span class="bg"></span></a>
                        </div>
                    </div>
                    <!-- project -->

                    <div class="project">
                        <div class="projectThumb">
                            <img src="images/mmd_logo/projectThumb.jpg" alt="project Thumb">
                        </div>

                        <div class="projectDesc">
                            <h2>MMD Logo</h2>
                            <p>This the my current company's logo, MMD (<a href="http://moselaymd.com/" target="_blank">Moselay Media Development</a>). I was in charge of creating a new logo for the company, and here it is. It's currently in use in every digital or print material the company produce.</p>
                            <a href="http://moselaymd.com/" class="more" target="_blank"><span class="text">Details</span><span class="bg"></span></a>
                        </div>
                    </div>
                    <!-- project -->

                    <div class="project">
                        <div class="projectThumb">
                            <img src="images/aqarmap/projectThumb.jpg" alt="project Thumb">
                        </div>

                        <div class="projectDesc">
                            <h2>Aqarmap</h2>
                            <p>The famous <a href="https://aqarmap.com/" target="_blank">Aqarmap.com</a>. I worked for Aqarmap for quite sometime, and during this period I was in charge of the re-design and front-end developing of their website (among other things). It was one of the best projects I've worked on in my career :)</p>
                            <a href="https://aqarmap.com/" class="more" target="_blank"><span class="text">Details</span><span class="bg"></span></a>
                        </div>
                    </div>
                    <!-- project -->

                    <div class="project">
                        <div class="projectThumb">
                            <img src="images/barsoom/projectThumb.jpg" alt="project Thumb">
                        </div>

                        <div class="projectDesc">
                            <h2>Barsoom</h2>
                            <p>Barsoom is a PSD template I did a while back for <a href="http://themeforest.net/" target="_blank">themeforest.net</a> as a news / magazine template. I'm proud of how this design came out. But due to lack of time back then I sold the UI Development rights to another themeforest.net author.</p>
                            <a href="http://themeforest.net/item/barsoom-12-psd-magazine-news-and-blog-template/3324512" class="more" target="_blank"><span class="text">Details</span><span class="bg"></span></a>
                        </div>
                    </div>
                    <!-- project -->

                    <div class="project">
                        <div class="projectThumb">
                            <img src="images/kora_score/projectThumb.jpg" alt="project Thumb">
                        </div>

                        <div class="projectDesc">
                            <h2>Kora Live Score</h2>
                            <p>Kora Live Score is a mobile app design I did for a jordanian company called "knockbook".</p>
                            <a href="kora_score.html" class="more"><span class="text">Details</span><span class="bg"></span></a>
                        </div>
                    </div>
                    <!-- project -->

                    <div class="project">
                        <div class="projectThumb">
                            <img src="images/shopesta/projectThumb.jpg" alt="project Thumb">
                        </div>

                        <div class="projectDesc">
                            <h2>Shopesta</h2>
                            <p>Shopesta is a PSD shopping template that I did for themeforest.net.<br>The HTML live version of this template is coming soon :)</p>
                            <a href="http://themeforest.net/item/shopesta-ecommerce-psd-template/6645502" class="more" target="_blank"><span class="text">Details</span><span class="bg"></span></a>
                        </div>
                    </div>
                    <!-- project -->

                    
                </article>
                <!-- content -->
            </section>
            <!-- portfolio -->

            <section class="contactMe" id="contactMe">
                <header class="sectionHeader">
                    <h3>Contact Me</h3>
                    <hr>
                </header>
                
                <article class="content">
                    <form action="<?php echo basename(__FILE__); ?>" method="post">
                        <noscript>
                                <p><input type="hidden" name="nojs" id="nojs" /></p>
                        </noscript>

                        <label class="name">
                            <input type="text" name="name" placeholder="Your Name ..." value="<?php get_data("name"); ?>">
                        </label>

                        <label class="mail">
                            <input type="text" name="email" placeholder="Your E-mail ..." value="<?php get_data("email"); ?>">
                        </label>
                        
                        <label class="message">
                            <textarea name="message" placeholder="Your Message ..."><?php get_data("message"); ?></textarea>
                        </label>
                        
                        <div class="submit">
                            <button type="submit" name="submit" <?php if (isset($disable) && $disable === true) echo ' disabled="disabled"'; ?>><span class="text">Send</span><span class="bg"></span></button>
                        </div>
                    </form>
                </article>
                <!-- content -->
            </section>
            <!-- contactMe -->

            <footer>
                <div class="socialLinks">
                    <ul>
                        <li><a href="https://www.facebook.com/Shaker.Hamdi" class="facebook" target="_blank"><i class="icon">&#xf09a;</i></a></li>
                        <li><a href="https://plus.google.com/u/0/102143771762786053235/posts" class="google" target="_blank"><i class="icon">&#xf0d5;</i></a></li>
                        <li><a href="https://www.linkedin.com/profile/view?id=AAIAAATMQbcBQl3QIlplY5x58giKH8abEFLCi_o&trk=nav_responsive_tab_profile_pic" class="linkedIn" target="_blank"><i class="icon">&#xf0e1;</i></a></li>
                    </ul>
                </div>
                <p>&copy; 2015. All rights reserved, shakerhamdi.net</p>
            </footer>
            <!-- footer -->
        </div>
        <!-- globalWrapper -->
    </div>
    <!-- container -->
    <!-- For Live Reload to work -->
    <script src="http://localhost:35729/livereload.js?snipver=1" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="js/script.js" type="text/javascript"></script>
</body>

</html>
