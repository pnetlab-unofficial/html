<?php

/**
 * 
 * @author LIN 
 * @copyright pnetlab.com
 * @link https://www.pnetlab.com/
 * 
 */

class device_c3660 extends device_dynamips
{

    function __construct($node)
    {
        parent::__construct($node);
    }

    public function createEthernets($quantity)
    {
        $this->createModule(0, 0, 'Leopard-2FE');
    }

    public function editParams($p)
    {

        parent::editParams($p);

        for ($i = 1; $i <= 6; $i++) {
            $key = 'slot' . $i;
            $nmid = $i . '/0';

            if (isset($p[$key]) && $p[$key] != '') {
                if (!isset($this->modules[$nmid]) || $this->modules[$nmid]->getName() != $p[$key]) {
                    $this->createModule($i, 0, $p[$key]);
                }
            } else if (isset($p[$key])) {
                unset($this->modules[$nmid]);
            }
        }
    }

    public function getParams()
    {
        $params = parent::getParams();
        $data = [];
        for ($i = 1; $i <= 6; $i++) {
            $key = 'slot' . $i;
            $nmid = $i . '/0';
            $data[$key] = isset($this->modules[$nmid]) ? $this->modules[$nmid]->getName() : '';
        }
        $params = array_replace($params, $data);

        return $params;
    }
}
