<?php
namespace Inchoo\Hello\Api;
 
interface CMSPageInterface
{

    /**
     * Returns greeting pages
     *
     * @api
     * @param string $page_url .
     * @return string Page data.
     */
    public function getPageData($page_url);
  
}
?>