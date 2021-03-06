<?php

use Mockery as m;
use Payum\Core\Reply\ReplyInterface;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;

class ObtainCreditCardActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_execute()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $viewFactory = m::spy('Illuminate\Contracts\View\Factory');
        $request = m::spy('Illuminate\Http\Request');
        $obtainCreditCard = m::spy('Payum\Core\Request\ObtainCreditCard');
        $result = 'foo';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('isMethod')->andReturn(false);

        $viewFactory
            ->shouldReceive('make')->andReturnSelf()
            ->shouldReceive('render')->andReturn($result);

        $obtainCreditCardAction = new ObtainCreditCardAction($viewFactory, $request);

        try {
            $obtainCreditCardAction->execute($obtainCreditCard);
        } catch (ReplyInterface $response) {
        }

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($result, $response->getResponse()->getContent());

        $request->shouldHaveReceived('isMethod')->once();
        $obtainCreditCard->shouldHaveReceived('getModel')->once();
        $obtainCreditCard->shouldHaveReceived('getFirstModel')->once();
        $obtainCreditCard->shouldHaveReceived('getToken')->once();
        $viewFactory->shouldHaveReceived('make')->with('payum::creditcard', m::type('array'))->once();
        $viewFactory->shouldHaveReceived('render')->once();
    }

    public function test_execute_with_post()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $request = m::mock('Illuminate\Http\Request');
        $obtainCreditCard = m::spy('Payum\Core\Request\ObtainCreditCard');
        $cardHolder = 'foo.card_holder';
        $cardNumber = 'foo.card_number';
        $securityCode = '222';
        $expireAt = date('Y-m-d');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('isMethod')->andReturn(true)
            ->shouldReceive('get')->with('card_holder')->andReturn($cardHolder)
            ->shouldReceive('get')->with('card_number')->andReturn($cardNumber)
            ->shouldReceive('get')->with('card_cvv')->andReturn($securityCode)
            ->shouldReceive('get')->with('card_expire_at')->andReturn($expireAt);

        $obtainCreditCardAction = new ObtainCreditCardAction($viewFactory, $request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertNull($obtainCreditCardAction->execute($obtainCreditCard));

        $request->shouldHaveReceived('isMethod')->once();
        $request->shouldHaveReceived('get')->with('card_holder')->once();
        $request->shouldHaveReceived('get')->with('card_number')->once();
        $request->shouldHaveReceived('get')->with('card_cvv')->once();
        $request->shouldHaveReceived('get')->with('card_expire_at')->once();
        $obtainCreditCard->shouldHaveReceived('set')->with(m::type('Payum\Core\Model\CreditCard'))->once();
    }

    /**
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function test_throw_not_support()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $viewFactory = m::spy('Illuminate\Contracts\View\Factory');
        $request = m::spy('Illuminate\Http\Request');
        $obtainCreditCard = m::spy('stdClass');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $obtainCreditCardAction = new ObtainCreditCardAction($viewFactory, $request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $obtainCreditCardAction->execute($obtainCreditCard);
    }
}
