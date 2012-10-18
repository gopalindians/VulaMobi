<?php

/* Sascha Watermeyer - WTRSAS001
 * VulaMobi CS Honours project
 * saschawatermeyer@gmail.com */

header('Access-Control-Allow-Origin: *');  

include_once 'simple_html_dom.php';

class Resource extends CI_Controller 
{
   // public $global = array();
    
    public function __construct() 
    {
        parent::__construct();
    }
    
    public function Resource() 
    {
        echo "yo";
        //show_404();
    }
    
    //get roster for a Course
    public function site($site_id)
    {   
        //globals
        $files = array();
        
        $this->login();
        
        $result = $this->getResource($site_id);//start recursion from root

        foreach($result as $resource)
        {
            $file = array('type' => $resource['type'],
                            'title' => $resource['title'],
                            'href' => $resource['href'] );
            $files[] = $file;
        }
        //output
        $this->output
            ->set_output(json_encode(array('resources' => $files)));  
    }

    /*public function getResource($site_id, &$global = array())
    {
        $cookie = $this->session->userdata('cookie');
        $cookiepath = realpath($cookie);

        $base = "https://vula.uct.ac.za /access/content/group/";
        $url = $base . $site_id;

        //eat cookie..yum
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiepath);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);

        //create html dom object
        $html_str = str_get_html($response);
        $html = new simple_html_dom($html_str);

        if (($li = $html->find('ul li')) != null)//find all <li>
        {
            foreach($li as $val)
            {
                $folder_name = "";
                
                if($val->class == "folder")
                {    
                    //folder name
                    $folder_name = $val->children(0)->href;
                    
                    //pass through new folder name for recursion
                    $this->getResource($site_id . "/". $folder_name, $global);
                }
                if($val->class == "file")
                {   
                    $file = array('folder' => $folder_name,
                                  'title' => $val->children(0)->innertext,
                                  'href' => $base . $val->children(0)->href );
                    $global[] = $file;
                }
            }
        }
        return $global;
    }*/
    
    public function getResource($site_id, &$global = array())
    {
        $cookie = $this->session->userdata('cookie');
        $cookiepath = realpath($cookie);

        $base = "https://vula.uct.ac.za/access/content/group/";
        $url = $base . $site_id;

        //eat cookie..yum
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiepath);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);

        //create html dom object
        $html_str = str_get_html($response);
        $html = new simple_html_dom($html_str);

        if (($li = $html->find('ul li')) != null)
        {
            foreach($li as $val)
            {
                if($val->class == "folder")
                {
                    $this->getResource($site_id . "/". $val->children(0)->href, $global);
                }
                if($val->class == "file")
                {   
                    $file= array('type' => "file",
                                 'title' => $val->children(0)->innertext,
                                 'href' => $base . $val->children(0)->href );
                    $global[] = $file;
                }
            }
        }
        return $global;
    }
    
    public function page($site_id, $ext)
    {
        //echo "id: ".$site_id."</br>";
        //echo "ext: ".$ext."</br>";
        
        
        //globals
        $global = array();
        
        $this->login();
        
        $cookie = $this->session->userdata('cookie');
        $cookiepath = realpath($cookie);

        $exts = explode("&", $ext);
        $extension = "";
        foreach($exts as $ext)
        {
            $extension = $extension . '/'. $ext;
        }
        
        //echo $extension . "</br>";
        
        $base = "https://vula.uct.ac.za/access/content/group/";
        $url = $base . $site_id . '/' . $extension;

        //eat cookie..yum
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiepath);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);

        //create html dom object
        $html_str = str_get_html($response);
        $html = new simple_html_dom($html_str);

        if (($li = $html->find('ul li')) != null)
        {
            foreach($li as $val)
            {
                if($val->class == "folder")
                {
                    $temp = $val->children(0)->href;
                    $folder_name = str_replace("/", "", $temp);
                    
                    $exts = explode("/", $ext);
                    
                    $extension = "";
                    foreach($exts as $ext)
                    {
                        $extension = $extension . '&'. $ext;
                    }
                    
                    $folder = array('type' => "folder",
                                 'title' => $val->children(0)->innertext,
                                 'ext' => $extension );
                    $global[] = $folder;
                }
                if($val->class == "file")
                {   
                    $file= array('type' => "file",
                                 'title' => $val->children(0)->innertext,
                                 'href' => $base . $val->children(0)->href );
                    $global[] = $file;
                }
            }
        }
        //output
        $this->output
            ->set_output(json_encode(array('resources' => $global)));
         
    }
    
    //login Vula
    public function login() 
    {   
         $username = "wtrsas001";
         $password = "honours";
         
        //$username = $this->input->post('username');
        //$password = $this->input->post('password');
        
        $credentials = array
        (
            'username' => $username,
            'password' => $password,
        );
        $this->session->set_userdata($credentials);

        //empty username or password
        if($username==null || $password==null)
        {
            echo "Empty Username or Password";;
            die;
        }

        $auth = array(
            'eid' => $username,
            'pw' => $password,
        );

        $url = "https://vula.uct.ac.za/portal/relogin";
        
        $cookie = tempnam ("/tmp", md5($username . $this->salt()));

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $auth);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        curl_exec($curl);
        $resultStatus = curl_getinfo($curl);

        if ($resultStatus['url'] == "https://vula.uct.ac.za/portal") //if redirected it means its logged in
        {
            $newdata = array(
                  'cookie' => $cookie,
                  'logged_in' => TRUE
              );
            $this->session->set_userdata($newdata);
        }
        else
        {
            echo "Incorrect Username or Password";
            die;
        } 
    }
    
    //returns random num from 10000 - 99999
    public function salt()
    {
        $found = false;
        while(!$found)
        {
            $x = rand(0, 99999);
            if($x < 10000)
                $x = rand(0, 99999);
            else $found = true;
        }
        return (string)$x;
    }
}
?>
