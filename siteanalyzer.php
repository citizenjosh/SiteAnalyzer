<?php
/**
 * Get the URL of a SiteMap and report on the LP Chat artifacts in each of its pages
 * @todo improve error handling
 */
require_once "websitescraper.php"; 
require_once "lpchatpage.php";
require_once "lpchatvariable.php";
?>

<html>
<head>
        <title>LivePerson: Website Analyzer</title>
</head>
<body>
<?php //phpinfo(); ?>    

    <h1>Website Analysis</h1>
    <form method="get">
        SiteMap URL: <input type="text" name="siteMapURL" /><br />
        <input type="submit" value="Analyze this!" />
    </form>
    <p>    . . . be patient, it can take a long time to read each page</p>
    <p><font size="-2">non-traditional SiteMap? Change the <a href="http://www.w3schools.com/xpath/xpath_syntax.asp" target="_blank">XPath expression</a> at <i>websitescraper.php:57</i></font></p>
    
<?php
if (isset($_GET['siteMapURL'])) {
    /* avoid XSS */
    $siteMapURL = htmlentities($_GET['siteMapURL']);
    
    //$websitescraper = new websitescraper( $siteMapURL );
    /* watch out for exceptions */
    try{
        $websitescraper = new websitescraper($siteMapURL);
        $pages = $websitescraper->get_pages();
    }  catch (Exception $e){
        echo '<p id="exception">Caught exception: ',  $e->getMessage(), "</p>";
//    } finally {
//        echo "";
    }
?>

    <h2>Pages</h2>
    <table border="1">
        <thead>
            <tr>
                <th>mtagconfig</th>
                <th>lpUnit</th>
                <th>lpLanguage</th>
                <th>lpAddVars</th>
                <th>lpSendData</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="5">Pages: <?php echo count($pages) ?></td>
            </tr>
        </tfoot>
        <tbody>
<?php
        // for each page in the sitemap
        foreach ($pages as $key => $page){
?>
            <tr valign="top">
                <td colspan="5"><?php echo $page->get_page_url() ?></td>
            </tr>
            <tr valign="top">
                <td><?php echo $page->get_mtagconfigurl() ?></td>
                <td><?php echo $page->get_lpunit() ?></td>
                <td><?php echo $page->get_lplanguage() ?></td>
                <td>
<?php
            $lpaddvarss = $page->get_lpaddvarss();
            if (count($lpaddvarss)>0){
?>
                    <ul>
<?php 
                foreach ($lpaddvarss as $value){ 
?>
                        <li><?php  echo $value; ?></li>
<?php 
                } 
?>
                    </ul>
<?php
            } else {
?>
<?php
                echo "none";
?>
<?php
            }
?>
                </td>           
                <td>
<?php
            $lpsenddatas = $page->get_lpsenddatas();
            if (count($lpsenddatas)>0){
?>
                    <ul>
<?php 
                foreach ($lpsenddatas as $value){ 
?>
                        <li><?php  echo $value; ?></li>
<?php 
                } 
?>
                    </ul>
<?php
            } else {
?>
<?php
                echo "none";
?>
<?php
            }
?>
                </td>
            </tr>
<?php
}
?>
        </tbody>
    </table>

            
<?php    
} else {
    error_log("die now cause siteMapURL="+"not set", 0);
    echo "<!-- siteMapURL=not set-->";
}
?>

</body>
</html>
