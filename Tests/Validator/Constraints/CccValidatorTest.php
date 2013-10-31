<?php


namespace devilcius\ExtraValidatorBundle\Tests\Validator\Constraints;

use devilcius\ExtraValidatorBundle\Validator\Constraints\Ccc;
use devilcius\ExtraValidatorBundle\Validator\Constraints\CccValidator;


class CccValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new CccValidator();
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

        $this->validator->validate(null);
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('');
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new \stdClass());
    }

    public function testValidCcc()
    {
        $this->context->expects($this->never())
            ->method('addViolation');
		$validCccs = array(
			"2077-0441-64-1100376423",
			"20770441641100376423",
			"2077-0441-64-11003764 23 ",
			"2077-0441-64-11003764-23");
		
		foreach($validCccs as $validCcc) {
		
			$this->validator->validate($validCcc);		
		}
    }

    // public function testInvalidCcc()
    // {
        // $constraint = new Enum(array(
            // 'allowedValues' => array('foo', 'bar'),
            // 'message'       => 'myMessage'
        // ));
        // $this->context->expects($this->once())
            // ->method('addViolation')
            // ->with('myMessage', $this->identicalTo(array(
                // '{{ value }}'           => 'foobar',
                // '{{ allowedValues }}'   => 'foo, bar'
            // )), $this->identicalTo('foobar'), array('foo', 'bar'));

        // $this->validator->validate('foobar', $constraint);
    // }
}