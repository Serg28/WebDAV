<?php
namespace dvcarrot\WebDAV;

/**
 * Class Client
 * @package dvcarrot \WebDAV
 */
class Client
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var resource
     */
    private $curl;

    /**
     * Sets variables
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Kill staff
     */
    public function __destruct()
    {
        if ($this->curl)
            curl_close($this->curl);
    }
    
    
  public function get_mime_type($file) { 
   $file=pathinfo($file);
   $ext = $file['extension'];
   // Расширение в нижний регистр 
   $ext=trim(strtolower($ext)); 
   switch($ext) {
       case 'pdf' : $ctype = 'application/pdf'; break;
       case 'zip' : $ctype = 'application/zip'; break;
       case 'doc' : $ctype = 'application/msword'; break;
       case 'xls' : $ctype = 'application/vnd.ms-excel'; break;
       case 'gif' : $ctype = 'image/gif'; break;
       case 'png' : $ctype = 'image/png'; break;
       case 'jpeg':
       case 'jpg' : $ctype = 'image/jpg'; break;
       case 'mp3' : $ctype = 'audio/mpeg'; break;
       case 'wav' : $ctype = 'audio/x-wav'; break;
       case 'mpeg':
       case 'mpg' :
       case 'mpe' : $ctype = 'video/mpeg'; break;
       case 'mov' : $ctype = 'video/quicktime'; break;
       case 'avi' : $ctype = 'video/x-msvideo'; break;
       default    : $ctype = 'application/octet-stream';
    }
    if ($ext!='' && isset($ctype)) { 
        // Если есть такой MIME-тип, то вернуть его 
        return $ctype; 
    } 
    else { 
        // Иначе вернуть дефолтный MIME-тип 
        return "application/force-download"; 
    }     
}
    
    

    /**
     * @param $file
     * @return Result
     */
    public function get($file)
    {
        return $this->request(
            $this->config->hostname . $file,
            array(''),
            'GET'
        );
    }
    
    /**
     * @param $file
     * @return File Download
     */
    public function get_file($file)
    {
      $mimetype = $this->get_mime_type($file);
      $result = $this->request(
            $this->config->hostname . $file,
            array(''),
            'GET'
        );
      if ($result) {
      header('Content-Disposition: attachment; filename='.basename($file));
      header('Content-Type: '.$mimetype);
      echo $result->response;
      }
    }

    /**
     * @param $fileOut
     * @param $fileIn
     * @return Result
     */
    public function put($fileOut, $fileIn)
    {
        return $this->request(
            $this->config->hostname . $fileOut,
            array('Content-type: application/octet-stream'),
            'PUT',
            $fileIn
        );
    }

    /**
     * @param $folder
     * @param $depth
     * @return Result
     */
    public function propfind($folder, $depth = 1)
    {
        return $this->request(
            $this->config->hostname . $folder,
            array('Depth: ' . $depth),
            'PROPFIND'
        );
    }

    /**
     * @param $folder
     * @return Result
     */
    public function mkcol($folder)
    {
        return $this->request(
            $this->config->hostname . $folder,
            array(),
            'MKCOL'
        );
    }

    /**
     * Executes queries to the cloud
     * @param string $url
     * @param array $headers
     * @param string $method
     * @param string $file
     * @return Result
     * @access private
     * @final
     */
    final private function request($url, $headers = array(), $method = '', $file = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERPWD, implode(':', array($this->config->username, $this->config->password)));

        if (empty($this->config->authtype) === false) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, $this->config->authtype);
        }
        if (empty($headers) === false) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if (empty($method) === false) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }
        if (is_null($file) === false) {
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILE, fopen($file, 'r'));
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
        }

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $result = new Result($statusCode, $response);

        return $result;
    }
}
