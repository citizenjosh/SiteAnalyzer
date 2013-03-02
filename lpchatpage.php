<?php

/**
 * A webpage in a website tagged for LP Chat
 *
 * @author	jrosenthal@LivePerson.com
 * @version	1.0
 *
 * @todo	allow for mis-coded pages where there is more than one value for lpUnit, lpLanguage, and mtagconfig.js	
 * @todo	reduce processing time by passing the $node array by reference rather than doing a $domxpath->query("//script") each time
 */
class lpchatpage {

    /**
     * URL of page
     *
     * @var	string	page's URL
     */
    private $page_url;

    /**
     * URL of mtagconfig.js file
     *
     * @var	string	URL of mtagconfig.js file
     */
    private $mtagconfig_url;

    /**
     * Site ID
     *
     * @var	string	Site ID of the page's given mtagconfig.js file
     * @see lpchatpage::$mtagconfig_url
     */
    private $site_id;

    /**
     * lpUnit
     *
     * @var	string	page's given lpUnit
     * @todo	test for >1 given value
     */
    private $lpunit;

    /**
     * lpLanguage
     *
     * @var	string	page's given lpLanguage
     * @todo	test for >1 given value
     */
    private $lplanguage;

    /**
     * Array of lpAddVars(<scope>,<name>,<value>)
     * 
     * @var	array	lpAddVars calls on the webpage
     */
    private $lpaddvarss = array();

    /**
     * Array of lpSendData(<scope>,<name>,<value>)
     * 
     * @var	array	lpSendData calls on the webpage
     */
    private $lpsenddatas = array();

    /**
     * XPath of HTML page
     *
     * @var	object	XPath representing the page
     */
    private $domxpath;

    /**
     * Given a URL, understand it from a LPSN POV
     *
     * @param	string	URL of the webpage
     * @todo	check the Site ID given at each mtagconfig.js
     */
    function __construct($webpage_url) {
        $this->set_page_url($webpage_url);
        $this->set_DOMXPath($this->get_page_url());

        /* read page */
        /* extract mtagconfig link */
        $this->set_mtagconfigurl($this->extract_mtagconfigurl($this->get_DOMXPath()));

        /* extract lpUnit value */
        $this->set_lpunit($this->extract_lpunit($this->get_DOMXPath()));

        /* extract lpLanguage value */
        $this->set_lplanguage($this->extract_lplanguage($this->get_DOMXPath()));

        /* extract all lpAddVars arguments */
        $this->set_lpaddvars($this->extract_lpaddvars($this->get_DOMXPath()));

        /* extract all lpSendData arguments */
        $this->set_lpsenddata($this->extract_lpsenddata($this->get_DOMXPath()));
    }

    /* extractors */

    /**
     * Extract the lpUnit given on a webage
     *
     * @param	string	text in which to look for the value of lpUnit
     * @return	string	the lpUnit value
     */
    function extract_lpunit($domxpath) {
        $lpunit = "not found";
        $text = null;
        foreach ($domxpath->query("//script") as $node) {
            $text = $node->nodeValue;
            if (stripos($text, "lpUnit") > 0) {
                /* needle is surrounded by "quotes" */
                if (($needleEnd = stripos($text, 'lpunit="') + 8) > 8) {
                    $lpunit = substr($text, $needleEnd, strpos($text, "\"", $needleEnd) - $needleEnd);
                }
                /* needle is surrounded by 'apostrophes' */ 
                else if (($needleEnd = stripos($text, "lpunit='") + 8) > 8) {
                    $lpunit = substr($text, $needleEnd, strpos($text, "'", $needleEnd) - $needleEnd);
                }
                break;
            }
        }
        return $lpunit;
    }

    /**
     * Extract the lpLanguage given on a webage
     *
     * @param	object	DOM XPath of webpage
     * @return	string	the lpLanguage value
     */
    function extract_lplanguage($domxpath) {
        $lplanguage = "not found";
        $text = null;
        foreach ($domxpath->query("//script") as $node) {
            $text = $node->nodeValue;
            if (stripos($text, "lpLanguage") > 0) {
                /* needle is surrounded by "quotes" */
                if (($needleEnd = stripos($text, 'lplanguage="') + 12) > 12) {
                    $lplanguage = substr($text, $needleEnd, strpos($text, '"', $needleEnd) - $needleEnd);
                }
                /* needle is surrounded by 'apostrophes' */ 
                else if (($needleEnd = stripos($text, "lpLanguage='") + 12) > 12) {
                    $lplanguage = substr($text, $needleEnd, strpos($text, "'", $needleEnd) - $needleEnd);
                }
                break;
            }
        }
        return $lplanguage;
    }

    /**
     * Extract the mtagconfigurl given on a webage
     *
     * @todo		check for more than 1 mtagconfig.js file
     *
     * @param	object	DOM XPath in which to look for the URL of the mtagconfig.js file
     * @return	string	the URL of the mtagconfig.js file
     */
    function extract_mtagconfigurl($domxpath) {
        $mtagconfigurl = "not found";
        $src = null;
        foreach ($domxpath->query("//script") as $node) {
            $src = $node->getAttribute("src");
            if (strpos($src, "mtagconfig.js") > 0) {
                $mtagconfigurl = $src;
                break;
            }
        }
        return $mtagconfigurl;
    }

    /**
     * Extract lpAddVars given on a webage
     *
     * @param	object	DOM XPath of webpage
     * @return	array	lpAddVars values
     */
    function extract_lpaddvars($domxpath) {
        $lpaddvars = array();

        /* check in script areas */
        $text = null;
        foreach ($domxpath->query("//script") as $node) {
            $text = $node->nodeValue;
            /* add an lpAddVars object from the text wherever the case-insensitive string "lpAddVars" is found */
            $offset = 0;
            while (($offset = stripos($text, "lpaddvars", $offset) + 10) > 10) {
                $lpaddvars[] = lpchatvariable::MakeFromSignature(substr($text, $offset, strpos($text, ")", $offset) - $offset));
            }
        }

        /* check each element's attributes */
        // unnecessary since lpAddVars only works during the initial layout		

        return $lpaddvars;
    }

    /**
     * Extract lpSendData given on a webage
     *
     * @param	object	DOM XPath of webpage
     * @return	array	lpSendData values
     */
    function extract_lpsenddata($domxpath) {
        $lpsenddata = array();

        /* check in script areas */
        $text = null;
        $offset = null;
        foreach ($domxpath->query("//script") as $node) {
            $text = $node->nodeValue;
            /* add an lpSendData object from the text wherever the case-insensitive string "lpSendData" is found */
            while (($offset = stripos($text, "lpsenddata", $offset) + 11) > 11) {
                $lpsenddata[] = lpchatvariable::MakeFromSignature(substr($text, $offset, strpos($text, ")", $offset) - $offset));
            }
            $offset = 0;
        }

        /* check each element's attributes */
        $text = null;
        foreach ($domxpath->query('//*[@*[contains(translate(.,"LPSENDDATA","lpsenddata"),"lpsenddata")]]') as $node) {
            foreach ($node->attributes as $attrName => $attrNode) {
                $text = $attrNode->value;

                if (stripos($text, "lpsenddata") !== FALSE) {   // only strings that contain "lpsenddata"
                    //$matches = [lL][pP][sS][eE][nN][dD][dD][aA][tT][aA]\(['\"][a-zA-Z0-9]*['\"],['\"][a-zA-Z0-9]*['\"],['\"][a-zA-Z0-9]*['\"]\)",$text, $matches);
                    foreach (explode(";", $text) as $key => $value) {
                        if (stripos($value, "lpsenddata") !== FALSE) {  // only substrings that contain "lpsenddata"
                            $lpsenddata[] = lpchatvariable::MakeFromSignature($value);
                        }
                    }
                }
            }
        }
        return $lpsenddata;
    }

    /* setters */

    function set_page_url($page_url) {
        $this->page_url = $page_url;
    }

    function set_mtagconfigurl($mtagconfig_url) {
        $this->mtagconfig_url = $mtagconfig_url;
    }

    function set_lpunit($lpunit) {
        $this->lpunit = $lpunit;
    }

    function set_lplanguage($lplanguage) {
        $this->lplanguage = $lplanguage;
    }

    /**
     * @param	object	array of lpAddVars objects
     */
    function set_lpaddvars($lpaddvars) {
        $this->lpaddvarss = $lpaddvars;
    }

    /**
     * @param	object	an lpAddVars object
     */
    function add_lpaddvars($lpAddVars) {
        $this->lpaddvarss[] = $lpAddVars;
    }

    /**
     * @param	object	array of lpsenddata objects
     */
    function set_lpsenddata($lpsenddata) {
        $this->lpsenddatas = $lpsenddata;
    }

    /**
     * @param	object	an lpsenddata object
     */
    function add_lpsenddata($lpsenddata) {
        $this->lpsenddatas[] = $lpsenddata;
    }

    function set_DOMXPath($url) {
        $dom = new DOMDocument();
        @$dom->loadHTMLFile($url);
        $this->dOMXPath = new DOMXPath($dom);
    }

    /* getters */

    /**
     * @return pointer to a DOM XPath object
     */
    function &get_DOMXPath() {
        return $this->dOMXPath;
    }

    function get_page_url() {
        return $this->page_url;
    }

    function get_lpunit() {
        return $this->lpunit;
    }

    function get_lplanguage() {
        return $this->lplanguage;
    }

    function get_lpaddvarss() {
        return $this->lpaddvarss;
    }

    function get_lpsenddatas() {
        return $this->lpsenddatas;
    }

    function get_mtagconfigurl() {
        return $this->mtagconfig_url;
    }

}

?>