<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

use Grav\Common\Page\Page;
use Grav\Common\Page\Collection;
use Grav\Common\Uri;
use Grav\Common\Taxonomy;

use Knp\Snappy\Pdf;

#use Grav\Common\Twig;

class SnappyGravPlugin extends Plugin
{
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    /**
     * Activate plugin if path matches to the configured one.
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }
        /** @var Uri $uri */
        $uri = $this->grav['uri'];
        $route = $this->config->get('plugins.snappygrav.route');

        $params = $uri->params();
        $len = strlen($params);
        $pdf="";
        if($len > 0){
            $pdf = substr($params, -4);
        }
        if($pdf == ":pdf" ){
            $this->enable([
                'onPageProcessed' => ['onPageProcessed', 0],
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
            ]);
        }
    }

    /**
     * Add
     *
     * @param Event $event
     */
    public function onPageProcessed(Event $event)
    {
        // Get the page header
        $page = $event['page'];
        $taxonomy = $page->taxonomy();

        // track month taxonomy in "jan_2015" format:
        if (!isset($taxonomy['archives_month'])) {
            $taxonomy['archives_month'] = array(strtolower(date('M_Y', $page->date())));
        }

        // track year taxonomy in "2015" format:
        if (!isset($taxonomy['archives_year'])) {
            $taxonomy['archives_year'] = array(date('Y', $page->date()));
        }

        // set the modified taxonomy back on the page object
        $page->taxonomy($taxonomy);
    }

    /**
     * Set needed variables to display the taxonomy list.
     */
    public function onTwigSiteVariables()
    {
        $uri = $this->grav['uri'];
        
        $taxonomy_map = $this->grav['taxonomy'];
        $pages = $this->grav['pages'];
        $start_date = time();
        $archives = array();
        $filters = (array) $this->config->get('plugins.snappygrav.filters');
        $operator = $this->config->get('plugins.snappygrav.filter_combinator');
        //$this->grav['debugger']->addMessage($filters);
        if (count($filters) > 0) {
            $collection = new Collection();
            $collection->append($taxonomy_map->findTaxonomy($filters, $operator)->toArray());
            $collection = $collection->order($this->config->get('plugins.snappygrav.order.by'), $this->config->get('plugins.snappygrav.order.dir'));
            $date_format = $this->config->get('plugins.snappygrav.date_display_format');

            $uri_params = $uri->params();
            $uri_params = str_replace(':pdf', "", $uri_params);
            $uri_params = str_replace('/', "", $uri_params);

            //$this->grav['debugger']->addMessage($collection);

            $vars = array();
            
            foreach ($collection as $page) {

                $start_date = $page->date() < $start_date ? $page->date() : $start_date;
                $archives[date($date_format, $page->date())][] = $page;

                $page_route = $page->route();
                $pieces = explode("/", $page_route);
                $len = count($pieces);
                $target = $pieces[$len-1];

                /*
                http://stackoverflow.com/questions/22105433/snappy-wkhtmltopdf-wrapper-send-generated-html-file-to-browser
                -   Save PDF of URL $input to file $output
                    generate($input, $output, array $options = array(), $overwrite = false)

                -   Save PDF of HTML $html to file $output
                    generateFromHtml($html, $output, array $options = array(), $overwrite = false)

                -   Return PDF of URL $input as string
                    getOutput($input, array $options = array())

                -   Return PDF of HTML $html as string
                    getOutputFromHtml($html, array $options = array())
                */
                if($uri_params == $target){

                    $page_title = $page->title();
                    $page_serial = $page->date();
                    $page_date = date("d-m-Y",$page_serial);
                    $page_header_author = "";
                    if(isset( $page->header()->author )) $page_header_author = $page->header()->author;
                    $page_content = $page->content();
                    //$page_content = str_replace('{.float_right}', "", $page_content);
                    //$page_content = str_replace('{.float_left}', "", $page_content);
                    //$page_content = str_replace('{.float_center}', "", $page_content);
                    //$page_content = str_replace(' style ', "", $page_content);
                    $page_slug = $page->slug();

                    $wk_path = $this->config->get('plugins.snappygrav.wk_path');
                    if($wk_path=="") $wk_path = 'usr/bin/wkhtmltopdf-i386';

                    // If the file does not exist displays an alert and exits the procedure
                    if (!file_exists($wk_path)) {
                        $message = 'The file\n '.$wk_path.'\n does not exist!';
                        echo '<script type="text/javascript">alert("'.$message.'");</script>';
                        break;
                    }
                    $snappy = new Pdf($wk_path);

                    // It takes some parameters from snappygrav.yaml file
                    $grayscale = $this->config->get('plugins.snappygrav.grayscale');
                    if($grayscale) $snappy->setOption('grayscale', $grayscale);

                    $margin_bottom = $this->config->get('plugins.snappygrav.margin_bottom');
                    if($margin_bottom) $snappy->setOption('margin-bottom', $margin_bottom);

                    $margin_left = $this->config->get('plugins.snappygrav.margin_left');
                    if($margin_left) $snappy->setOption('margin-left', $margin_left);

                    $margin_right = $this->config->get('plugins.snappygrav.margin_right');
                    if($margin_right) $snappy->setOption('margin-right', $margin_right);

                    $margin_top = $this->config->get('plugins.snappygrav.margin_top');
                    if($margin_top) $snappy->setOption('margin-top', $margin_top);

                    $orientation = $this->config->get('plugins.snappygrav.orientation');
                    if($orientation == "Portrait" || $orientation == "Landscape") {
                        $snappy->setOption('orientation', $orientation);
                    }

                    $page_size = $this->config->get('plugins.snappygrav.page_size');
                    if($page_size) $snappy->setOption('page-size', $page_size);

                    $hastitle = $this->config->get('plugins.snappygrav.title');
                    if($hastitle) $snappy->setOption('title', $page_title);
                       
                    $zoom = $this->config->get('plugins.snappygrav.zoom');
                    if($zoom) $snappy->setOption('zoom', $zoom);

                    $html = "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'><h2><center>". $page_title ."</center></h2><center><b>". $page_header_author ."</b></center><br/><center><b>". $page_date ."</b></center>".$page_content;
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="'.$page_slug.'.pdf"');
                    echo $snappy->getOutputFromHtml($html);
                }
            }
        }
    }
}
