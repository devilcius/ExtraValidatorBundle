<?php

namespace devilcius\ExtraValidatorBundle\Tests\Validator\Constraints;

use devilcius\ExtraValidatorBundle\Validator\Constraints\Dni;
use devilcius\ExtraValidatorBundle\Validator\Constraints\DniValidator;

class DniValidatorTest extends \PHPUnit_Framework_TestCase
{

    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new DniValidator();
        $this->validator->initialize($this->context);
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
                ->method('addViolation');

        $this->validator->validate(null, new Dni());
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
                ->method('addViolation');

        $this->validator->validate('', new Dni());
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new \stdClass(), new Dni());
    }

    
    /**
     * @dataProvider getValidDnis
     */    
    public function testValidDni($dni)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($dni, new Dni());
    }

    public function getValidDnis()
    {
        return array(
            array('5206453-N'),
            array('5206453-n'),
            array('05206453-N'),
            array('05206453N'),
            array('5206453N'),
            array('5206453n'),
        );
    }    
    
    /**
     * @dataProvider getInvalidDnis
     */
    public function testInvalidDni($dni)
    {
        $constraint = new Dni(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
                ->method('addViolation')
                ->with('myMessage', array(
                    '{{ value }}' => $dni,
        ));

        $this->validator->validate($dni, $constraint);
    }

    public function getInvalidDnis()
    {
        return array(
            array('20757-0441'),
            array('5206453'),
            array('5206453'),
            array('05206453-X'),
            array('5206453-x'),
            array('0'),
            array('bu'),
            array(123),
        );
    }

}
