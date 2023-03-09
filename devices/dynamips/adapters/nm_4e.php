<?php
/**
 * 
 * @author LIN 
 * @copyright pnetlab.com
 * @link https://www.pnetlab.com/
 * 
 */

class nm_4e extends adapter
{

    function __construct($device, $slot, $subSlot)
    {
        parent::__construct($device, $slot, $subSlot);
        $this->name = 'NM-4E';
        $this->flag = '-p ' . $slot . ':' . $this->name;
        
    }

    public function createEthernets()
    {

        for ($p = 0; $p <= 3; $p++) {
            $interfaceId = $this->slot * 16 + $this->subSlot * 16 + $p;
            if (!isset($this->ethernets[$interfaceId])) {
                $flag = '-s '.$this->slot.':'.$p.':tap:vunl' . $this->device->getSession() . '_' . $interfaceId;
                try {
                    $this->ethernets[$interfaceId] = new Interfc( $this->device, array('name' => 'e'.$this->slot.'/'.$p, 'type' => 'ethernet', 'flag' => $flag), $interfaceId);
                } catch (Exception $e) {
                    error_log(date('M d H:i:s ') . 'ERROR: ' . $GLOBALS['messages'][40020]);
                    error_log(date('M d H:i:s ') . (string) $e);
                    return false;
                }
            }
        }
    }
}
