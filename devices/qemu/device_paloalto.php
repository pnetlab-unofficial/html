<?php

/**
 * 
 * @author LIN 
 * @copyright pnetlab.com
 * @link https://www.pnetlab.com/
 * 
 */

class device_paloalto extends device_qemu
{

    function __construct($node)
    {
        parent::__construct($node);
    }

    public function createEthernets($quantity)
    {
        $ethernets = [];
  $first = 0 ;
                 /*FOR CUSTOM COSNOLES */
        if ($this->console == 'http' || $this->console_2nd == 'http' )
                {
                    $n = 'Mgmt http';
                    try {
                        $ethernets[$quantity + $first] = new Interfc( $this, array('name' => $n, 'type' => 'ethernet',), $quantity + $first);
                    } catch (Exception $e) {
                        error_log(date('M d H:i:s ') . 'ERROR: ' . $GLOBALS['messages'][40020]);
                        error_log(date('M d H:i:s ') . (string) $e);
                        return 40020;
                    } 
                    $first  ++;
                }
        if ($this->console == 'https' || $this->console_2nd == 'https' )
                {
                    $n = 'Mgmt https';

                    try {
                        $ethernets[$quantity + $first] = new Interfc( $this, array('name' => $n, 'type' => 'ethernet', ), $quantity + $first);
                    } catch (Exception $e) {
                        error_log(date('M d H:i:s ') . 'ERROR: ' . $GLOBALS['messages'][40020]);
                        error_log(date('M d H:i:s ') . (string) $e);
                        return 40020;
                    } $first  ++;
                }
                /////////////
        if ($this->console == 'ssh' || $this->console_2nd == 'ssh' )
                {
                   $n = 'Mgmt ssh';
                   try {
                        $ethernets[$quantity + $first] = new Interfc( $this, array('name' => $n, 'type' => 'ethernet',), $quantity + $first);
                   }
                     catch (Exception $e) {
                        error_log(date('M d H:i:s ') . 'ERROR: ' . $GLOBALS['messages'][40020]);
                        error_log(date('M d H:i:s ') . (string) $e);
                        return 40020;
                    } $first  ++;
                }
                //////////////
        if ($this->console == 'rdp' || $this->console_2nd == 'rdp' )
                {
                    $n = 'Mgmt rdp';
                    try {
                        $ethernets[$quantity + $first] = new Interfc( $this, array('name' => $n, 'type' => 'ethernet',), $quantity + $first);
                    } catch (Exception $e) {
                        error_log(date('M d H:i:s ') . 'ERROR: ' . $GLOBALS['messages'][40020]);
                        error_log(date('M d H:i:s ') . (string) $e);
                        return 40020;
                    } $first  ++;
             }
                //////////////
        if ($this->console == 'rdp-tls' || $this->console_2nd == 'rdp-tls' )
                {
                    $n = 'Mgmt rdp-tls';
                    try {
                        $ethernets[$quantity+ $first] = new Interfc( $this, array('name' => $n, 'type' => 'ethernet',), $quantity + $first);
                    } catch (Exception $e) {
                        error_log(date('M d H:i:s ') . 'ERROR: ' . $GLOBALS['messages'][40020]);
                        error_log(date('M d H:i:s ') . (string) $e);
                        return 40020;
                    }  $first  ++;
         }
        /*FOR CUSTOM COSNOLES */
      
        for ($i = 0; $i < $quantity; $i++) {
              if($i == 0 && $this->first_nic != ''){
                    $flag = ' -device '.$this->first_nic.',netdev=net' . $i . ',mac=' . incMac($this->createFirstMac(), $i);
                }else{
                    $flag = ' -device %NICDRIVER%,netdev=net' . $i . ',mac=' . incMac($this->createFirstMac(), $i);
                }
                $flag .= ' -netdev tap,id=net' . $i . ',ifname=vunl' . $this->getSession() . '_' . $i . ',script=no';

            if (!isset($this->ethernets[$i])) {

                if ($first > 0) {
                    
                        $n = 'eth1/' . ($i + $first ) ;           
                }
                else {

                        if ($i == 0) {
                            $n = 'mgmt';            // Interface name
                        } 
                        else {
                            $n = 'eth1/' . $i;        // Interface name
                        }
                }

                try {
                    $ethernets[$i] = new Interfc( $this, array('name' => $n, 'type' => 'ethernet', 'flag' => $flag), $i);
                } catch (Exception $e) {
                    error_log(date('M d H:i:s ') . 'ERROR: ' . $GLOBALS['messages'][40020]);
                    error_log(date('M d H:i:s ') . (string) $e);
                    return false;
                }
            } else {
                $ethernets[$i] = $this->ethernets[$i];
            }
            // Setting CMD flags (virtual device and map to TAP device)

        }

        $this->ethernets = $ethernets;
        return $this->ethernets;
    }

    

    public function prepare()
    {
        $result = parent::prepare();
        if ($result != 0) return $result;

        if (is_file($this->getRunningPath() . '/startup-config') && !is_file($this->getRunningPath() . '/.configured')) {
            exec('mkdir ' . $this->getRunningPath() . '/cdrom');
            exec('mkdir ' . $this->getRunningPath() . '/cdrom/config');
            exec('mkdir ' . $this->getRunningPath() . '/cdrom/license');
            exec('mkdir ' . $this->getRunningPath() . '/cdrom/software');
            exec('mkdir ' . $this->getRunningPath() . '/cdrom/content');
            copy($this->getRunningPath() . '/startup-config',  $this->getRunningPath() . '/cdrom/config/bootstrap.xml');
            preg_match_all('/(\s*<system>.*<\/system>)/ms', file_get_contents($this->getRunningPath() . '/startup-config'), $xmlinit);
            $xml = simplexml_load_string($xmlinit[0][0]);
            $initcfg = 'type=' . $xml->type->static->getName() . $xml->type->{'dhcp-client'}->getName() . "\nip-address=" . $xml->{'ip-address'} . "\ndefault-gateway=" . $xml->{'default-gateway'} . "\nnetmask=" . $xml->netmask;
            file_put_contents($this->getRunningPath() . '/cdrom/config/init-cfg.txt', $initcfg);
            $isocmd = 'mkisofs -o ' . $this->getRunningPath() . '/config.iso -l --iso-level 4 ' . $this->getRunningPath() . '/cdrom';
            exec($isocmd, $o, $rc);
            //exec('rm -rf '.$this -> getRunningPath().'/cdrom/');
        }

        return 0;
    }


    public function customFlag($flag)
    {
        if(is_file($this->getRunningPath() . '/config.iso') && !is_file($this->getRunningPath() . '/.configured') && $this->config != 0) {
            $flag .= ' -drive file=config.iso,if=ide,media=cdrom,index=3' ;
        }
        return $flag;
    }
}
