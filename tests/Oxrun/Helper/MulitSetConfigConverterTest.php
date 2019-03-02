<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-03-02
 * Time: 17:02
 */

namespace Oxrun\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Class MulitSetConfigConverterTest
 * @package Oxrun\Helper
 */
class MulitSetConfigConverterTest extends TestCase
{

    /**
     * @dataProvider configTypes
     */
    public function testConvert($expect, $config)
    {
        //Arrange
        $mulitSetConfigConverter = new MulitSetConfigConverter();

        //Act
        $actual = $mulitSetConfigConverter->convert($config);

        //Assert
        $this->assertSame($expect, $actual);
    }

    public function configTypes()
    {
        return [
            'booleanType' => [
                [
                    'key' => 'blGAAnonymizeIPs',
                    'value' => [
                        'variableType' => 'bool',
                        'variableValue' => true,
                        'moduleId' => 'theme:flow',
                    ]
                ],
                [
                    'oxvarname' => 'blGAAnonymizeIPs',
                    'oxvartype' => 'bool',
                    'oxvarvalue' => '1',
                    'oxmodule' => 'theme:flow',
                ]
            ],
            'OptionalModule' => [
                [
                    'key' => 'blGAAnonymizeIPs',
                    'value' => [
                        'variableType' => 'bool',
                        'variableValue' => false,
                    ]
                ],
                [
                    'oxvarname' => 'blGAAnonymizeIPs',
                    'oxvartype' => 'bool',
                    'oxvarvalue' => '',
                    'oxmodule' => '',
                ]
            ],
            'stringType' => [
                [
                    'key' => 'sBackgroundColor',
                    'value' => [
                        'variableType' => 'str',
                        'variableValue' => '#CCEBF5',
                        'moduleId' => 'theme:flow',
                    ]
                ],
                [
                    'oxvarname' => 'sBackgroundColor',
                    'oxvartype' => 'str',
                    'oxvarvalue' => '#CCEBF5',
                    'oxmodule' => 'theme:flow',
                ]
            ],
            'stringSimpleType' => [
                [
                    'key' => 'sBackgroundColor',
                    'value' => '#CCEBF5',
                ],
                [
                    'oxvarname' => 'sBackgroundColor',
                    'oxvartype' => 'str',
                    'oxvarvalue' => '#CCEBF5',
                    'oxmodule' => '',
                ]
            ],
            'arrType' => [
                [
                    'key' => 'aNrofCatArticlesInGrid',
                    'value' => [
                        'variableType' => 'arr',
                        'variableValue' => ['12', '16', '24', '32'],
                        'moduleId' => 'theme:flow',
                    ]
                ],
                [
                    'oxvarname' => 'aNrofCatArticlesInGrid',
                    'oxvartype' => 'arr',
                    'oxvarvalue' => 'a:4:{i:0;s:2:"12";i:1;s:2:"16";i:2;s:2:"24";i:3;s:2:"32";}',
                    'oxmodule' => 'theme:flow',
                ]
            ],
            'aarrType' => [
                [
                    'key' => 'aDetailImageSizes',
                    'value' => [
                        'variableType' => 'aarr',
                        'variableValue' => [
                            'oxpic1' => '540*340',
                            'oxpic2' => '540*340',
                            'oxpic3' => '540*340'
                        ],
                    ]
                ],
                [
                    'oxvarname' => 'aDetailImageSizes',
                    'oxvartype' => 'aarr',
                    'oxvarvalue' => 'a:3:{s:6:"oxpic1";s:7:"540*340";s:6:"oxpic2";s:7:"540*340";s:6:"oxpic3";s:7:"540*340";}',
                ]
            ],
            'selectType' => [
                [
                    'key' => 'sDefaultListDisplayType',
                    'value' => [
                        'variableType' => 'select',
                        'variableValue' => 'infogrid',
                    ]
                ],
                [
                    'oxvarname' => 'sDefaultListDisplayType',
                    'oxvartype' => 'select',
                    'oxvarvalue' => 'infogrid',
                ]
            ],

        ];
    }
}
