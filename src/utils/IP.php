<?php
namespace ys\utils;

class IP
{
    
    public static function getHostname()
    {
        static $hostname = null;
        if ($hostname === null) {
            $hostname = gethostname();
        }
    
        return $hostname;
    }
    
    /**
     * 获取所有本机IP
     *
     * @param bool|true $include_local 是否包含本地IP
     *
     * @return array|string
     */
    public static function getLocalIP($include_local = true)
    {
        $preg = '/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/';
    
        static $ips = [];
    
        if (empty($ips)) {
            if ($include_local) {
                $ips[] = '127.0.0.1';
            }
    
            //获取操作系统为win2000/xp、win7的本机IP真实地址
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                exec("ipconfig", $out, $stats);
                if (!empty($out)) {
                    foreach ($out as $row) {
                        if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) {
                            $tmpIp = explode(":", $row);
                            if (preg_match($preg, trim($tmpIp[1]))) {
                                $ips[] = trim($tmpIp[1]);
                            }
                        }
                    }
                }
            } elseif (stripos(PHP_OS, 'LINUX') !== false) {
                //获取操作系统为linux类型的本机IP真实地址
                exec("ifconfig | fgrep 'inet addr:' | fgrep -v '127.0.0.' | fgrep -v '2.0.1.' | fgrep -v '169.254.' | fgrep -v '192.168.2.' | awk '{print $2}' | awk -F ':' '{print $2}'", $ips, $stats);
            } elseif (stripos(PHP_OS, 'DARWIN') !== false) {
                //获取操作系统为mac类型的本机IP真实地址
                //exec("ifconfig | fgrep 'inet ' | fgrep -v '127.0.0.' | fgrep -v '2.0.1.' | fgrep -v '169.254.' | fgrep -v '192.168.2.' | awk '{print $2}' | sort -n", $ips, $stats);
                exec("ifconfig | fgrep 'inet ' | fgrep '192.' | fgrep -v '2.0.1.' | fgrep -v '169.254.' | fgrep -v '192.168.2.' | awk '{print $2}' | sort -n", $ips, $stats);
            }
    
            if ($include_local && count($ips) == 1) {
                $ips = [];
            }
        }
    
        return $ips;
    }
}
