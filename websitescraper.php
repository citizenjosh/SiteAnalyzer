<?php

/**
* Retain the LP Chat artifacts of all pages in a given sitemap
*
* @author	jrosenthal@LivePerson.com
* @version	1.0
*
* @todo		throw errors in try/catch blocks to simplify layout	
*/
class websitescraper {

	/**
	* Location of the sitemap
	*/
	private $sitemap_url;
	/**
	* Pages in the website
	* Array of LPSNPage objects
	*/
	private $pages = array();

	/**
	* Provide the URL of the sitemap
	*/
	function __construct($sitemap_url) {
            $this->set_sitemap_url($sitemap_url);
            $this->extract_pages();
	}
	
	/* setters */
        /**
         * Set the URL of the sitemap.xml file
         * @param   String  sitemap_url
         * @todo    get a local copy to speed re-crawling of a website ... though reading each page takes the most time
         */
	function set_sitemap_url($sitemap_url){
            /* Validate the URL */
            if( strlen($sitemap_url)<=7 || stripos($sitemap_url,"http://")>0 || stripos($sitemap_url,"https://")>0 ){
            //if (!file_exists($sitemap_url)){
                //errorlog("websitescraper"+"->"+"set_sitemap_url("+$sitemap_url+") unreachable. "+"check php.ini:extension=php_openssl.dll");
                throw new Exception("malformed URL: ".$sitemap_url);
            }

            /* set the variable */
            $this->sitemap_url = $sitemap_url;
	}
	/**
	* Get page URLs from the well-formed SiteMap XML file
        * @link http://www.sitemaps.org/protocol.html
        * 
	*/
        private function extract_pages(){
            $dom = new DOMDocument();
            @$dom->loadHTMLFile($this->get_sitemap_url());
            $dOMXPath = new DOMXPath($dom);
            foreach ($dOMXPath->query("//urlset/url/loc") as $node) {
                $this->add_page($node->nodeValue);
            }
	}
	/**
	* Add webpage to set of lpchatpages
	*/
	protected function add_page($webpage_url){
		error_log("websitescraper->"+"add_page(".$webpage_url.")");
		$lpchatpage = new lpchatpage($webpage_url);
		$this->pages[]=$lpchatpage;
	}
	/* getters */
	/**
	* @return array
	*/
	function get_pages(){
		return $this->pages;
	}
        /**
         * @return string URL of the sitemap.xml file
         */
        function get_sitemap_url(){
            return $this->sitemap_url;
        }
}
?>