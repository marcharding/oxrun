<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-02
 * Time: 17:00
 */

namespace Oxrun\Helper;

/**
 * Class MulitSetConfigConverter
 * @package Oxrun\Helper
 */
class MulitSetConfigConverter
{
    /**
     * @param array $config
     * @return array
     */
    public function convert($config)
    {
        $newconfig['variableType'] = $config['oxvartype'];
        $newconfig['variableValue'] = $this->convertValue($config['oxvartype'], $config['oxvarvalue']);

        if (isset($config['oxmodule']) && $config['oxmodule']) {
            $newconfig['moduleId'] = $config['oxmodule'];
        }

        //Simple string
        if ($config['oxvartype'] == 'str' && $config['oxmodule'] == '') {
            $newconfig = $config['oxvarvalue'];
        }

        return [
            'key' => $config['oxvarname'],
            'value' => $newconfig
        ];
    }

    /**
     * @param $type
     * @param $value
     * @return mixed
     */
    protected function convertValue($type, $value)
    {
        switch (true) {
            case $type == 'bool':
                return (bool)$value;
            case $type == 'arr':
            case $type == 'aarr':
                return (array)unserialize($value);
            default:
                return $value;
        }
    }
}
