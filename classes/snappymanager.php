<?php

namespace Grav\Plugin\SnappyManager;
use Grav\Common\Grav;
use Grav\Common\Utils;
use RocketTheme\Toolbox\File\JsonFile;


class SnappyManager
{
    public $user;
    public $grav;
    public $route;
    protected $session;
    public $json_response;
    protected $post;
    protected $task;

    protected $lang;

    public function __construct(Grav $grav, $base, $location, $route)
    {
        $this->grav     = $grav;
        $this->config   = $this->grav['config'];
        $this->base     = $base;
        $this->route    = $route;
        $this->user     = $this->grav['user'];
        $this->session  = $this->grav['session'];
        $this->uri      = $this->grav['uri'];

        $this->lang = $this->grav['language'];
    }


    public function session()
    {
        return $this->session;
    }


    public function setMessage($msg, $type = 'info')
    {
        $messages = $this->grav['messages'];
        $messages->add($msg, $type);
    }


    public function messages($type = null)
    {
        $messages = $this->grav['messages'];
        return $messages->fetch($type);
    }


    public function execute($task, $post)
    {
        $this->task = $task;
        $this->post = $post;
        if (!$this->validateNonce()) {
            return false;
        }

        $method = 'task' . ucfirst($this->task);

        if (method_exists($this, $method)) {
            try {
                $success = call_user_func([$this, $method]);
            } catch (\RuntimeException $e) {
                $success = true;
                $this->setMessage($e->getMessage(), 'error');
            }
        }
        return $success;
    }


    protected function validateNonce()
    {
        if (method_exists('Grav\Common\Utils', 'getNonce')) {
            if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
                if (isset($this->post['snappy-nonce'])) {
                    $nonce = $this->post['snappy-nonce'];
                } else {
                    $nonce = $this->grav['uri']->param('snappy-nonce');
                }

                if (!$nonce || !Utils::verifyNonce($nonce, 'snappy-form')) {
                    if ($this->task == 'addmedia') {

                        $message = sprintf($this->lang->translate('PLUGIN_ADMIN.FILE_TOO_LARGE', null),
                            ini_get('post_max_size'));

                        $this->json_response = [
                            'status'  => 'error',
                            'message' => $message
                        ];

                        return false;
                    }

                    $this->setMessage($this->lang->translate('PLUGIN_ADMIN.INVALID_SECURITY_TOKEN'), 'error');
                    $this->json_response = [
                        'status'  => 'error',
                        'title' => $this->lang->translate('PLUGIN_SNAPPYGRAV.INVALID_SECURITY_TOKEN_TITLE'),
                        'message' => $this->lang->translate('PLUGIN_SNAPPYGRAV.INVALID_SECURITY_TOKEN_TEXT')
                    ];

                    return false;
                }
                unset($this->post['snappy-nonce']);
            } else {
                $nonce = $this->grav['uri']->param('snappy-nonce');
                if (!isset($nonce) || !Utils::verifyNonce($nonce, 'snappy-form')) {
                    $this->setMessage($this->lang->translate('PLUGIN_SNAPPYGRAV.INVALID_SECURITY_TOKEN'), 'error');
                    $this->json_response = [
                        'status'  => 'error',
                        'title' => $this->lang->translate('PLUGIN_SNAPPYGRAV.INVALID_SECURITY_TOKEN_TITLE'),
                        'message' => $this->lang->translate('PLUGIN_SNAPPYGRAV.INVALID_SECURITY_TOKEN_TEXT')
                    ];
                    return false;
                }
            }
        }
        return true;
    }


    public function setRedirect($path, $code = 303)
    {
        $this->redirect     = $path;
        $this->redirectCode = $code;
    }


    public function redirect()
    {
        if (!$this->redirect) {
            return;
        }

        $base           = $this->base;
        $this->redirect = '/' . ltrim($this->redirect, '/');
        $multilang      = $this->isMultilang();

        $redirect = '';
        if ($multilang) {
            $langPrefix = '/' . $this->grav['session']->admin_lang;
            if (!Utils::startsWith($base, $langPrefix . '/')) {
                $base = $langPrefix . $base;
            }
            
            if (Utils::pathPrefixedByLangCode($base) && Utils::pathPrefixedByLangCode($this->redirect)
                && substr($base,
                    0, 4) != substr($this->redirect, 0, 4)
            ) {
                $redirect = $this->redirect;
            } else {
                if (!Utils::startsWith($this->redirect, $base)) {
                    $this->redirect = $base . $this->redirect;
                }
            }

        } else {
            if (!Utils::startsWith($this->redirect, $base)) {
                $this->redirect = $base . $this->redirect;
            }
        }

        if (!$redirect) {
            $redirect = $this->redirect;
        }

        $this->grav->redirect($redirect, $this->redirectCode);
    }


    protected function taskSnappy()
    {
        $export_branch  = $this->grav['uri']->param('branch');
        $export_id      = $this->grav['uri']->param('id');
        $export_leaf    = $this->grav['uri']->param('leaf');
        $export_type    = $this->grav['uri']->param('type');
        
        $download       = $this->grav['uri']->param('download');
        $content_disp   = $this->grav['uri']->param('codisp');

        //For now only the pdf format is handled
        if( $export_type ){
            if( $export_type != 'pdf' ){
                $this->json_response = [
                    'status'    => 'error',
                    'title'     => $this->lang->translate('PLUGIN_SNAPPYGRAV.CREATION_FAILED'),
                    'message'   => strtoupper($export_type) .' '. $this->lang->translate('PLUGIN_SNAPPYGRAV.NOT_YET_MANAGED') . '<br/>' . $this->lang->translate('PLUGIN_SNAPPYGRAV.WAIT_FOR_FUTURE_UPDATES')
                ];
                return true;
            }
        }

        //The newly created document is available in the tmp folder
        if ($download) {
            $file = base64_decode(urldecode($download));
            $pdfs_root_dir = $this->grav['locator']->findResource('tmp://', true);

            if (substr($file, 0, strlen($pdfs_root_dir)) !== $pdfs_root_dir) {
                header('HTTP/1.1 401 Unauthorized');
                exit();
            }

            if($content_disp=='attach'){
                Utils::download($file, true);
            } else {
                Utils::download($file, false);
            }
        }
        
        try {
            $export = $this->makeDocument($export_leaf, $export_branch, $export_id);
        } catch (\Exception $e) {
            $this->json_response = [
                'status'    => 'error',
                'title'     => 'Error 1',
                'message'   => $this->lang->translate('PLUGIN_SNAPPYGRAV.AN_ERROR_OCCURRED') . '. ' . $e->getMessage()
            ];
            return true;
        }

        $download = urlencode(base64_encode($export));

        //http://stackoverflow.com/questions/36201927/laravel-5-2-how-to-return-a-pdf-as-part-of-a-json-response
        //$pdf = base64_encode(file_get_contents( $export )); //File contents

        $uri = rtrim($this->grav['uri']->rootUrl(true), '/');

        $url_inline = $uri . '/codisp:inline/snappytask:snappy/download:' . $download . '/snappy-nonce:' . Utils::getNonce('snappy-form');
        $url_attach = $uri . '/codisp:attach/snappytask:snappy/download:' . $download . '/snappy-nonce:' . Utils::getNonce('snappy-form');

        $btn_plugin = $this->lang->translate('PLUGIN_SNAPPYGRAV.BTN_PLUGIN');
        $log = JsonFile::instance($this->grav['locator']->findResource("log://export.log", true, true));
        $log->content([
            'time'      => time(),
            'location'  => $export
        ]);
        $log->save();

        $inline_button      = $this->lang->translate('PLUGIN_SNAPPYGRAV.INLINE');
        $attachment_button  = $this->lang->translate('PLUGIN_SNAPPYGRAV.ATTACHMENT');
        $message            = $this->lang->translate('PLUGIN_SNAPPYGRAV.YOUR_DOCUMENT_IS_READY_FOR');

        //$export_type = 'Pdf';
        $message = str_replace('%1', strtoupper($export_type), $message);

        $this->json_response = [
            'status'        => 'success',
            'url_inline'    => $url_inline,
            'url_attach'    => $url_attach,
            'message'       => $message
        ];

        return true;
    }


    protected function makeDocument($leaf, $branch, $id)
    {
        $target = $leaf;
        $bough  = $branch;
        $cid    = $id;
        $option = ( empty($target) ? 'completepdf' : '');
        
        $current_theme = $this->grav['themes']->current();
        switch ($current_theme) {
            case 'antimatter':
                $where = DS . $this->config->get('plugins.snappygrav.slug_blog');
                if(empty($where)) $where = DS . 'blog';
                $my_path='@page.children';
                break;
            case 'knowledge-base':
                $where = $this->grav['config']->get('themes.knowledge-base')['params']['articles']['root'];
                if(empty($where)) $where = DS . 'home';
                $my_path='@page.children';
                break;
            case 'learn3':
                $where = DS;
                //$my_path='@page.children';
                $my_path='@root.descendants'; //see https://learn.getgrav.org/content/collections
                break;
        }

        $pages = $this->grav['page'];
        $page_children = $pages->evaluate([$my_path => $where ]);
        
        $collection = $page_children;
        if( $current_theme != 'learn2' && $current_theme != 'learn3' ){
            $collection = $page_children->order('date', 'desc');
        }

        $parameters = [];
        $html = [];
        
        foreach ($collection as $page) {

            $slug = $page->slug();
            $id   = $page->id();

            $twig = $this->grav['twig'];
            if($slug == $target && $id == $cid || $option == "completepdf"){

                $filename = $slug;

                $parameters['theme']        = $current_theme;
                $parameters['breadcrumbs']  = $this->get_crumbs( $page );
                $parameters['bough']        = ($bough == 'yes' ? true: false);

                $html[] = $twig->processTemplate('snappygrav.html.twig', ['page' => $page, 'parameters' => $parameters]);
            }
        }
        
        if($option == 'completepdf'){
            $filename = $_SERVER['SERVER_NAME'];
        }

        // Path of created pdf
        $send_path = ROOT_DIR . 'tmp' . DS . $filename. '.pdf';
        
        // Path of the wkhtmltopdf program
        $wk_path = ROOT_DIR .'user/plugins/snappygrav/'. $this->config->get('plugins.snappygrav.wk_path');
        if( (empty($wk_path)) || (!file_exists($wk_path)) ) $wk_path = ROOT_DIR .'user/plugins/snappygrav/'. 'vendor/h4cc/wkhtmltopdf-i386/bin/wkhtmltopdf-i386';

        // Check if wkhtmltopdf-i386 is executable
        $perms = fileperms( $wk_path );
        if($perms!=33261){
            @chmod($wk_path, 0755); //33261
        }

        // If the file does not exist displays an alert and exits the procedure
        if (!file_exists($wk_path)) {
            $message = 'The file\n '.$wk_path.'\n does not exist!';
            echo '<script type="text/javascript">alert("'.$message.'");</script>';
            break;
        }

        $snappy = new \Knp\Snappy\Pdf($wk_path);

        // It takes some parameters from snappygrav.yaml file
        //$snappy->setOption('default-header', true);
        //$snappy->setOption('header-left',$matter['page_title']);
        //$snappy->setOption('header-right','[page]/[toPage]');
        //$snappy->setOption('header-spacing',5);
        //$snappy->setOption('header-line',true);
         
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
        
        //$hastitle = $this->config->get('plugins.snappygrav.title');
        //if($hastitle) $snappy->setOption('title', $matter['page_title']);
        
        $toc = $this->config->get('plugins.snappygrav.toc');
        if($toc) $snappy->setOption('toc', true);
        
        $zoom = $this->config->get('plugins.snappygrav.zoom');
        if($zoom) $snappy->setOption('zoom', $zoom);

        //echo ($snappy->getOutputFromHtml($html));
        $snappy->generateFromHtml($html, $send_path,[],true);
        return $send_path;
    }


    protected function get_crumbs( $page )
    {
        $current = $page;
        $hierarchy = array();
        while ($current && !$current->root()) {
            $hierarchy[$current->url()] = $current;
            $current = $current->parent();
        }
        $home = $this->grav['pages']->dispatch('/');
        if ($home && !array_key_exists($home->url(), $hierarchy)) {
            $hierarchy[] = $home;
        }
        $elements = array_reverse($hierarchy);
        $crumbs = array();
        foreach ($elements as $key => $crumb) {
            $crumbs[] = [ 'route' => $crumb->route(), 'title' => $crumb->title() ];
        }

        return $crumbs;
    }

}
