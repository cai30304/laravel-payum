<?php

use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Mockery as m;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use Recca0120\LaravelPayum\Http\Controllers\PaymentController;
use Recca0120\LaravelPayum\Payment;

class ControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_throw_reply_interface()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with([
                'payum_token' => $payumToken,
            ])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->with(m::type(Capture::class))->once()->andReturnUsing(function ($capture) {
                throw new HttpResponse('test');
            })
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $sessionManager = m::mock(SessionManager::class)
            ->shouldReceive('set')->with('payum_token', $payumToken)->once()
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class)
            ->shouldReceive('convert')->with(m::type(ReplyInterface::class))->once()
            ->mock();

        $payment = new Payment($payum, $sessionManager, $converter);
        $controller = new PaymentController();
        $controller->capture($payment, $request, $payumToken);
    }
}
