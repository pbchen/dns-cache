<?php

/**
 * 定义DNS缓存的前缀
 */
namespace DnsCache;
define('CURL_DNS_KEY_PREFIX', 'curl_dns_cache_');

class NotifyCurl{

        /**
         * 默认UserAgent
        */
        var $user_agent = 'Notification robot';
        /**
         * curl 对象
         */
        var $curl;
        /**
         * 自定义header
         */
        var $headers = array();
        /**
         * DNS搜索失败重试次数
         */
        var $dns_search_limit = 5;
        /**
         * DNS Cache的过期时间
         */
        var $dns_cache_timeout = 1800;

        /**
         * 默认构造方法
         */
        function __construct(){

                $this->curl = curl_init();
                curl_setopt($this->curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true); //不直接输出
                curl_setopt($this->curl, CURLOPT_USERAGENT,$this->user_agent);
                curl_setopt($this->curl, CURLOPT_HEADER, false); // 过滤HTTP头
                curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
        }

        /**
         * 析构方法，释放资源
         */
        function __destruct() {
                curl_close($this->curl);
        }

        /**
         * Curl的配置项
         * @param string $opt 配置项名称
         * @param object $val 配置项值
         */
        public function option($opt, $val){
                curl_setopt($this->curl, $opt, $val);
        }

        /**
         * 发送url的通知
         * @param string $url URL地址
         * @param integer $timeout 默认超时时间
         * @param integer $forward 默认重定向次数
         * @param boolean $custom_dns 释放使用自定义的DNS来解析发送数据
         * @return string Url的返回值
         */
        public function send($url, $timeout=5, $forward = 5, $custom_dns=FALSE){

                if($custom_dns){
                        $url_info = $this->get_send_url($url);
                	curl_setopt($this->curl, CURLOPT_URL, $url_info['url']); // 配置url
                	if($url_info['host']){
                                curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Host: '.$url_info['host']));
                        }
                } else {
                	curl_setopt($this->curl, CURLOPT_URL, $url); // 配置url
		            }
		            curl_setopt($this->curl, CURLOPT_TIMEOUT, $timeout); //超时设置
                $data = curl_exec($this->curl);
                $curl_info = curl_getinfo($this->curl);
		//var_dump($curl_info);
                //30x 需要自动转向
                $go_forward = preg_match('/^30/',$curl_info['http_code']);
                if($go_forward && $forward>0 && isset($curl_info['redirect_url'])){
                        $data = $this->send($curl_info['redirect_url'], $timeout, $forward-1, $custom_dns);
                //20x 成功返回
                } else if($go_forward || preg_match('/^20/',$curl_info['http_code'])){
                        if ($data===''){
                                $data = TRUE;
                        }
                } else {
                        $data = FALSE;  
                }
                return $data;

        }

        /**
         * TODO: 支持user:pass 不支持自动登录的url
         * 解析host，自定义DNS
         * @param string $url URL
         * @return array 包含 url 和 host 的关联数组，host = FALSE表示是IP的url
         */
        function get_send_url($url){
                //format  scheme://user:pass@host/path?query#fragment'
                $uri = parse_url($url);
                $ret = array();
                if(!preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/',
                        $uri['host'])){
                        $ip = $this->get_ip_cache($uri['host']);
                        if(!isset($uri['path'])){ $uri['path']='';}
                        if(!isset($uri['query'])){ 
                                $uri['query']=''; 
                        } else {
                                $uri['query']='?'.$uri['query'];
                        }
                        $ret['host'] = $uri['host'];
                        $ret['url'] = $uri['scheme']. '://'. $ip 
                                        . $uri['path'].$uri['query'];
                } else {
                        $ret['host'] = FALSE;
                        $ret['url'] = $url;
                }
		
                return  $ret;
        }

        /**
         * 带有缓存的获取host对应的ip信息
         * @param string $host 主机名信息
         * @return string IP地址信息
         */
        function get_ip_cache($host){

                $ip = '';
                //使用apc作为DNS的缓存
                if(function_exists('apc_fetch')){
                        $ip = apc_fetch(CURL_DNS_KEY_PREFIX.$host);
                }

                if(!$ip){
                        $ip = $this->get_ip($host);

                        //使用apc作为DNS的缓存
                        if(function_exists('apc_store') 
                                && $ip != $host){
                                apc_store(CURL_DNS_KEY_PREFIX.$host, $ip, 
                                        $this->dns_cache_timeout);
                        }                        
                }

                return $ip;
                
        }

        /**
         * 无缓存的获取host对应的ip信息
         * @param string $host 主机名信息
         * @return string IP地址信息
         */
        function get_ip($host){
          
                $ip = gethostbyname($host);
                if( $ip === $host ){
                        $limit = $this->dns_search_limit;
                        while ($limit>0 && $ip === $host) {
                                $ip = gethostbyname($host);
                                $limit--;
                        }
                }
    
                return $ip;
        }


}
