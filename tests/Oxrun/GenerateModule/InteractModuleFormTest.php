<?php
/**
 * Created by oxrun.
 * Autor: Tobias Matthaiou <tm@loberon.de>
 * Date: 10.11.18
 * Time: 11:31
 */

namespace Oxrun\GenerateModule\Test;

use Oxrun\GenerateModule\InteractModuleForm;
use Oxrun\GenerateModule\ModuleSpecification;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InteractModuleFormTest
 * @package Oxrun\GenerateModule\Test
 */
class InteractModuleFormTest extends TestCase
{
    /**
     * This method is called before each test.
     */
    private $interactModuleForm;

    /**
     * @var ModuleSpecification|ObjectProphecy
     */
    private $moduleSpecification;

    /**
     * @var QuestionHelper|ObjectProphecy
     */
    private $questionHelper;

    /**
     * @var OutputInterface|ObjectProphecy
     */
    private $outputInterface;

    /**
     * @var InputInterface|ObjectProphecy
     */
    private $inputInterface;

    protected function setUp()
    {
        $this->moduleSpecification = $this->prophesize(ModuleSpecification::class);

        $this->inputInterface = $this->prophesize(InputInterface::class);
        $this->outputInterface = $this->prophesize(OutputInterface::class);
        $this->questionHelper = $this->prophesize(QuestionHelper::class);
    }

    public function dataOptionModule()
    {
        return [
            [
                'InteractModuleForm' => 'askModuleName',
                'option' => 'name',
                'value' => 'HammerModule',
                'ModuleSpecification' => 'setModuleName'
            ],
            [
                'InteractModuleForm' => 'askVendor',
                'option' => 'vendor',
                'value' => 'Firma',
                'ModuleSpecification' => 'setVendor'
            ],
            [
                'InteractModuleForm' => 'askDescription',
                'option' => 'description',
                'value' => 'bla bla bal test',
                'ModuleSpecification' => 'setDescription'
            ],
            [
                'InteractModuleForm' => 'askAuthor',
                'option' => 'author',
                'value' => 'Mrs. Developer',
                'ModuleSpecification' => 'setAuthorName'
            ],
            [
                'InteractModuleForm' => 'askEmail',
                'option' => 'email',
                'value' => 'dev@localhost',
                'ModuleSpecification' => 'setAuthorEmail'
            ],
        ];
    }

    /**
     * @param $classFunc
     * @param $optionName
     * @param $optionValue
     * @param $assertFunction
     *
     * @dataProvider dataOptionModule
     */
    public function testOptionModuleName($classFunc, $optionName, $optionValue, $assertFunction)
    {
        //Assert
        $this->inputInterface->getOption(Argument::is($optionName))
            ->willReturn($optionValue)
            ->shouldBeCalled();
        $this->moduleSpecification->$assertFunction(Argument::is($optionValue))
            ->willReturn()
            ->shouldBeCalled();

        //Arrage
        $interactModuleForm = $this->factoryInteractModuleForm();

        //Act
        $actual = $interactModuleForm->$classFunc();

        //Assert Fluet
        $this->assertInstanceOf(InteractModuleForm::class, $actual);
    }

    /**
     * @param $classFunc
     * @param $optionName
     * @param $optionValue
     * @param $assertFunction
     *
     * @dataProvider dataOptionModule
     */
    public function testInteractModuleName($classFunc, $optionName, $optionValue, $assertFunction)
    {
        //Assert
        $this->questionHelper->ask(Argument::any(),Argument::any(),Argument::any())
            ->willReturn($optionValue)
            ->shouldBeCalled();
        $this->moduleSpecification->$assertFunction(Argument::is($optionValue))
            ->willReturn()
            ->shouldBeCalled();
        $this->inputInterface->getOption(Argument::is($optionName))->willReturn()->shouldBeCalled();

        //Arrage
        $interactModuleForm = $this->factoryInteractModuleForm();

        //Act
        $interactModuleForm->$classFunc();
    }


    /**
     * @return InteractModuleForm
     */
    protected function factoryInteractModuleForm()
    {
        return new InteractModuleForm(
            $this->moduleSpecification->reveal(),
            $this->questionHelper->reveal(),
            $this->inputInterface->reveal(),
            $this->outputInterface->reveal()
        );
    }

}
